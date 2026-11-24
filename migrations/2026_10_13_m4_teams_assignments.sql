-- M4: Response Teams + Assignment + Monitoring
-- Run against: resqnow

-- 1. Response teams
CREATE TABLE IF NOT EXISTS response_teams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    unit_type ENUM('fire','medical','rescue','security','multi') NOT NULL DEFAULT 'rescue',
    coverage_area VARCHAR(150) NULL,
    contact_no VARCHAR(20) NULL,
    leader_id INT UNSIGNED NULL,
    status ENUM('available','on_mission','offline') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_unit_type (unit_type),
    CONSTRAINT fk_team_leader FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Team members (many-to-many users ↔ response_teams)
CREATE TABLE IF NOT EXISTS team_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_team_member (team_id, user_id),
    KEY idx_user (user_id),
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES response_teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Assignments (incident ↔ team, one active at a time)
CREATE TABLE IF NOT EXISTS assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    incident_id INT UNSIGNED NOT NULL,
    team_id INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    status ENUM('active','reassigned','completed','cancelled') NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_incident (incident_id),
    KEY idx_team (team_id),
    KEY idx_status (status),
    CONSTRAINT fk_assign_incident FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_team FOREIGN KEY (team_id) REFERENCES response_teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_by FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Expand response_updates for M4 (add columns for richer tracking)
ALTER TABLE response_updates
    ADD COLUMN IF NOT EXISTS team_id INT UNSIGNED NULL AFTER created_by,
    ADD COLUMN IF NOT EXISTS responder_id INT UNSIGNED NULL AFTER team_id,
    ADD COLUMN IF NOT EXISTS action_code VARCHAR(60) NULL AFTER responder_id,
    ADD COLUMN IF NOT EXISTS support_requested TINYINT(1) NOT NULL DEFAULT 0 AFTER action_code,
    ADD COLUMN IF NOT EXISTS support_type ENUM('backup','ambulance','fire_truck','additional_personnel') NULL AFTER support_requested,
    ADD COLUMN IF NOT EXISTS support_acknowledged_by INT UNSIGNED NULL AFTER support_type,
    ADD COLUMN IF NOT EXISTS support_acknowledged_at TIMESTAMP NULL AFTER support_acknowledged_by,
    ADD KEY IF NOT EXISTS idx_team (team_id),
    ADD KEY IF NOT EXISTS idx_support (support_requested);

-- 5. Add assignment FK columns to incidents
ALTER TABLE incidents
    ADD COLUMN IF NOT EXISTS assigned_team_id INT UNSIGNED NULL AFTER assigned_team,
    ADD COLUMN IF NOT EXISTS assigned_by INT UNSIGNED NULL AFTER assigned_team_id,
    ADD COLUMN IF NOT EXISTS assigned_at TIMESTAMP NULL AFTER assigned_by,
    ADD COLUMN IF NOT EXISTS resolved_at TIMESTAMP NULL AFTER assigned_at,
    ADD KEY IF NOT EXISTS idx_assigned_team (assigned_team_id);

-- 6. Seed: 4 responder users (password = 'responder123' bcrypt)
INSERT IGNORE INTO users (name, email, phone, password, role, status, barangay) VALUES
('Juan dela Cruz',    'juan.responder@resqnow.test',   '09100000011', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 1'),
('Maria Santos',      'maria.responder@resqnow.test',  '09100000012', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 1'),
('Carlos Reyes',      'carlos.responder@resqnow.test', '09100000013', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 2'),
('Ana Gonzalez',      'ana.responder@resqnow.test',    '09100000014', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 2'),
('Pedro Villanueva',  'pedro.responder@resqnow.test',  '09100000015', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 3'),
('Rosa Mendoza',      'rosa.responder@resqnow.test',   '09100000016', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 3'),
('Boy Aquino',        'boy.responder@resqnow.test',    '09100000017', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 4'),
('Lina Castillo',     'lina.responder@resqnow.test',   '09100000018', '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK', 'responder', 'approved', 'Barangay 4');

-- 7. Seed: 4 teams (using subselects to get the user IDs safely)
INSERT IGNORE INTO response_teams (team_name, unit_type, coverage_area, contact_no, leader_id, status) VALUES
('Team Alpha Rescue', 'rescue',   'Barangay 1 & 2', '09200000001', (SELECT id FROM users WHERE email='juan.responder@resqnow.test' LIMIT 1), 'available'),
('Team Bravo Fire',   'fire',     'Barangay 1 & 3', '09200000002', (SELECT id FROM users WHERE email='carlos.responder@resqnow.test' LIMIT 1), 'available'),
('Medical Unit',      'medical',  'All Barangays',  '09200000003', (SELECT id FROM users WHERE email='ana.responder@resqnow.test' LIMIT 1), 'available'),
('Security & Traffic','security', 'Barangay 2 & 4', '09200000004', (SELECT id FROM users WHERE email='boy.responder@resqnow.test' LIMIT 1), 'available');

-- 8. Seed: team members
INSERT IGNORE INTO team_members (team_id, user_id)
SELECT t.id, u.id FROM response_teams t, users u
WHERE t.team_name='Team Alpha Rescue' AND u.email IN ('juan.responder@resqnow.test','maria.responder@resqnow.test');

INSERT IGNORE INTO team_members (team_id, user_id)
SELECT t.id, u.id FROM response_teams t, users u
WHERE t.team_name='Team Bravo Fire' AND u.email IN ('carlos.responder@resqnow.test','rosa.responder@resqnow.test');

INSERT IGNORE INTO team_members (team_id, user_id)
SELECT t.id, u.id FROM response_teams t, users u
WHERE t.team_name='Medical Unit' AND u.email IN ('ana.responder@resqnow.test','pedro.responder@resqnow.test');

INSERT IGNORE INTO team_members (team_id, user_id)
SELECT t.id, u.id FROM response_teams t, users u
WHERE t.team_name='Security & Traffic' AND u.email IN ('boy.responder@resqnow.test','lina.responder@resqnow.test');
