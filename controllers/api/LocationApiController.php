<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\ResponseTeam;
use App\Models\AuditLog;

class LocationApiController extends Controller
{
    private const HISTORY_KEEP = 100; // rows to keep per team

    // ── POST /api/v1/responder/location ─────────────────────────────
    // Responder pushes their GPS position while on active mission.
    public function update(): void
    {
        $user = $this->requireApiAuth('responder');

        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $lat  = isset($body['lat'])  ? (float) $body['lat']  : null;
        $lng  = isset($body['lng'])  ? (float) $body['lng']  : null;
        $acc  = isset($body['accuracy']) ? (float) $body['accuracy'] : null;

        if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $this->json(['error' => 'Invalid coordinates'], 422);
            return;
        }

        // Find the team this responder belongs to that has an active assignment
        $stmt = $this->db->prepare(
            "SELECT rt.id AS team_id
             FROM team_members tm
             JOIN response_teams rt ON rt.id = tm.team_id
             JOIN assignments a ON a.team_id = rt.id AND a.status = 'active'
             WHERE tm.user_id = :uid
             LIMIT 1"
        );
        $stmt->execute(['uid' => $user['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            // No active mission — still record but return gracefully
            $this->json(['ok' => false, 'msg' => 'No active mission'], 200);
            return;
        }

        $teamId = (int) $row['team_id'];

        // Update team current position
        $this->db->prepare(
            "UPDATE response_teams
             SET current_lat = :lat, current_lng = :lng,
                 location_accuracy = :acc, location_updated_at = NOW(),
                 tracking_active = 1
             WHERE id = :tid"
        )->execute(['lat' => $lat, 'lng' => $lng, 'acc' => $acc, 'tid' => $teamId]);

        // Insert into history
        $this->db->prepare(
            "INSERT INTO responder_location_history (team_id, user_id, lat, lng, accuracy)
             VALUES (:tid, :uid, :lat, :lng, :acc)"
        )->execute(['tid' => $teamId, 'uid' => $user['id'], 'lat' => $lat, 'lng' => $lng, 'acc' => $acc]);

        // Prune history: keep only last N rows per team
        $this->db->prepare(
            "DELETE FROM responder_location_history
             WHERE team_id = :tid
               AND id NOT IN (
                 SELECT id FROM (
                   SELECT id FROM responder_location_history
                   WHERE team_id = :tid2
                   ORDER BY created_at DESC
                   LIMIT :keep
                 ) sub
               )"
        )->execute(['tid' => $teamId, 'tid2' => $teamId, 'keep' => self::HISTORY_KEEP]);

        $this->json(['ok' => true, 'team_id' => $teamId]);
    }

    // ── POST /api/v1/responder/location/stop ────────────────────────
    // Called when responder resolves a mission or logs out.
    public function stop(): void
    {
        $user = $this->requireApiAuth('responder');

        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        // Mark all teams for this responder as no longer tracking
        $this->db->prepare(
            "UPDATE response_teams rt
             JOIN team_members tm ON tm.team_id = rt.id
             SET rt.tracking_active = 0
             WHERE tm.user_id = :uid"
        )->execute(['uid' => $user['id']]);

        (new AuditLog($this->db))->write(
            (int) $user['id'], 'tracking.stopped', 'user', (int) $user['id'],
            json_encode(['source' => 'pwa'])
        );

        $this->json(['ok' => true]);
    }

    // ── GET /api/v1/incidents/{code}/responder-location ─────────────
    // Returns latest responder position for a given incident (for resident + admin map).
    public function forIncident(string $code): void
    {
        $user = $this->requireApiAuth();

        // Find the active assignment for this incident
        $stmt = $this->db->prepare(
            "SELECT a.team_id, rt.team_name,
                    rt.current_lat, rt.current_lng, rt.location_accuracy,
                    rt.location_updated_at, rt.tracking_active,
                    i.location_lat AS inc_lat, i.location_lng AS inc_lng,
                    i.reported_by
             FROM incidents i
             JOIN assignments a ON a.incident_id = i.id AND a.status = 'active'
             JOIN response_teams rt ON rt.id = a.team_id
             WHERE i.code = :code
             LIMIT 1"
        );
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();

        if (!$row) {
            $this->json(['tracking' => false, 'reason' => 'No active assignment or incident not found']);
            return;
        }

        // Access control: resident can only see their own incident
        if (($user['role'] ?? '') === 'resident' && (int)$row['reported_by'] !== (int)$user['id']) {
            $this->json(['error' => 'Forbidden'], 403);
            return;
        }

        if (!$row['current_lat'] || !$row['tracking_active']) {
            $this->json([
                'tracking'  => false,
                'team_name' => $row['team_name'],
                'reason'    => 'Responder location not available',
            ]);
            return;
        }

        $lastSeenSec = $row['location_updated_at']
            ? max(0, (int)(time() - strtotime($row['location_updated_at'] . ' UTC')))
            : null;

        // Simple ETA: haversine distance / 40 km/h
        $etaMinutes = null;
        if ($row['inc_lat'] && $row['inc_lng']) {
            $distKm = $this->haversine(
                (float)$row['current_lat'], (float)$row['current_lng'],
                (float)$row['inc_lat'],     (float)$row['inc_lng']
            );
            $etaMinutes = $distKm < 0.05 ? 0 : (int) ceil($distKm / (40 / 60));
        }

        $this->json([
            'tracking'        => true,
            'team_name'       => $row['team_name'],
            'lat'             => (float) $row['current_lat'],
            'lng'             => (float) $row['current_lng'],
            'accuracy'        => $row['location_accuracy'] ? (float) $row['location_accuracy'] : null,
            'last_seen_seconds' => $lastSeenSec,
            'eta_minutes'     => $etaMinutes,
        ]);
    }

    // ── GET /api/v1/monitoring/locations ────────────────────────────
    // Returns all active responder team positions for the monitoring map.
    public function all(): void
    {
        $this->requireApiAuth(); // admin/personnel/responder

        $stmt = $this->db->query(
            "SELECT rt.id, rt.team_name, rt.unit_type,
                    rt.current_lat, rt.current_lng, rt.location_accuracy,
                    rt.location_updated_at, rt.tracking_active,
                    (SELECT i.code FROM assignments a
                     JOIN incidents i ON i.id = a.incident_id
                     WHERE a.team_id = rt.id AND a.status = 'active'
                     LIMIT 1) AS active_incident_code
             FROM response_teams rt
             WHERE rt.status = 'on_mission'
               AND rt.current_lat IS NOT NULL
               AND rt.tracking_active = 1"
        );
        $teams = $stmt->fetchAll();

        $result = array_map(function($t) {
            $lastSec = $t['location_updated_at']
                ? max(0, (int)(time() - strtotime($t['location_updated_at'] . ' UTC')))
                : null;
            return [
                'team_id'            => (int) $t['id'],
                'team_name'          => $t['team_name'],
                'unit_type'          => $t['unit_type'],
                'lat'                => (float) $t['current_lat'],
                'lng'                => (float) $t['current_lng'],
                'accuracy'           => $t['location_accuracy'] ? (float)$t['location_accuracy'] : null,
                'last_seen_seconds'  => $lastSec,
                'active_incident'    => $t['active_incident_code'],
            ];
        }, $teams);

        $this->json(['responders' => $result, 'ts' => time()]);
    }

    // ── Haversine distance in km ─────────────────────────────────────
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R  = 6371;
        $dL = deg2rad($lat2 - $lat1);
        $dG = deg2rad($lng2 - $lng1);
        $a  = sin($dL/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dG/2)**2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
