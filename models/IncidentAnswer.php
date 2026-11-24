<?php

namespace App\Models;

use App\Core\Model;

class IncidentAnswer extends Model
{
    protected string $table = 'incident_answers';

    public function createMany(int $incidentId, array $rows): void
    {
        $this->assertValidIdentifier($this->table);
        if (empty($rows)) {
            return;
        }
        $sql = "INSERT INTO {$this->table}
                (incident_id, question_key, question_text, answer_label, weight, points_scored, is_unsure)
                VALUES (:incident_id, :question_key, :question_text, :answer_label, :weight, :points_scored, :is_unsure)";
        $stmt = $this->db->prepare($sql);
        foreach ($rows as $row) {
            $stmt->execute([
                'incident_id'   => $incidentId,
                'question_key'  => $row['key'],
                'question_text' => $row['text'],
                'answer_label'  => $row['answer_label'],
                'weight'        => $row['weight'],
                'points_scored' => $row['points_scored'],
                'is_unsure'     => $row['is_unsure'] ? 1 : 0,
            ]);
        }
    }

    public function forIncident(int $incidentId): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE incident_id = :incident_id ORDER BY id ASC");
        $stmt->execute(['incident_id' => $incidentId]);
        return $stmt->fetchAll();
    }

    public function deleteForIncident(int $incidentId): void
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE incident_id = :incident_id");
        $stmt->execute(['incident_id' => $incidentId]);
    }
}
