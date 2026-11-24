-- M3: Verification & Reassessment migration
-- Run against: resqnow

-- 1. Expand incidents.status enum to the M3 lifecycle set.
--    Map any legacy rows first so the enum shrink is safe.
UPDATE incidents SET status = 'submitted' WHERE status IN ('pending','cancelled');
UPDATE incidents SET status = 'triaged'   WHERE status = 'prioritized';
UPDATE incidents SET status = 'assigned'  WHERE status = 'dispatched';

ALTER TABLE incidents
    MODIFY COLUMN status ENUM(
        'submitted','verified','rejected','duplicate',
        'triaged','assigned','acknowledged','on_scene',
        'in_progress','resolved','closed'
    ) NOT NULL DEFAULT 'submitted';

-- 2. Escalation flag for high-priority flagging.
ALTER TABLE incidents
    ADD COLUMN is_escalated TINYINT(1) NOT NULL DEFAULT 0
    AFTER unsure_ratio;

-- 3. Verification records table (one row per verification action).
CREATE TABLE IF NOT EXISTS verification_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    incident_id INT UNSIGNED NOT NULL,
    action ENUM('verified','reassessed','rejected','duplicate','escalated','deescalated','reopened') NOT NULL,
    original_triage_level VARCHAR(30) NULL,
    new_triage_level VARCHAR(30) NULL,
    reason TEXT NULL,
    original_report_code VARCHAR(30) NULL,
    verified_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_incident (incident_id),
    KEY idx_action (action),
    CONSTRAINT fk_verif_incident FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    CONSTRAINT fk_verif_user FOREIGN KEY (verified_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
