-- ============================================================
-- ResQNow Demo Seed Data
-- Realistic data for Barangay Camunatan, City of Ilagan
-- Run AFTER schema.sql + all migrations
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE response_updates;
TRUNCATE TABLE assignments;
TRUNCATE TABLE incident_answers;
TRUNCATE TABLE incident_status_history;
TRUNCATE TABLE verification_records;
TRUNCATE TABLE notifications;
TRUNCATE TABLE audit_logs;
TRUNCATE TABLE announcements;
TRUNCATE TABLE team_members;
TRUNCATE TABLE response_teams;
TRUNCATE TABLE incidents;
TRUNCATE TABLE safety_guides;
TRUNCATE TABLE emergency_contacts;
TRUNCATE TABLE barangays;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ── Barangays ────────────────────────────────────────────────────────
INSERT INTO barangays (id, name) VALUES
(1, 'Barangay Camunatan, City of Ilagan'),
(2, 'Barangay 124, Zone 4'),
(3, 'Barangay 125, Zone 5'),
(4, 'Barangay 126, Zone 5');

-- ── Users ─────────────────────────────────────────────────────────────
-- Password for all: admin123 / responder123 / resident123
-- Admin hash = admin123, Responder hash = responder123, Resident hash = resident123
INSERT INTO users (id, name, email, phone, password, role, status, barangay) VALUES
-- Admin
(1, 'Admin ResQNow', 'admin@resqnow.test', '09100000001',
 '$2y$10$mr.6kmZUG/Qiqm1NVAawculjjZDiBOkiDaOSkI9eoLfqAjw4o35y.', 'admin', 'approved', 'Barangay Camunatan, City of Ilagan'),
-- Original admin account (kept for backwards compatibility)
(24,'Admin',          'admin@test.com',      '09170000002',
 '$2y$10$mr.6kmZUG/Qiqm1NVAawculjjZDiBOkiDaOSkI9eoLfqAjw4o35y.', 'admin', 'approved', 'Barangay Camunatan, City of Ilagan'),
-- Personnel
(2, 'Maria Santos',  'maria@resqnow.test',  '09100000002',
 '$2y$10$mr.6kmZUG/Qiqm1NVAawculjjZDiBOkiDaOSkI9eoLfqAjw4o35y.', 'personnel', 'approved', 'Barangay Camunatan, City of Ilagan'),
(3, 'Jose Dela Cruz','jose@resqnow.test',   '09100000003',
 '$2y$10$mr.6kmZUG/Qiqm1NVAawculjjZDiBOkiDaOSkI9eoLfqAjw4o35y.', 'personnel', 'approved', 'Barangay 124, Zone 4'),
