-- M4 Demo Seed
-- Populates incidents, assignments, and response_updates so every
-- Kanban column on the monitoring board has at least one card.
-- Idempotent: uses INSERT IGNORE / UPDATE WHERE NOT EXISTS patterns.
-- Run against: resqnow

SET @admin_id = (SELECT id FROM users WHERE role IN ('admin','personnel') LIMIT 1);

-- ── Ensure we have a known admin/personnel user ──────────────────────
-- If no admin exists yet, create one (password = admin123)
INSERT IGNORE INTO users (name, email, phone, password, role, status)
VALUES ('Admin User', 'admin@resqnow.test', '09100000001',
        '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK',
        'admin', 'approved');

INSERT IGNORE INTO users (name, email, phone, password, role, status)
VALUES ('Personnel One', 'personnel@resqnow.test', '09100000002',
        '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK',
        'personnel', 'approved');

-- Resident reporter
INSERT IGNORE INTO users (name, email, phone, password, role, status, barangay, home_address)
VALUES ('Test Resident', 'resident@resqnow.test', '09100000003',
        '$2y$12$6oIbgG0Ip7U8kOZe.K7Mwuq8hXCLEf2lG2uL5dOnBl3xbhJjLVRpK',
        'resident', 'approved', 'Barangay 1', '123 Rizal St, Barangay 1');

-- ── Refresh variable after potential inserts ─────────────────────────
SET @admin_id      = (SELECT id FROM users WHERE role = 'admin'     AND status = 'approved' LIMIT 1);
SET @personnel_id  = (SELECT id FROM users WHERE role = 'personnel' AND status = 'approved' LIMIT 1);
SET @resident_id   = (SELECT id FROM users WHERE role = 'resident'  AND status = 'approved' LIMIT 1);
SET @responder1_id = (SELECT id FROM users WHERE email = 'juan.responder@resqnow.test' LIMIT 1);
SET @responder2_id = (SELECT id FROM users WHERE email = 'carlos.responder@resqnow.test' LIMIT 1);
SET @responder3_id = (SELECT id FROM users WHERE email = 'ana.responder@resqnow.test' LIMIT 1);
SET @responder4_id = (SELECT id FROM users WHERE email = 'boy.responder@resqnow.test' LIMIT 1);

SET @team_rescue   = (SELECT id FROM response_teams WHERE team_name = 'Team Alpha Rescue' LIMIT 1);
SET @team_fire     = (SELECT id FROM response_teams WHERE team_name = 'Team Bravo Fire'   LIMIT 1);
SET @team_medical  = (SELECT id FROM response_teams WHERE team_name = 'Medical Unit'      LIMIT 1);
SET @team_security = (SELECT id FROM response_teams WHERE team_name = 'Security & Traffic' LIMIT 1);

-- ── Seed api_token for responder 1 (for API testing) ─────────────────
UPDATE users SET api_token = 'r3sp0nd3r1t0k3nabc123def456ghi789jkl000mn1'
WHERE email = 'juan.responder@resqnow.test' AND api_token IS NULL;

-- ── Incident 3: flood, verified, about to be assigned ─────────────────
INSERT IGNORE INTO incidents
  (code, hazard_type, triage_level, triage_score, unsure_ratio, status,
   is_emergency, source, address, location_lat, location_lng,
   description, reported_by, created_at)
VALUES
  ('RQN-2026-0003', 'flood', 'high', 10, 0.0000, 'verified',
   1, 'resident_web', 'Mabini St cor. Rizal Ave, Barangay 2', 14.5995, 120.9842,
   'Knee-deep flooding on the main road, water still rising. 3 families stranded.',
   @resident_id, DATE_SUB(NOW(), INTERVAL 90 MINUTE));

-- ── Incident 4: fire/critical → assigned (Team Bravo Fire) ───────────
INSERT IGNORE INTO incidents
  (code, hazard_type, triage_level, triage_score, unsure_ratio, status,
   is_emergency, source, address, location_lat, location_lng, assigned_team,
   assigned_team_id, assigned_by, assigned_at, description, reported_by, created_at)
