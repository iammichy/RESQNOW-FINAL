-- M6: PWA — safety guides, emergency contacts, user profile fields
-- Run against: resqnow

-- 1. Safety guides
CREATE TABLE IF NOT EXISTS safety_guides (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hazard_type VARCHAR(80) NOT NULL,
    title       VARCHAR(200) NOT NULL,
    content     TEXT NOT NULL,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Emergency contacts
CREATE TABLE IF NOT EXISTS emergency_contacts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label       VARCHAR(100) NOT NULL,
    phone       VARCHAR(30) NOT NULL,
    description VARCHAR(200) NULL,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Announcements (resident home feed)
CREATE TABLE IF NOT EXISTS announcements (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    body        TEXT NULL,
    hazard_type VARCHAR(80) NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_by  INT UNSIGNED NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ann_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Add push_token to users for future FCM (nullable)
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS push_token VARCHAR(255) NULL AFTER api_token,
    ADD COLUMN IF NOT EXISTS notif_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER push_token;

-- 5. Seed safety guides
INSERT IGNORE INTO safety_guides (hazard_type, title, content, sort_order) VALUES
('fire', 'Before a Fire', 'Install smoke alarms on every level. Test them monthly. Plan two escape routes from every room. Keep fire extinguishers accessible and know how to use them.', 1),
('fire', 'During a Fire', 'Get out immediately. Crawl low if there is smoke. Feel doors before opening — if hot, use another route. Close doors to slow fire spread. Call 911 from outside. Never go back inside.', 2),
('fire', 'After a Fire', 'Do not re-enter until authorities declare it safe. Document damage with photos. Contact your barangay hall for relief assistance.', 3),
('flood', 'Before a Flood', 'Know your evacuation routes. Prepare a go-bag (documents, water, food, medicine). Move valuables to upper floors. Charge all devices.', 1),
('flood', 'During a Flood', 'Never walk through flowing water — 15 cm can knock you down. Do not drive through flooded roads. Move to higher ground immediately. Avoid downed power lines.', 2),
('flood', 'After a Flood', 'Wait for official all-clear before returning. Wear rubber boots and gloves. Boil water before drinking. Watch for snakes and pests.', 3),
('earthquake', 'During an Earthquake', 'DROP, COVER (under a sturdy desk), and HOLD ON. Stay away from windows and shelves. If outdoors, move away from buildings and power lines. If driving, pull over away from structures.', 1),
('earthquake', 'After an Earthquake', 'Expect aftershocks. Check for injuries. Check for gas leaks — do not use lighters. Inspect your home for structural damage before re-entering.', 2),
('medical', 'Medical Emergency Response', 'Call emergency services immediately. Do not move a person with possible spinal injury. Control bleeding with firm pressure. Perform CPR only if trained. Stay with the person until help arrives.', 1),
('accident', 'Road Accident Response', 'Do not move injured persons unless there is immediate danger. Turn on hazard lights. Call for help. Keep the area clear of traffic. Do not leave the scene until police arrive.', 1),
('evacuation', 'Evacuation Guide', 'Follow official evacuation orders immediately. Bring your go-bag. Bring essential medications. Lock your home. Go to the designated evacuation center. Register with barangay officials on arrival.', 1);

-- 6. Seed emergency contacts
INSERT IGNORE INTO emergency_contacts (label, phone, description, sort_order) VALUES
('BFP (Bureau of Fire Protection)', '160',           'National fire emergency hotline', 1),
('Philippine Red Cross',            '143',           'Disaster response and blood services', 2),
('NDRRMC Operations',               '(02) 911-1406', 'National Disaster Risk Reduction and Management Council', 3),
('PNP Emergency',                   '117',           'Philippine National Police emergency', 4),
('Barangay Emergency',              '09200000099',   'Local barangay emergency response team', 5),
('DSWD',                            '(02) 931-8101', 'Department of Social Welfare and Development', 6);

-- 7. Seed announcements
INSERT IGNORE INTO announcements (title, body, hazard_type, is_active) VALUES
('⚠ Elevated Flood Risk — Barangay 2', 'Continuous rainfall forecast for the next 48 hours. Residents in low-lying areas of Barangay 2 are advised to prepare evacuation bags and monitor water levels.', 'flood', 1),
('🔥 Fire Safety Reminder', 'With the dry season approaching, please ensure LPG tanks are properly stored and electrical wiring is checked. Keep fire extinguishers accessible.', 'fire', 1);

SELECT CONCAT('safety_guides: ', COUNT(*)) AS m6_seed FROM safety_guides
UNION ALL SELECT CONCAT('emergency_contacts: ', COUNT(*)) FROM emergency_contacts
UNION ALL SELECT CONCAT('announcements: ', COUNT(*)) FROM announcements;