-- Responders (Team Alpha Rescue)
(4, 'Juan Reyes',    'juan.r@resqnow.test', '09200000001',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay Camunatan, City of Ilagan'),
(5, 'Ana Cruz',      'ana.c@resqnow.test',  '09200000002',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay Camunatan, City of Ilagan'),
(6, 'Pedro Lim',     'pedro.l@resqnow.test','09200000003',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay 124, Zone 4'),
-- Responders (Team Bravo Fire)
(7, 'Carlos Bautista','carlos.b@resqnow.test','09200000004',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay 125, Zone 5'),
(8, 'Rosa Mendoza',  'rosa.m@resqnow.test',  '09200000005',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay 125, Zone 5'),
-- Responders (Medical Unit)
(9, 'Lina Torres',   'lina.t@resqnow.test',  '09200000006',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay 126, Zone 5'),
(10,'Boy Aquino',    'boy.a@resqnow.test',   '09200000007',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay 126, Zone 5'),
-- Responders (Security & Traffic)
(11,'Ben Garcia',    'ben.g@resqnow.test',   '09200000008',
 '$2y$10$fGg.FdiW3zAr/wxkqOks5uPafNdZ/rN8rM2T6sqiQnEB7JE07PfWq', 'responder', 'approved', 'Barangay Camunatan, City of Ilagan'),
-- Residents (12 total)
(12,'Nena Villanueva','nena.v@gmail.com',    '09300000001',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay Camunatan, City of Ilagan'),
(13,'Roberto Santos', 'rob.s@gmail.com',     '09300000002',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay Camunatan, City of Ilagan'),
(14,'Corazon Reyes',  'cora.r@gmail.com',    '09300000003',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay 124, Zone 4'),
(15,'Felix Ramos',    'felix.r@gmail.com',   '09300000004',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay 124, Zone 4'),
(16,'Gloria Ocampo',  'gloria.o@gmail.com',  '09300000005',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay 125, Zone 5'),
(17,'Danny Pascual',  'danny.p@gmail.com',   '09300000006',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay 125, Zone 5'),
(18,'Eloisa Navarro', 'eloisa.n@gmail.com',  '09300000007',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay 126, Zone 5'),
(19,'Rodrigo Aquino', 'rod.a@gmail.com',     '09300000008',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay 126, Zone 5'),
(20,'Maricel Flores', 'maricel.f@gmail.com', '09300000009',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay Camunatan, City of Ilagan'),
(21,'Arturo Soriano', 'arturo.s@gmail.com',  '09300000010',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'approved', 'Barangay 124, Zone 4'),
(22,'Tessie Magno',   'tessie.m@gmail.com',  '09300000011',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'pending',  'Barangay 125, Zone 5'),
(23,'Lito Macaraeg',  'lito.m@gmail.com',    '09300000012',
 '$2y$10$1iIo4SfApfbKQw1zv53HTOJHSJMyZeq8svX4IaEDBOClgGeVZdfVq', 'resident', 'pending',  'Barangay Camunatan, City of Ilagan');

-- ── Response Teams ───────────────────────────────────────────────────
INSERT INTO response_teams (id, team_name, unit_type, coverage_area, contact_no, leader_id, status) VALUES
(1, 'Team Alpha Rescue',  'rescue',   'Barangay 123 & 124', '09400000001', 4,  'available'),
(2, 'Team Bravo Fire',    'fire',     'Barangay 125 & 126', '09400000002', 7,  'available'),
(3, 'Medical Unit',       'medical',  'All Barangays',      '09400000003', 9,  'available'),
(4, 'Security & Traffic', 'security', 'Barangay 123 & 125', '09400000004', 11, 'available');

INSERT INTO team_members (team_id, user_id) VALUES
(1,4),(1,5),(1,6),
(2,7),(2,8),
(3,9),(3,10),
(4,11);

-- ── Incidents (20 across all statuses, hazards, triage levels) ────────
INSERT INTO incidents
  (id, code, hazard_type, triage_level, triage_score, unsure_ratio, status,
   is_emergency, source, address, location_lat, location_lng,
   assigned_team, assigned_team_id, assigned_by, assigned_at, resolved_at,
   description, reported_by, barangay_id, created_at)
VALUES
-- 1. SUBMITTED — pending verification
(1,'RQN-2026-0001','fire','critical',14,0.00,'submitted',1,'resident_web',
 '123 Rizal Street, Barangay 123',17.1500,121.8872,NULL,NULL,NULL,NULL,NULL,
 'House fire spreading quickly, LPG tank nearby.',12,1, NOW() - INTERVAL 2 HOUR),

-- 2. SUBMITTED — pending_reassessment (all-unsure)
(2,'RQN-2026-0002','flood','pending_reassessment',0,0.80,'submitted',1,'resident_mobile',
 'Mabini Ave cor. Quezon St',17.1496,121.8895,NULL,NULL,NULL,NULL,NULL,
 'Not sure how bad the flooding is.', 13,1, NOW() - INTERVAL 3 HOUR),

-- 3. VERIFIED — waiting assignment
(3,'RQN-2026-0003','medical','critical',13,0.00,'verified',1,'resident_web',
 '45 Bonifacio St, Barangay 124',17.1492,121.8885,NULL,NULL,NULL,NULL,NULL,
 'Elderly man unconscious, not breathing normally.',14,2, NOW() - INTERVAL 5 HOUR),

-- 4. VERIFIED — high triage
(4,'RQN-2026-0004','earthquake','high',10,0.00,'verified',1,'resident_mobile',
 'Purok 3, Barangay 125',17.1478,121.8887,NULL,NULL,NULL,NULL,NULL,
 'Moderate damage, cracks in walls, still feeling aftershocks.',16,3, NOW() - INTERVAL 6 HOUR),

-- 5. ASSIGNED — critical fire (Team Bravo)
(5,'RQN-2026-0005','fire','critical',15,0.00,'assigned',1,'resident_web',
 'Block 5 Lot 12, Barangay 125',17.1490,121.8892,
 'Team Bravo Fire',2,1, NOW() - INTERVAL 80 MINUTE, NULL,
 'Two-storey house fully involved, LPG and electrical hazards.',17,3, NOW() - INTERVAL 100 MINUTE),

-- 6. ACKNOWLEDGED — accident (Team Alpha)
(6,'RQN-2026-0006','accident','critical',15,0.00,'acknowledged',1,'resident_mobile',
 'National Highway near Barangay 124',17.1505,121.8873,
 'Team Alpha Rescue',1,2, NOW() - INTERVAL 55 MINUTE, NULL,
 'Multi-vehicle collision, 3 vehicles, person trapped.',18,2, NOW() - INTERVAL 70 MINUTE),

-- 7. ON_SCENE — earthquake (Team Alpha)
(7,'RQN-2026-0007','earthquake','high',11,0.00,'on_scene',1,'resident_web',
 'Purok 7, Barangay 126',17.1466,121.8908,
 'Team Alpha Rescue',1,1, NOW() - INTERVAL 90 MINUTE, NULL,
 'Person trapped under collapsed wall.',19,4, NOW() - INTERVAL 110 MINUTE),

-- 8. IN_PROGRESS with support request (Medical Unit)
(8,'RQN-2026-0008','medical','critical',14,0.00,'in_progress',1,'resident_mobile',
 '78 Luna St, Barangay 123',17.1507,121.8879,
 'Medical Unit',3,2, NOW() - INTERVAL 120 MINUTE, NULL,
 'Severe road accident victim, bleeding heavily, unconscious.',20,1, NOW() - INTERVAL 140 MINUTE),

-- 9. IN_PROGRESS — flood (Team Alpha)
(9,'RQN-2026-0009','flood','high',10,0.00,'in_progress',1,'resident_web',
 'Low-lying area, Barangay 124',17.1488,121.8896,
 'Team Alpha Rescue',1,1, NOW() - INTERVAL 100 MINUTE, NULL,
 'Water knee-deep, still rising, 6 families stranded.',21,2, NOW() - INTERVAL 120 MINUTE),

-- 10. RESOLVED (yesterday)
(10,'RQN-2026-0010','fire','high',9,0.00,'resolved',1,'resident_web',
 'Sitio Malaya, Barangay 123',17.1502,121.8860,
 'Team Bravo Fire',2,1, NOW() - INTERVAL 26 HOUR, NOW() - INTERVAL 24 HOUR,
 'Kitchen fire contained before spreading.',12,1, NOW() - INTERVAL 28 HOUR),

-- 11. RESOLVED (2 days ago)
(11,'RQN-2026-0011','medical','moderate',6,0.00,'resolved',1,'resident_mobile',
 '22 Mabini St, Barangay 125',17.1495,121.8899,
 'Medical Unit',3,2, NOW() - INTERVAL 50 HOUR, NOW() - INTERVAL 48 HOUR,
 'Elderly patient with hypertensive crisis, stable after transport.',15,3, NOW() - INTERVAL 52 HOUR),

-- 12. RESOLVED (3 days ago)
(12,'RQN-2026-0012','flood','critical',13,0.00,'resolved',1,'resident_web',
 'Riverside area, Barangay 126',17.1478,121.8912,
 'Team Alpha Rescue',1,1, NOW() - INTERVAL 74 HOUR, NOW() - INTERVAL 70 HOUR,
 'Neck-deep flooding, 10+ families rescued.',22,4, NOW() - INTERVAL 76 HOUR),

-- 13. REJECTED
(13,'RQN-2026-0013','other','low',2,0.10,'rejected',0,'resident_mobile',
 'Barangay 123 playground',17.1508,121.8879,NULL,NULL,NULL,NULL,NULL,
 'Stray dogs in the playground.', 13,1, NOW() - INTERVAL 48 HOUR),

-- 14. DUPLICATE
(14,'RQN-2026-0014','fire','critical',14,0.00,'duplicate',1,'resident_mobile',
 '123 Rizal Street, Barangay 123',17.1500,121.8872,NULL,NULL,NULL,NULL,NULL,
 'Smoke coming from house on Rizal St.',13,1, NOW() - INTERVAL 1 HOUR + INTERVAL 10 MINUTE),

-- 15. VERIFIED — security
(15,'RQN-2026-0015','security','moderate',7,0.00,'verified',1,'resident_web',
 'Barangay 124 hall',17.1499,121.8889,NULL,NULL,NULL,NULL,NULL,
 'Group of armed strangers spotted near the barangay hall.',14,2, NOW() - INTERVAL 4 HOUR),

-- 16. SUBMITTED — low triage, evacuation
(16,'RQN-2026-0016','evacuation','low',3,0.00,'submitted',0,'admin_manual',
 'Sitio A, Barangay 126',17.1469,121.8900,NULL,NULL,NULL,NULL,NULL,
 'Preventive evacuation for 5 families near riverbank.',1,4, NOW() - INTERVAL 7 HOUR),

-- 17. ASSIGNED — security
(17,'RQN-2026-0017','security','high',9,0.00,'assigned',1,'resident_web',
 'Commercial area, Barangay 125',17.1486,121.8885,
 'Security & Traffic',4,2, NOW() - INTERVAL 40 MINUTE, NULL,
 'Physical altercation with weapons reported.',15,3, NOW() - INTERVAL 60 MINUTE),

-- 18. RESOLVED
(18,'RQN-2026-0018','accident','moderate',6,0.00,'resolved',1,'resident_mobile',
 'Barangay 123 main road',17.1500,121.8868,
 'Team Alpha Rescue',1,1, NOW() - INTERVAL 10 HOUR, NOW() - INTERVAL 8 HOUR,
 'Minor vehicular accident, no injuries.',16,1, NOW() - INTERVAL 12 HOUR),

-- 19. IN_PROGRESS — pending_reassessment then reassessed
(19,'RQN-2026-0019','earthquake','moderate',5,0.60,'in_progress',1,'resident_web',
 'Purok 5, Barangay 124',17.1493,121.8896,
 'Team Alpha Rescue',1,2, NOW() - INTERVAL 35 MINUTE, NULL,
 'Structural damage, unsure of severity.',21,2, NOW() - INTERVAL 50 MINUTE),

-- 20. SUBMITTED (fresh)
(20,'RQN-2026-0020','medical','high',9,0.00,'submitted',1,'resident_mobile',
 'Barangay 126 evacuation center',17.1490,121.8920,NULL,NULL,NULL,NULL,NULL,
 'Child with high fever and seizures.',23,4, NOW() - INTERVAL 15 MINUTE);

-- ── Incident Answers (sampling for key incidents) ────────────────────
-- Incident 1 (fire/critical): trapped=Yes(4), people=6+(3), spreading=Yes(2), hazards=Yes(1), vuln=Yes
INSERT INTO incident_answers (incident_id,question_key,question_text,answer_label,weight,points_scored,is_unsure) VALUES
(1,'trapped','Is anyone trapped?','Yes',2,4,0),
(1,'people_affected','Estimated number of people affected/trapped','6+',1,3,0),
(1,'spreading','Is fire spreading to adjacent structures?','Yes',1,2,0),
(1,'hazards','Are there LPG/chemical/electrical hazards?','Yes',1,1,0),
(1,'vulnerability','Does anyone involved need urgent assistance?','Yes (+2 priority bonus)',1,2,0);

-- Incident 2 (all-unsure → pending_reassessment)
INSERT INTO incident_answers (incident_id,question_key,question_text,answer_label,weight,points_scored,is_unsure) VALUES
(2,'water_level','Current water level','Unsure',2,0,1),
(2,'people_affected','Estimated number of people affected','Unsure',1,0,1),
(2,'rising','Is the water level still rising?','Unsure',1,0,1),
(2,'vulnerability','Does anyone involved need urgent assistance?','Unsure',1,0,1);

-- Incident 8 (medical/critical in_progress with support)
INSERT INTO incident_answers (incident_id,question_key,question_text,answer_label,weight,points_scored,is_unsure) VALUES
(8,'consciousness','Patient consciousness level','Unconscious',2,4,0),
(8,'bleeding','Severity of bleeding','Severe',1,4,0),
(8,'breathing','Breathing status','Difficult',1,1,0),
(8,'vulnerability','Does anyone involved need urgent assistance?','Yes (+2 priority bonus)',1,2,0);

-- ── Verification Records ─────────────────────────────────────────────
INSERT INTO verification_records (incident_id,action,original_triage_level,new_triage_level,reason,verified_by,created_at) VALUES
(3,'verified','critical','critical',NULL,2, NOW() - INTERVAL 4 HOUR + INTERVAL 30 MINUTE),
(4,'verified','high','high',NULL,2, NOW() - INTERVAL 5 HOUR),
(2,'reassessed','pending_reassessment','high','Field observation confirmed knee-deep flooding. Triage upgraded.',2, NOW() - INTERVAL 2 HOUR + INTERVAL 30 MINUTE),
(13,'rejected','low','low','Not an emergency incident. Out of scope.',1, NOW() - INTERVAL 47 HOUR),
(14,'duplicate','critical','critical',NULL,2, NOW() - INTERVAL 55 MINUTE),
(15,'verified','moderate','moderate',NULL,2, NOW() - INTERVAL 3 HOUR),
(5,'verified','critical','critical',NULL,1, NOW() - INTERVAL 95 MINUTE),
(6,'verified','critical','critical',NULL,2, NOW() - INTERVAL 65 MINUTE),
(10,'verified','high','high',NULL,1, NOW() - INTERVAL 27 HOUR),
(10,'reassessed','high','high','Confirmed kitchen fire, not spreading.',1, NOW() - INTERVAL 27 HOUR);

-- ── Status History ────────────────────────────────────────────────────
INSERT INTO incident_status_history (incident_id,from_status,to_status,changed_by,notes,created_at) VALUES
-- Incident 3 lifecycle
(3,'submitted','verified',2,'Verified by Maria Santos',  NOW() - INTERVAL 4 HOUR + INTERVAL 30 MINUTE),
-- Incident 4
(4,'submitted','verified',2,'Verified by Maria Santos',  NOW() - INTERVAL 5 HOUR),
-- Incident 5 (fire — assigned)
(5,'submitted','verified',1,'Verified by Admin',          NOW() - INTERVAL 95 MINUTE),
(5,'verified','assigned',1,'Assigned to Team Bravo Fire', NOW() - INTERVAL 80 MINUTE),
-- Incident 6 (accident — acknowledged)
(6,'submitted','verified',2,'Verified by Maria Santos',   NOW() - INTERVAL 65 MINUTE),
(6,'verified','assigned',2,'Assigned to Team Alpha Rescue',NOW() - INTERVAL 55 MINUTE),
(6,'assigned','acknowledged',4,'Acknowledged by Juan Reyes', NOW() - INTERVAL 40 MINUTE),
-- Incident 7 (on_scene)
(7,'submitted','verified',1,'Verified',                   NOW() - INTERVAL 105 MINUTE),
(7,'verified','assigned',1,'Assigned to Team Alpha Rescue',NOW() - INTERVAL 90 MINUTE),
(7,'assigned','acknowledged',4,'Acknowledged',            NOW() - INTERVAL 78 MINUTE),
(7,'acknowledged','on_scene',4,'Juan Reyes arrived on scene',NOW() - INTERVAL 60 MINUTE),
-- Incident 8 (in_progress with support)
(8,'submitted','verified',2,'Verified by Maria Santos',   NOW() - INTERVAL 135 MINUTE),
(8,'verified','assigned',2,'Assigned to Medical Unit',    NOW() - INTERVAL 120 MINUTE),
(8,'assigned','acknowledged',9,'Acknowledged by Lina Torres', NOW() - INTERVAL 110 MINUTE),
(8,'acknowledged','on_scene',9,'Lina Torres on scene',    NOW() - INTERVAL 95 MINUTE),
(8,'on_scene','in_progress',9,'Administering first aid',  NOW() - INTERVAL 80 MINUTE),
-- Incident 9 (flood in_progress)
(9,'submitted','verified',1,'Verified',                   NOW() - INTERVAL 115 MINUTE),
(9,'verified','assigned',1,'Assigned to Team Alpha',      NOW() - INTERVAL 100 MINUTE),
(9,'assigned','acknowledged',5,'Ana Cruz acknowledged',   NOW() - INTERVAL 90 MINUTE),
(9,'acknowledged','on_scene',5,'Ana Cruz on scene',       NOW() - INTERVAL 75 MINUTE),
(9,'on_scene','in_progress',5,'Evacuating residents',     NOW() - INTERVAL 55 MINUTE),
-- Incident 10 (resolved fire)
(10,'submitted','verified',1,'Verified',                  NOW() - INTERVAL 27 HOUR),
(10,'verified','assigned',1,'Assigned to Team Bravo',     NOW() - INTERVAL 26 HOUR),
(10,'assigned','acknowledged',7,'Carlos acknowledged',    NOW() - INTERVAL 25 HOUR + INTERVAL 45 MINUTE),
(10,'acknowledged','on_scene',7,'Carlos on scene',        NOW() - INTERVAL 25 HOUR + INTERVAL 15 MINUTE),
(10,'on_scene','in_progress',7,'Extinguishing fire',      NOW() - INTERVAL 25 HOUR),
(10,'in_progress','resolved',7,'Fire extinguished, area safe.', NOW() - INTERVAL 24 HOUR),
-- Incident 12 (flood resolved)
(12,'submitted','verified',1,'Verified emergency',        NOW() - INTERVAL 75 HOUR),
(12,'verified','assigned',1,'Assigned to Team Alpha',     NOW() - INTERVAL 74 HOUR),
(12,'assigned','acknowledged',4,'Juan acknowledged',      NOW() - INTERVAL 73 HOUR + INTERVAL 30 MINUTE),
(12,'acknowledged','on_scene',4,'Team on scene',          NOW() - INTERVAL 73 HOUR),
(12,'on_scene','in_progress',4,'Evacuating families',     NOW() - INTERVAL 72 HOUR),
(12,'in_progress','resolved',4,'All 10 families evacuated safely.', NOW() - INTERVAL 70 HOUR),
-- Incident 13 (rejected)
(13,'submitted','rejected',1,'Not an emergency.',         NOW() - INTERVAL 47 HOUR),
-- Incident 14 (duplicate)
(14,'submitted','duplicate',2,'Duplicate of RQN-2026-0001', NOW() - INTERVAL 55 MINUTE),
-- Incident 17 (security assigned)
(17,'submitted','verified',2,'Verified',                  NOW() - INTERVAL 55 MINUTE),
(17,'verified','assigned',2,'Assigned to Security & Traffic', NOW() - INTERVAL 40 MINUTE),
-- Incident 19
(19,'submitted','verified',2,'Verified',                  NOW() - INTERVAL 45 MINUTE),
(19,'verified','assigned',2,'Assigned to Team Alpha',     NOW() - INTERVAL 35 MINUTE);

-- ── Assignments ───────────────────────────────────────────────────────
INSERT INTO assignments (id,incident_id,team_id,assigned_by,status,assigned_at,acknowledged_at,resolved_at) VALUES
(1, 5, 2,1,'active',   NOW() - INTERVAL 80 MINUTE, NULL, NULL),
(2, 6, 1,2,'active',   NOW() - INTERVAL 55 MINUTE, NOW() - INTERVAL 40 MINUTE, NULL),
(3, 7, 1,1,'active',   NOW() - INTERVAL 90 MINUTE, NOW() - INTERVAL 78 MINUTE, NULL),
(4, 8, 3,2,'active',   NOW() - INTERVAL 120 MINUTE,NOW() - INTERVAL 110 MINUTE,NULL),
(5, 9, 1,1,'active',   NOW() - INTERVAL 100 MINUTE,NOW() - INTERVAL 90 MINUTE, NULL),
(6,10, 2,1,'completed',NOW() - INTERVAL 26 HOUR,   NOW() - INTERVAL 25 HOUR + INTERVAL 45 MINUTE, NOW() - INTERVAL 24 HOUR),
(7,11, 3,2,'completed',NOW() - INTERVAL 50 HOUR,   NOW() - INTERVAL 49 HOUR + INTERVAL 30 MINUTE, NOW() - INTERVAL 48 HOUR),
(8,12, 1,1,'completed',NOW() - INTERVAL 74 HOUR,   NOW() - INTERVAL 73 HOUR + INTERVAL 30 MINUTE, NOW() - INTERVAL 70 HOUR),
(9,17, 4,2,'active',   NOW() - INTERVAL 40 MINUTE, NULL, NULL),
(10,19,1,2,'active',   NOW() - INTERVAL 35 MINUTE, NULL, NULL);

-- Update teams to on_mission for active assignments
UPDATE response_teams SET status = 'on_mission' WHERE id IN (1,2,3,4);

-- ── Response Updates ─────────────────────────────────────────────────
INSERT INTO response_updates (incident_id,team_id,responder_id,update_type,action_code,message,support_requested,support_type,created_at) VALUES
-- Incident 6 (accident, acknowledged)
(6,1,4,'status_change','acknowledged','Juan Reyes (Team Alpha Rescue) acknowledged mission.',0,NULL, NOW() - INTERVAL 40 MINUTE),
(6,1,4,'en_route','en_route','Juan Reyes en route to scene.',0,NULL, NOW() - INTERVAL 35 MINUTE),
-- Incident 7 (on_scene)
(7,1,4,'status_change','acknowledged','Juan Reyes (Team Alpha Rescue) acknowledged.',0,NULL, NOW() - INTERVAL 78 MINUTE),
(7,1,4,'en_route','en_route','Team en route.',0,NULL, NOW() - INTERVAL 70 MINUTE),
(7,1,4,'status_change','arrived_on_scene','Juan Reyes arrived on scene.',0,NULL, NOW() - INTERVAL 60 MINUTE),
-- Incident 8 (in_progress + SUPPORT REQUEST)
(8,3,9,'status_change','acknowledged','Lina Torres (Medical Unit) acknowledged.',0,NULL, NOW() - INTERVAL 110 MINUTE),
(8,3,9,'en_route','en_route','Medical Unit en route.',0,NULL, NOW() - INTERVAL 100 MINUTE),
(8,3,9,'status_change','arrived_on_scene','Lina Torres on scene.',0,NULL, NOW() - INTERVAL 95 MINUTE),
(8,3,9,'status_change','first_aid','Lina Torres (Medical Unit): Administering first aid — patient critical.',0,NULL, NOW() - INTERVAL 80 MINUTE),
(8,3,9,'support_request','support_requested','Lina Torres (Medical Unit) requested ambulance support.',1,'ambulance', NOW() - INTERVAL 60 MINUTE),
-- Incident 9 (flood in_progress)
(9,1,5,'status_change','acknowledged','Ana Cruz (Team Alpha Rescue) acknowledged.',0,NULL, NOW() - INTERVAL 90 MINUTE),
(9,1,5,'status_change','arrived_on_scene','Ana Cruz arrived on scene.',0,NULL, NOW() - INTERVAL 75 MINUTE),
(9,1,5,'status_change','evacuating','Ana Cruz (Team Alpha Rescue): Evacuating residents — 6 families being moved.',0,NULL, NOW() - INTERVAL 55 MINUTE),
-- Incident 10 (resolved)
(10,2,7,'status_change','acknowledged','Carlos Bautista acknowledged.',0,NULL, NOW() - INTERVAL 25 HOUR + INTERVAL 45 MINUTE),
(10,2,7,'status_change','arrived_on_scene','Carlos Bautista on scene.',0,NULL, NOW() - INTERVAL 25 HOUR + INTERVAL 15 MINUTE),
(10,2,7,'status_change','extinguishing','Carlos Bautista (Team Bravo Fire): Extinguishing fire.',0,NULL, NOW() - INTERVAL 25 HOUR),
(10,2,7,'resolved','incident_resolved','Carlos Bautista: Fire fully extinguished. No casualties. Structure damaged.',0,NULL, NOW() - INTERVAL 24 HOUR),
-- Incident 12 (flood resolved)
(12,1,4,'status_change','acknowledged','Juan Reyes (Team Alpha Rescue) acknowledged.',0,NULL, NOW() - INTERVAL 73 HOUR + INTERVAL 30 MINUTE),
(12,1,4,'status_change','arrived_on_scene','Team on scene.',0,NULL, NOW() - INTERVAL 73 HOUR),
(12,1,4,'status_change','evacuating','Juan Reyes: Evacuating 10 families by boat.',0,NULL, NOW() - INTERVAL 72 HOUR),
(12,1,4,'resolved','incident_resolved','Juan Reyes: All 10 families safely evacuated to Barangay Hall.',0,NULL, NOW() - INTERVAL 70 HOUR);

-- Update resolved_at on incidents
UPDATE incidents SET resolved_at = NOW() - INTERVAL 24 HOUR WHERE id = 10;
UPDATE incidents SET resolved_at = NOW() - INTERVAL 48 HOUR WHERE id = 11;
UPDATE incidents SET resolved_at = NOW() - INTERVAL 70 HOUR WHERE id = 12;
UPDATE incidents SET resolved_at = NOW() - INTERVAL 8 HOUR  WHERE id = 18;

-- ── Announcements ─────────────────────────────────────────────────────
INSERT INTO announcements (title, body, hazard_type, is_active, created_by) VALUES
('⚠ Elevated Flood Alert — Barangays 124 & 126',
 'LPA approaching Luzon. Residents in low-lying areas should prepare evacuation bags and monitor water levels. Barangay emergency hotline: 09400000001.',
 'flood', 1, 1),
('🔥 Fire Safety Campaign This Week',
 'Schedule your LPG inspection at the Barangay Hall (Mon-Fri, 8AM-5PM). Expired tanks are a fire risk. BFP inspectors are available free of charge.',
 'fire', 1, 2),
('📢 Mass Casualty Drill — October 20',
 'Mandatory participation for all response team members. Civilians may observe at Barangay Plaza from 9AM.',
 NULL, 1, 1),
('✅ Flood Response Complete — Barangay 126',
 '10 families evacuated from riverside area are now at the Barangay Hall evacuation center. Relief goods distributed.',
 'flood', 1, 2);

-- ── Emergency Contacts ────────────────────────────────────────────────
INSERT INTO emergency_contacts (label, phone, description, sort_order) VALUES
('BFP (Bureau of Fire Protection)', '160', 'National fire emergency hotline', 1),
('Philippine Red Cross', '143', 'Disaster response and blood services', 2),
('NDRRMC Operations', '(02) 911-1406', 'National Disaster Risk Reduction', 3),
('PNP Emergency', '117', 'Philippine National Police', 4),
('Barangay 123 Emergency', '09400000001', 'Team Alpha Rescue direct line', 5),
('Barangay Health Center', '09400000099', 'Medical emergencies and first aid', 6);

-- ── Safety Guides ─────────────────────────────────────────────────────
INSERT INTO safety_guides (hazard_type, title, content, sort_order) VALUES
('fire', 'Before a Fire', 'Install smoke alarms on every level. Test them monthly. Keep fire extinguishers accessible.', 1),
('fire', 'During a Fire', 'Get out immediately. Crawl low if smoke. Feel doors before opening. Call 160 from outside. Never go back inside.', 2),
('flood', 'Before a Flood', 'Know your evacuation routes. Prepare a go-bag (documents, water, food, medicine). Move valuables upstairs.', 1),
('flood', 'During a Flood', 'Never walk through moving water. Avoid flooded roads. Move to higher ground.', 2),
('earthquake', 'During an Earthquake', 'DROP, COVER, HOLD ON. Stay away from windows. If outdoors, move away from buildings.', 1),
('earthquake', 'After an Earthquake', 'Expect aftershocks. Check for gas leaks. Do not use lighters or matches.', 2),
('medical', 'Medical Emergency', 'Call 143 (Red Cross) or 09400000099. Do not move spinal injury victims. Control bleeding with firm pressure.', 1),
('evacuation', 'Evacuation Guide', 'Follow official orders. Bring go-bag. Register at evacuation center. Bring medications.', 1);

-- ── Audit Logs (~50 rows) ─────────────────────────────────────────────
INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address, created_at) VALUES
(1,'auth.login','user',1,'{"source":"web"}','127.0.0.1', NOW() - INTERVAL 3 HOUR),
(2,'auth.login','user',2,'{"source":"web"}','127.0.0.1', NOW() - INTERVAL 4 HOUR),
(12,'auth.login_mobile','user',12,'{"source":"pwa"}','127.0.0.1', NOW() - INTERVAL 2 HOUR),
(12,'incident.created','incident',1,'{"code":"RQN-2026-0001","hazard_type":"fire","triage_level":"critical"}','192.168.1.10', NOW() - INTERVAL 2 HOUR),
(13,'incident.created','incident',2,'{"code":"RQN-2026-0002","hazard_type":"flood","triage_level":"pending_reassessment"}','192.168.1.11', NOW() - INTERVAL 3 HOUR),
(2,'incident.verified','incident',3,'{"code":"RQN-2026-0003"}','127.0.0.1', NOW() - INTERVAL 4 HOUR + INTERVAL 30 MINUTE),
(2,'incident.verified','incident',4,'{"code":"RQN-2026-0004"}','127.0.0.1', NOW() - INTERVAL 5 HOUR),
(1,'incident.assigned','incident',5,'{"code":"RQN-2026-0005","team":"Team Bravo Fire"}','127.0.0.1', NOW() - INTERVAL 80 MINUTE),
(2,'incident.assigned','incident',6,'{"code":"RQN-2026-0006","team":"Team Alpha Rescue"}','127.0.0.1', NOW() - INTERVAL 55 MINUTE),
(4,'incident.acknowledged','incident',6,'{"code":"RQN-2026-0006","source":"web"}','192.168.1.20', NOW() - INTERVAL 40 MINUTE),
(4,'incident.on_scene','incident',7,'{"code":"RQN-2026-0007"}','192.168.1.20', NOW() - INTERVAL 60 MINUTE),
(9,'incident.in_progress','incident',8,'{"code":"RQN-2026-0008","action_code":"first_aid"}','192.168.1.25', NOW() - INTERVAL 80 MINUTE),
(9,'incident.support_requested','incident',8,'{"code":"RQN-2026-0008","support_type":"ambulance"}','192.168.1.25', NOW() - INTERVAL 60 MINUTE),
(7,'incident.resolved','incident',10,'{"code":"RQN-2026-0010","source":"web"}','192.168.1.22', NOW() - INTERVAL 24 HOUR),
(4,'incident.resolved','incident',12,'{"code":"RQN-2026-0012"}','192.168.1.20', NOW() - INTERVAL 70 HOUR),
(1,'incident.rejected','incident',13,'{"code":"RQN-2026-0013","reason":"Not an emergency."}','127.0.0.1', NOW() - INTERVAL 47 HOUR),
(2,'incident.duplicate','incident',14,'{"code":"RQN-2026-0014","original_code":"RQN-2026-0001"}','127.0.0.1', NOW() - INTERVAL 55 MINUTE),
(2,'incident.reassessed','incident',2,'{"code":"RQN-2026-0002","from":"pending_reassessment","to":"high"}','127.0.0.1', NOW() - INTERVAL 2 HOUR + INTERVAL 30 MINUTE),
(1,'team.created','response_team',1,'{"team_name":"Team Alpha Rescue"}','127.0.0.1', NOW() - INTERVAL 72 HOUR),
(1,'team.created','response_team',2,'{"team_name":"Team Bravo Fire"}','127.0.0.1', NOW() - INTERVAL 72 HOUR),
(1,'team.created','response_team',3,'{"team_name":"Medical Unit"}','127.0.0.1', NOW() - INTERVAL 72 HOUR),
(1,'team.created','response_team',4,'{"team_name":"Security & Traffic"}','127.0.0.1', NOW() - INTERVAL 72 HOUR),
(1,'export_analytics','analytics',NULL,'{"filters":{"date_from":"2026-10-01","date_to":"2026-10-14"},"row_count":20}','127.0.0.1', NOW() - INTERVAL 1 HOUR),
(2,'auth.login','user',2,'{"source":"web"}','127.0.0.1', NOW() - INTERVAL 1 HOUR),
(1,'auth.login','user',1,'{"source":"web"}','127.0.0.1', NOW() - INTERVAL 30 MINUTE),
(14,'incident.created','incident',3,'{"code":"RQN-2026-0003","source":"resident_web"}','192.168.1.12', NOW() - INTERVAL 5 HOUR),
(16,'incident.created','incident',5,'{"code":"RQN-2026-0005","source":"resident_web"}','192.168.1.14', NOW() - INTERVAL 100 MINUTE),
(18,'incident.created','incident',6,'{"code":"RQN-2026-0006","source":"resident_mobile"}','192.168.1.16', NOW() - INTERVAL 70 MINUTE),
(19,'incident.created','incident',7,'{"code":"RQN-2026-0007","source":"resident_web"}','192.168.1.17', NOW() - INTERVAL 110 MINUTE),
(20,'incident.created','incident',8,'{"code":"RQN-2026-0008","source":"resident_mobile"}','192.168.1.18', NOW() - INTERVAL 140 MINUTE),
(1,'incident.escalated','incident',8,'{"code":"RQN-2026-0008"}','127.0.0.1', NOW() - INTERVAL 65 MINUTE),
(2,'incident.verified','incident',15,'{"code":"RQN-2026-0015"}','127.0.0.1', NOW() - INTERVAL 3 HOUR),
(2,'incident.assigned','incident',17,'{"code":"RQN-2026-0017","team":"Security & Traffic"}','127.0.0.1', NOW() - INTERVAL 40 MINUTE),
(5,'incident.acknowledged','incident',9,'{"code":"RQN-2026-0009"}','192.168.1.21', NOW() - INTERVAL 90 MINUTE),
(5,'incident.on_scene','incident',9,'{"code":"RQN-2026-0009"}','192.168.1.21', NOW() - INTERVAL 75 MINUTE),
(5,'incident.in_progress','incident',9,'{"code":"RQN-2026-0009","action_code":"evacuating"}','192.168.1.21', NOW() - INTERVAL 55 MINUTE),
(1,'auth.register','user',22,'{"name":"Tessie Magno","source":"pwa"}','192.168.1.30', NOW() - INTERVAL 24 HOUR),
(1,'auth.register','user',23,'{"name":"Lito Macaraeg","source":"pwa"}','192.168.1.31', NOW() - INTERVAL 12 HOUR),
(2,'incident.assigned','incident',9,'{"code":"RQN-2026-0009","team":"Team Alpha Rescue"}','127.0.0.1', NOW() - INTERVAL 100 MINUTE),
(1,'incident.assigned','incident',1,'{"code":"RQN-2026-0001 — pending assignment"}','127.0.0.1', NOW() - INTERVAL 1 HOUR),
(9,'incident.in_progress','incident',8,'{"code":"RQN-2026-0008","action_code":"transporting_patient"}','192.168.1.25', NOW() - INTERVAL 45 MINUTE),
(4,'incident.in_progress','incident',12,'{"code":"RQN-2026-0012","action_code":"evacuating"}','192.168.1.20', NOW() - INTERVAL 72 HOUR),
(7,'incident.in_progress','incident',10,'{"code":"RQN-2026-0010","action_code":"extinguishing"}','192.168.1.22', NOW() - INTERVAL 25 HOUR),
(2,'export_analytics','analytics',NULL,'{"filters":{},"row_count":20}','127.0.0.1', NOW() - INTERVAL 2 HOUR),
(1,'auth.login','user',1,'{"source":"web"}','127.0.0.1', NOW() - INTERVAL 6 HOUR),
(2,'auth.login','user',2,'{"source":"web"}','127.0.0.1', NOW() - INTERVAL 6 HOUR),
(4,'auth.login_mobile','user',4,'{"source":"pwa"}','192.168.1.20', NOW() - INTERVAL 95 MINUTE),
(9,'auth.login_mobile','user',9,'{"source":"pwa"}','192.168.1.25', NOW() - INTERVAL 125 MINUTE),
(12,'incident.created','incident',20,'{"code":"RQN-2026-0020","hazard_type":"medical","triage_level":"high"}','192.168.1.10', NOW() - INTERVAL 15 MINUTE),
(1,'incident.escalated','incident',5,'{"code":"RQN-2026-0005"}','127.0.0.1', NOW() - INTERVAL 75 MINUTE);

-- ── Notifications ─────────────────────────────────────────────────────
INSERT INTO notifications (user_id,title,message,link,is_read,created_at) VALUES
(1,'New Incident Report: RQN-2026-0020','A new medical report was submitted. Triage: high (score 9).','/reports/RQN-2026-0020',0, NOW() - INTERVAL 15 MINUTE),
(2,'New Incident Report: RQN-2026-0020','A new medical report was submitted. Triage: high (score 9).','/reports/RQN-2026-0020',0, NOW() - INTERVAL 15 MINUTE),
(1,'🆘 Support requested: RQN-2026-0008','Lina Torres (Medical Unit) requested ambulance support.','/monitoring',0, NOW() - INTERVAL 60 MINUTE),
(2,'🆘 Support requested: RQN-2026-0008','Lina Torres (Medical Unit) requested ambulance support.','/monitoring',0, NOW() - INTERVAL 60 MINUTE),
(12,'Report RQN-2026-0014 marked duplicate','Your report was marked as a duplicate of RQN-2026-0001.','/reports/RQN-2026-0014',0, NOW() - INTERVAL 55 MINUTE),
(13,'Report RQN-2026-0013 rejected','Your report was rejected. Reason: Not an emergency incident.','/reports/RQN-2026-0013',1, NOW() - INTERVAL 47 HOUR),
(4,'Mission assigned: RQN-2026-0007','Your team has been assigned to incident RQN-2026-0007 (earthquake).','/responder',1, NOW() - INTERVAL 90 MINUTE),
(9,'Mission assigned: RQN-2026-0008','Your team has been assigned to incident RQN-2026-0008 (medical).','/responder',1, NOW() - INTERVAL 120 MINUTE),
(18,'Report RQN-2026-0006 verified','Your report has been verified and is being processed.','/reports/RQN-2026-0006',1, NOW() - INTERVAL 65 MINUTE);

-- ── Update barangay_id from user barangay ─────────────────────────────
UPDATE incidents i JOIN users u ON u.id = i.reported_by JOIN barangays b ON b.name = u.barangay
SET i.barangay_id = b.id WHERE i.barangay_id IS NULL;

-- ── Final summary ─────────────────────────────────────────────────────
SELECT CONCAT('Users: ', COUNT(*)) AS seed_summary FROM users
UNION ALL SELECT CONCAT('Incidents: ', COUNT(*)) FROM incidents
UNION ALL SELECT CONCAT('Active assignments: ', COUNT(*)) FROM assignments WHERE status='active'
UNION ALL SELECT CONCAT('Response updates: ', COUNT(*)) FROM response_updates
UNION ALL SELECT CONCAT('Notifications (unread): ', COUNT(*)) FROM notifications WHERE is_read=0
UNION ALL SELECT CONCAT('Audit log rows: ', COUNT(*)) FROM audit_logs
UNION ALL SELECT CONCAT('Announcements: ', COUNT(*)) FROM announcements
UNION ALL SELECT CONCAT('Teams: ', COUNT(*)) FROM response_teams;