VALUES
  ('RQN-2026-0004', 'fire', 'critical', 14, 0.0000, 'assigned',
   1, 'resident_mobile', 'Block 5 Lot 12, Barangay 3', 14.6015, 120.9830,
   'Team Bravo Fire', @team_fire, @admin_id, DATE_SUB(NOW(), INTERVAL 45 MINUTE),
   'House fire spreading to adjacent structure. LPG tank on site.',
   @resident_id, DATE_SUB(NOW(), INTERVAL 60 MINUTE));

INSERT IGNORE INTO assignments (incident_id, team_id, assigned_by, status, assigned_at)
SELECT i.id, @team_fire, @admin_id, 'active', DATE_SUB(NOW(), INTERVAL 45 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0004'
AND NOT EXISTS (SELECT 1 FROM assignments a WHERE a.incident_id = i.id AND a.status = 'active');

-- ── Incident 5: medical → acknowledged ───────────────────────────────
INSERT IGNORE INTO incidents
  (code, hazard_type, triage_level, triage_score, unsure_ratio, status,
   is_emergency, source, address, location_lat, location_lng, assigned_team,
   assigned_team_id, assigned_by, assigned_at, description, reported_by, created_at)
VALUES
  ('RQN-2026-0005', 'medical', 'critical', 13, 0.0000, 'acknowledged',
   1, 'resident_web', '45 Bonifacio St, Barangay 1', 14.5988, 120.9855,
   'Medical Unit', @team_medical, @admin_id, DATE_SUB(NOW(), INTERVAL 35 MINUTE),
   'Unconscious patient, severe bleeding, not breathing normally.',
   @resident_id, DATE_SUB(NOW(), INTERVAL 50 MINUTE));

INSERT IGNORE INTO assignments (incident_id, team_id, assigned_by, status, assigned_at, acknowledged_at)
SELECT i.id, @team_medical, @admin_id, 'active',
       DATE_SUB(NOW(), INTERVAL 35 MINUTE),
       DATE_SUB(NOW(), INTERVAL 28 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0005'
AND NOT EXISTS (SELECT 1 FROM assignments a WHERE a.incident_id = i.id AND a.status = 'active');

INSERT IGNORE INTO response_updates (incident_id, team_id, responder_id, update_type, action_code, message, created_at)
SELECT i.id, @team_medical, @responder3_id,
       'status_change', 'acknowledged',
       'Mission acknowledged by Ana Gonzalez (Medical Unit).',
       DATE_SUB(NOW(), INTERVAL 28 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0005'
AND NOT EXISTS (SELECT 1 FROM response_updates ru WHERE ru.incident_id = i.id AND ru.action_code = 'acknowledged');

-- ── Incident 6: earthquake → on_scene ────────────────────────────────
INSERT IGNORE INTO incidents
  (code, hazard_type, triage_level, triage_score, unsure_ratio, status,
   is_emergency, source, address, location_lat, location_lng, assigned_team,
   assigned_team_id, assigned_by, assigned_at, description, reported_by, created_at)
VALUES
  ('RQN-2026-0006', 'earthquake', 'high', 11, 0.0000, 'on_scene',
   1, 'resident_mobile', 'Purok 3, Barangay 4', 14.6030, 120.9810,
   'Team Alpha Rescue', @team_rescue, @personnel_id, DATE_SUB(NOW(), INTERVAL 55 MINUTE),
   'Moderate structural damage, 1 person trapped under debris, aftershocks ongoing.',
   @resident_id, DATE_SUB(NOW(), INTERVAL 70 MINUTE));

INSERT IGNORE INTO assignments (incident_id, team_id, assigned_by, status, assigned_at, acknowledged_at)
SELECT i.id, @team_rescue, @personnel_id, 'active',
       DATE_SUB(NOW(), INTERVAL 55 MINUTE),
       DATE_SUB(NOW(), INTERVAL 48 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0006'
AND NOT EXISTS (SELECT 1 FROM assignments a WHERE a.incident_id = i.id AND a.status = 'active');

INSERT IGNORE INTO response_updates (incident_id, team_id, responder_id, update_type, action_code, message, created_at)
SELECT i.id, @team_rescue, @responder1_id, 'status_change', 'acknowledged',
       'Mission acknowledged by Juan dela Cruz (Team Alpha Rescue).',
       DATE_SUB(NOW(), INTERVAL 48 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0006'
AND NOT EXISTS (SELECT 1 FROM response_updates ru WHERE ru.incident_id = i.id AND ru.action_code = 'acknowledged');

INSERT IGNORE INTO response_updates (incident_id, team_id, responder_id, update_type, action_code, message, created_at)
SELECT i.id, @team_rescue, @responder1_id, 'en_route', 'en_route',
       'Juan dela Cruz (Team Alpha Rescue) is en route to the scene.',
       DATE_SUB(NOW(), INTERVAL 42 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0006'
AND NOT EXISTS (SELECT 1 FROM response_updates ru WHERE ru.incident_id = i.id AND ru.action_code = 'en_route');

INSERT IGNORE INTO response_updates (incident_id, team_id, responder_id, update_type, action_code, message, created_at)
SELECT i.id, @team_rescue, @responder1_id, 'status_change', 'arrived_on_scene',
       'Juan dela Cruz (Team Alpha Rescue) has arrived on scene.',
       DATE_SUB(NOW(), INTERVAL 30 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0006'
AND NOT EXISTS (SELECT 1 FROM response_updates ru WHERE ru.incident_id = i.id AND ru.action_code = 'arrived_on_scene');

-- ── Incident 7: accident → in_progress with support request ──────────
INSERT IGNORE INTO incidents
  (code, hazard_type, triage_level, triage_score, unsure_ratio, status,
   is_emergency, source, address, location_lat, location_lng, assigned_team,
   assigned_team_id, assigned_by, assigned_at, description, reported_by, created_at)
VALUES
  ('RQN-2026-0007', 'accident', 'critical', 15, 0.0000, 'in_progress',
   1, 'resident_web', 'National Highway near Barangay 4 marker', 14.6045, 120.9795,
   'Security & Traffic', @team_security, @admin_id, DATE_SUB(NOW(), INTERVAL 80 MINUTE),
   'Multi-vehicle collision. 3 vehicles. Serious injuries. One person trapped.',
   @resident_id, DATE_SUB(NOW(), INTERVAL 95 MINUTE));

INSERT IGNORE INTO assignments (incident_id, team_id, assigned_by, status, assigned_at, acknowledged_at)
SELECT i.id, @team_security, @admin_id, 'active',
       DATE_SUB(NOW(), INTERVAL 80 MINUTE),
       DATE_SUB(NOW(), INTERVAL 73 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0007'
AND NOT EXISTS (SELECT 1 FROM assignments a WHERE a.incident_id = i.id AND a.status = 'active');

INSERT IGNORE INTO response_updates (incident_id, team_id, responder_id, update_type, action_code, message, created_at)
SELECT i.id, @team_security, @responder4_id, 'status_change', 'acknowledged',
       'Mission acknowledged by Boy Aquino (Security & Traffic).',
       DATE_SUB(NOW(), INTERVAL 73 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0007'
AND NOT EXISTS (SELECT 1 FROM response_updates ru WHERE ru.incident_id = i.id AND ru.action_code = 'acknowledged');

INSERT IGNORE INTO response_updates (incident_id, team_id, responder_id, update_type, action_code, message, created_at)
SELECT i.id, @team_security, @responder4_id, 'status_change', 'arrived_on_scene',
       'Boy Aquino (Security & Traffic) has arrived on scene.',
       DATE_SUB(NOW(), INTERVAL 65 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0007'
AND NOT EXISTS (SELECT 1 FROM response_updates ru WHERE ru.incident_id = i.id AND ru.action_code = 'arrived_on_scene');

INSERT IGNORE INTO response_updates (incident_id, team_id, responder_id, update_type, action_code, message, created_at)
SELECT i.id, @team_security, @responder4_id, 'status_change', 'rescue_in_progress',
       'Boy Aquino (Security & Traffic): Rescue in progress — extracting trapped victim.',
       DATE_SUB(NOW(), INTERVAL 50 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0007'
AND NOT EXISTS (SELECT 1 FROM response_updates ru WHERE ru.incident_id = i.id AND ru.action_code = 'rescue_in_progress');

-- Support request (unacknowledged — shows red pulsing card on board)
INSERT IGNORE INTO response_updates
  (incident_id, team_id, responder_id, update_type, action_code,
   message, support_requested, support_type, created_at)
SELECT i.id, @team_security, @responder4_id, 'support_request', 'support_requested',
       'Boy Aquino (Security & Traffic) requested ambulance support.',
       1, 'ambulance', DATE_SUB(NOW(), INTERVAL 20 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0007'
AND NOT EXISTS (SELECT 1 FROM response_updates ru
                WHERE ru.incident_id = i.id
                  AND ru.support_requested = 1
                  AND ru.support_type = 'ambulance');

-- ── Update team statuses to match assignments ─────────────────────────
UPDATE response_teams SET status = 'on_mission'
WHERE team_name IN ('Team Bravo Fire', 'Medical Unit', 'Team Alpha Rescue', 'Security & Traffic');

-- ── Write status_history for all seeded incidents ────────────────────
INSERT IGNORE INTO incident_status_history (incident_id, from_status, to_status, changed_by, notes, created_at)
SELECT i.id, 'submitted', 'verified', @admin_id, 'Verified by Admin User', DATE_SUB(NOW(), INTERVAL 85 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0003'
AND NOT EXISTS (SELECT 1 FROM incident_status_history h WHERE h.incident_id = i.id AND h.to_status = 'verified');

INSERT IGNORE INTO incident_status_history (incident_id, from_status, to_status, changed_by, notes, created_at)
SELECT i.id, 'verified', 'assigned', @admin_id, 'Assigned to Team Bravo Fire', DATE_SUB(NOW(), INTERVAL 45 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0004'
AND NOT EXISTS (SELECT 1 FROM incident_status_history h WHERE h.incident_id = i.id AND h.to_status = 'assigned');

INSERT IGNORE INTO incident_status_history (incident_id, from_status, to_status, changed_by, notes, created_at)
SELECT i.id, 'assigned', 'acknowledged', @responder3_id, 'Acknowledged by Ana Gonzalez (Medical Unit)', DATE_SUB(NOW(), INTERVAL 28 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0005'
AND NOT EXISTS (SELECT 1 FROM incident_status_history h WHERE h.incident_id = i.id AND h.to_status = 'acknowledged');

INSERT IGNORE INTO incident_status_history (incident_id, from_status, to_status, changed_by, notes, created_at)
SELECT i.id, 'acknowledged', 'on_scene', @responder1_id, 'Arrived on scene — Team Alpha Rescue', DATE_SUB(NOW(), INTERVAL 30 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0006'
AND NOT EXISTS (SELECT 1 FROM incident_status_history h WHERE h.incident_id = i.id AND h.to_status = 'on_scene');

INSERT IGNORE INTO incident_status_history (incident_id, from_status, to_status, changed_by, notes, created_at)
SELECT i.id, 'on_scene', 'in_progress', @responder4_id, 'Rescue in progress — Security & Traffic', DATE_SUB(NOW(), INTERVAL 50 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0007'
AND NOT EXISTS (SELECT 1 FROM incident_status_history h WHERE h.incident_id = i.id AND h.to_status = 'in_progress');

-- ── Verification records for seeded verified incidents ────────────────
INSERT IGNORE INTO verification_records (incident_id, action, original_triage_level, new_triage_level, verified_by, created_at)
SELECT i.id, 'verified', 'high', 'high', @admin_id, DATE_SUB(NOW(), INTERVAL 85 MINUTE)
FROM incidents i WHERE i.code = 'RQN-2026-0003'
AND NOT EXISTS (SELECT 1 FROM verification_records v WHERE v.incident_id = i.id AND v.action = 'verified');

SELECT
  CONCAT('Teams: ', COUNT(*)) AS summary FROM response_teams
UNION ALL
SELECT CONCAT('Responders: ', COUNT(*)) FROM users WHERE role = 'responder'
UNION ALL
SELECT CONCAT('Incidents total: ', COUNT(*)) FROM incidents
UNION ALL
SELECT CONCAT('Active assignments: ', COUNT(*)) FROM assignments WHERE status = 'active'
UNION ALL
SELECT CONCAT('Response updates: ', COUNT(*)) FROM response_updates
UNION ALL
SELECT CONCAT('Pending support requests: ', COUNT(*)) FROM response_updates WHERE support_requested = 1 AND support_acknowledged_at IS NULL;
