<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\ResponseTeam;
use App\Models\User;
use App\Models\AuditLog;

class TeamController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin']);

        $teamModel = new ResponseTeam($this->db);
        $teams = $teamModel->allWithStats();

        $this->view('teams/index', [
            'pageTitle' => 'Response Teams',
            'teams'     => $teams,
            'flash'     => $this->flash(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['admin']);

        $userModel = new User($this->db);
        $responders = $userModel->allByRole(['responder']);

        if ($this->isPost()) {
            $this->handleCreate($responders);
            return;
        }

        $this->view('teams/form', [
            'pageTitle'  => 'New Response Team',
            'team'       => null,
            'members'    => [],
            'responders' => $responders,
            'errors'     => [],
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole(['admin']);

        $teamModel = new ResponseTeam($this->db);
        $team = $teamModel->findWithDetails((int) $id);
        if (!$team) {
            http_response_code(404); die('Team not found');
        }

        $userModel = new User($this->db);
        $responders = $userModel->allByRole(['responder']);
        $members = $teamModel->memberIds((int) $id);

        if ($this->isPost()) {
            $this->handleEdit((int) $id, $team, $responders);
            return;
        }

        $this->view('teams/form', [
            'pageTitle'  => 'Edit ' . $team['team_name'],
            'team'       => $team,
            'members'    => $members,
            'responders' => $responders,
            'errors'     => [],
        ]);
    }

    public function delete(string $id): void
    {
        $this->requireRole(['admin']);
        $this->guardCsrf();

        $teamModel = new ResponseTeam($this->db);
        $team = $teamModel->find((int) $id);
        if (!$team) {
            http_response_code(404); die('Team not found');
        }
        if ($team['status'] === 'on_mission') {
            $this->setFlash('danger', 'Cannot delete a team that is currently on a mission.');
            $this->redirect('/teams');
            return;
        }

        $this->db->prepare("DELETE FROM response_teams WHERE id = :id")->execute(['id' => $id]);

        (new AuditLog($this->db))->write(
            auth()['id'], 'team.deleted', 'response_team', (int) $id,
            json_encode(['team_name' => $team['team_name']])
        );

        $this->setFlash('success', 'Team "' . $team['team_name'] . '" deleted.');
        $this->redirect('/teams');
    }

    // ------------------------------------------------------------------
    // Private
    // ------------------------------------------------------------------

    private function handleCreate(array $responders): void
    {
        $this->guardCsrf();
        [$data, $memberIds, $errors] = $this->parseForm($responders);

        if (!empty($errors)) {
            $this->view('teams/form', [
                'pageTitle'  => 'New Response Team',
                'team'       => $data,
                'members'    => $memberIds,
                'responders' => $responders,
                'errors'     => $errors,
            ]);
            return;
        }

        $teamModel = new ResponseTeam($this->db);
        $teamId = $teamModel->create($data);
        $teamModel->syncMembers($teamId, $memberIds);

        (new AuditLog($this->db))->write(
            auth()['id'], 'team.created', 'response_team', $teamId,
            json_encode(['team_name' => $data['team_name']])
        );

        $this->setFlash('success', 'Team "' . $data['team_name'] . '" created.');
        $this->redirect('/teams');
    }

    private function handleEdit(int $id, array $team, array $responders): void
    {
        $this->guardCsrf();
        [$data, $memberIds, $errors] = $this->parseForm($responders);

        if (!empty($errors)) {
            $teamModel = new ResponseTeam($this->db);
            $this->view('teams/form', [
                'pageTitle'  => 'Edit ' . $team['team_name'],
                'team'       => array_merge($team, $data),
                'members'    => $memberIds,
                'responders' => $responders,
                'errors'     => $errors,
            ]);
            return;
        }

        // Preserve on_mission status if already on mission
        if ($team['status'] === 'on_mission') {
            $data['status'] = 'on_mission';
        }

        $teamModel = new ResponseTeam($this->db);
        $teamModel->update($id, $data);
        $teamModel->syncMembers($id, $memberIds);

        (new AuditLog($this->db))->write(
            auth()['id'], 'team.updated', 'response_team', $id,
            json_encode(['team_name' => $data['team_name']])
        );

        $this->setFlash('success', 'Team updated.');
        $this->redirect('/teams');
    }

    private function parseForm(array $responders): array
    {
        $responderIds = array_column($responders, 'id');

        $teamName    = trim((string) $this->input('team_name', ''));
        $unitType    = trim((string) $this->input('unit_type', ''));
        $coverage    = trim((string) $this->input('coverage_area', ''));
        $contact     = trim((string) $this->input('contact_no', ''));
        $leaderId    = $this->input('leader_id') ? (int) $this->input('leader_id') : null;
        $status      = trim((string) $this->input('status', 'available'));
        $memberIds   = (array) ($this->input('members') ?? []);
        $memberIds   = array_map('intval', $memberIds);
        // only keep valid responder IDs
        $memberIds   = array_values(array_filter($memberIds, fn($id) => in_array($id, $responderIds)));

        $validTypes   = ['fire', 'medical', 'rescue', 'security', 'multi'];
        $validStatus  = ['available', 'on_mission', 'offline'];

        $errors = [];
        if ($teamName === '') {
            $errors['team_name'] = 'Team name is required.';
        }
        if (!in_array($unitType, $validTypes, true)) {
            $errors['unit_type'] = 'Please select a valid unit type.';
        }
        if (!in_array($status, $validStatus, true)) {
            $errors['status'] = 'Please select a valid status.';
        }
        // Contact number: allow blank, but if filled must be digits/spaces/dashes/+ only
        if ($contact !== '' && !preg_match('/^[0-9\+\-\s]{7,20}$/', $contact)) {
            $errors['contact_no'] = 'Contact No. must be a valid phone number (digits, spaces, dashes, or + only).';
        }
        if ($leaderId !== null && !in_array($leaderId, $responderIds)) {
            $errors['leader_id'] = 'Please select a valid team leader.';
        }

        $data = [
            'team_name'     => $teamName,
            'unit_type'     => $unitType,
            'coverage_area' => $coverage ?: null,
            'contact_no'    => $contact ?: null,
            'leader_id'     => $leaderId,
            'status'        => $status,
        ];

        return [$data, $memberIds, $errors];
    }

    private function guardCsrf(): void
    {
        if (!Csrf::verify($this->input('csrf_token'))) {
            $this->setFlash('danger', 'Invalid session. Please try again.');
            $this->redirect('/teams');
            exit;
        }
    }
}
