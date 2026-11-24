<?php

namespace App\Models;

use App\Core\Model;

class ResponseTeam extends Model
{
    protected string $table = 'response_teams';

    public function allWithStats(): array
    {
        $sql = "SELECT t.*,
                    u.name AS leader_name,
                    u.phone AS leader_phone,
                    COUNT(DISTINCT tm.user_id) AS member_count,
                    COUNT(DISTINCT CASE WHEN a.status = 'active' THEN a.id END) AS active_missions,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            mu.name, '|||',
                            COALESCE(
                                (SELECT 'on_duty' FROM assignments aa
                                 JOIN incidents ai ON ai.id = aa.incident_id
                                 JOIN team_members ttm ON ttm.team_id = aa.team_id
                                 WHERE ttm.user_id = mu.id AND aa.status = 'active' LIMIT 1),
                                'off_duty'
                            )
                        )
                        ORDER BY mu.name ASC SEPARATOR ';;'
                    ) AS members_packed
                FROM response_teams t
                LEFT JOIN users u ON u.id = t.leader_id
                LEFT JOIN team_members tm ON tm.team_id = t.id
                LEFT JOIN users mu ON mu.id = tm.user_id
                LEFT JOIN assignments a ON a.team_id = t.id AND a.status = 'active'
                GROUP BY t.id
                ORDER BY FIELD(t.status,'available','on_mission','offline'), t.team_name";
        return $this->db->query($sql)->fetchAll();
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.name AS leader_name
             FROM response_teams t
             LEFT JOIN users u ON u.id = t.leader_id
             WHERE t.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function membersOf(int $teamId): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.name, u.phone, u.email, u.barangay
             FROM team_members tm
             JOIN users u ON u.id = tm.user_id
             WHERE tm.team_id = :team_id
             ORDER BY u.name"
        );
        $stmt->execute(['team_id' => $teamId]);
        return $stmt->fetchAll();
    }

    public function memberIds(int $teamId): array
    {
        $stmt = $this->db->prepare(
            "SELECT user_id FROM team_members WHERE team_id = :team_id"
        );
        $stmt->execute(['team_id' => $teamId]);
        return array_column($stmt->fetchAll(), 'user_id');
    }

    public function syncMembers(int $teamId, array $userIds): void
    {
        $this->db->prepare("DELETE FROM team_members WHERE team_id = :tid")
                 ->execute(['tid' => $teamId]);
        if (empty($userIds)) {
            return;
        }
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO team_members (team_id, user_id) VALUES (:tid, :uid)"
        );
        foreach ($userIds as $uid) {
            $stmt->execute(['tid' => $teamId, 'uid' => (int) $uid]);
        }
    }

    public function create(array $data): int
    {
        $cols = array_keys($data);
        foreach ($cols as $c) {
            $this->assertValidIdentifier($c);
        }
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO response_teams (" . implode(',', $cols) . ")
                VALUES (" . implode(',', $placeholders) . ")";
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];
        foreach (array_keys($data) as $c) {
            $this->assertValidIdentifier($c);
            $sets[] = "{$c} = :{$c}";
        }
        $data['id'] = $id;
        $this->db->prepare(
            "UPDATE response_teams SET " . implode(', ', $sets) . " WHERE id = :id"
        )->execute($data);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db->prepare(
            "UPDATE response_teams SET status = :status WHERE id = :id"
        )->execute(['status' => $status, 'id' => $id]);
    }

    /** Available teams, sorted so unit_type matching $hazardType comes first */
    public function availableFor(string $hazardType): array
    {
        // Map hazard → unit_type for sort priority
        $map = [
            'fire'        => 'fire',
            'flood'       => 'rescue',
            'earthquake'  => 'rescue',
            'medical'     => 'medical',
            'accident'    => 'medical',
            'evacuation'  => 'rescue',
            'security'    => 'security',
            'other'       => 'rescue',
        ];
        $preferred = $map[$hazardType] ?? 'rescue';

        $stmt = $this->db->prepare(
            "SELECT t.*,
                    u.name AS leader_name,
                    COUNT(DISTINCT tm.user_id) AS member_count,
                    COUNT(DISTINCT a.id) AS active_load
             FROM response_teams t
             LEFT JOIN users u ON u.id = t.leader_id
             LEFT JOIN team_members tm ON tm.team_id = t.id
             LEFT JOIN assignments a ON a.team_id = t.id AND a.status = 'active'
             WHERE t.status = 'available'
             GROUP BY t.id
             ORDER BY (t.unit_type = :preferred) DESC, t.team_name"
        );
        $stmt->execute(['preferred' => $preferred]);
        return $stmt->fetchAll();
    }

    public function activeMissionCount(int $teamId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM assignments WHERE team_id = :tid AND status = 'active'"
        );
        $stmt->execute(['tid' => $teamId]);
        return (int) $stmt->fetchColumn();
    }

    /** Is the given user a member (or leader) of this team? */
    public function isMember(int $teamId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM team_members WHERE team_id = :tid AND user_id = :uid"
        );
        $stmt->execute(['tid' => $teamId, 'uid' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Find the team(s) a responder belongs to */
    public function teamsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.name AS leader_name
             FROM response_teams t
             JOIN team_members tm ON tm.team_id = t.id
             LEFT JOIN users u ON u.id = t.leader_id
             WHERE tm.user_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
