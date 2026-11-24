-- M5: Analytics — barangays lookup table + incident barangay linkage
-- Run against: resqnow

-- 1. Barangays reference table
CREATE TABLE IF NOT EXISTS barangays (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    UNIQUE KEY uq_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Seed barangays from existing user data
INSERT IGNORE INTO barangays (name)
SELECT DISTINCT barangay FROM users WHERE barangay IS NOT NULL AND barangay != '';

-- Add some standard ones if empty
INSERT IGNORE INTO barangays (name) VALUES
    ('Barangay 1'), ('Barangay 2'), ('Barangay 3'), ('Barangay 4'),
    ('Barangay 5'), ('Barangay 6'), ('Barangay 7'), ('Barangay 8');

-- 3. Add barangay_id to incidents (nullable FK)
ALTER TABLE incidents
    ADD COLUMN IF NOT EXISTS barangay_id INT UNSIGNED NULL
    AFTER address,
    ADD KEY IF NOT EXISTS idx_barangay (barangay_id);

-- 4. Back-fill barangay_id from reporter's barangay where possible
UPDATE incidents i
JOIN users u ON u.id = i.reported_by
JOIN barangays b ON b.name = u.barangay
SET i.barangay_id = b.id
WHERE i.barangay_id IS NULL AND u.barangay IS NOT NULL;

-- 5. Export rate-limit: store per-user export timestamps in audit_logs
--    (no new table needed — we query audit_logs WHERE action='export_analytics')

SELECT
    CONCAT('Barangays: ', COUNT(*)) AS m5_summary FROM barangays
UNION ALL
SELECT CONCAT('Incidents with barangay_id: ', COUNT(*)) FROM incidents WHERE barangay_id IS NOT NULL;
