-- =============================================================================
-- M17: Replace placeholder teams/responders with real BDRRMC org structure
-- Barangay Camunatan BDRRMC Organizational Structure
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Remove old placeholder assignments that reference old teams/responders
DELETE FROM assignments WHERE team_id IN (1,2,3,4);

-- ── 2. Remove old team memberships
DELETE FROM team_members WHERE team_id IN (1,2,3,4);

-- ── 3. Remove old placeholder teams
DELETE FROM response_teams WHERE id IN (1,2,3,4);

-- ── 4. Remove old placeholder responders (IDs 4-11)
DELETE FROM users WHERE id IN (4,5,6,7,8,9,10,11) AND role = 'responder';

SET FOREIGN_KEY_CHECKS = 1;

-- ── 5. Insert real BDRRMC responders
--    Password: Responder@1 (bcrypt)
--    Hash generated: $2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2Cz9A02hFG/EHdq.Mi (placeholder)
--    We use a consistent hash for all demo accounts

INSERT INTO users (id, name, email, phone, password, role, status, barangay) VALUES

-- RESPONSE sub-committee teams (operational / deployable)
(30, 'Amado V. Bulauan',       'amado.bulauan@camunatan.gov',     '09171000030', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(31, 'Romeo Baricaua',          'romeo.baricaua@camunatan.gov',    '09171000031', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(32, 'Solita M. Noriega',       'solita.noriega@camunatan.gov',    '09171000032', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(33, 'Sherley M. Aganinta',     'sherley.aganinta@camunatan.gov',  '09171000033', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(34, 'Michelle S. Nuez',        'michelle.nuez@camunatan.gov',     '09171000034', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(35, 'Ricardo C. Zipagan',      'ricardo.zipagan@camunatan.gov',   '09171000035', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(36, 'Melanio A. Tuliao',       'melanio.tuliao@camunatan.gov',    '09171000036', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(37, 'Melanio M. Casasola',     'melanio.casasola@camunatan.gov',  '09171000037', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(38, 'Gloria D. Candelaria',    'gloria.candelaria@camunatan.gov', '09171000038', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(39, 'Joselito Gabiran',        'joselito.gabiran@camunatan.gov',  '09171000039', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(40, 'Placido Del Rosario',     'placido.delrosario@camunatan.gov','09171000040', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(41, 'Fatima Tumamang',         'fatima.tumamang@camunatan.gov',   '09171000041', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(42, 'Simfroso M. Casasola',    'simfroso.casasola@camunatan.gov', '09171000042', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(43, 'Liezel M. Del Rosario',   'liezel.delrosario@camunatan.gov', '09171000043', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(44, 'Catalina A. Padagas',     'catalina.padagas@camunatan.gov',  '09171000044', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(45, 'Glenda M. Rivero',        'glenda.rivero@camunatan.gov',     '09171000045', '$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan'),
(46, 'Amado Bulauan (RDANA)',   'amado.bulauan.rdana@camunatan.gov','09171000046','$2y$12$kjgGu9yoBapiAffafu24ROmSojJ5.ZY/zEgVq7X2nvFf6Izg7GJFy', 'responder', 'approved', 'Camunatan')
ON DUPLICATE KEY UPDATE name=VALUES(name), email=VALUES(email), phone=VALUES(phone), role='responder', status='approved';

-- ── 6. Insert real BDRRMC response teams
INSERT INTO response_teams (id, team_name, unit_type, coverage_area, contact_no, leader_id, status) VALUES
(1,  'Security & Safety Team',           'security', 'Camunatan',  '09171000030', 30, 'available'),
(2,  'SRR Team',                         'rescue',   'Camunatan',  '09171000031', 31, 'available'),
(3,  'Evacuation & Camp Mngt. Team',     'rescue',   'Camunatan',  '09171000032', 32, 'available'),
(4,  'Relief Distribution Team',         'multi',    'Camunatan',  '09171000033', 33, 'available'),
(5,  'Health/First Aid & Psychosocial',  'medical',  'Camunatan',  '09171000034', 34, 'available'),
(6,  'Fire Management Team',             'fire',     'Camunatan',  '09171000035', 35, 'available'),
(7,  'Damage Control Team',              'rescue',   'Camunatan',  '09171000036', 36, 'available'),
(8,  'Protection Team',                  'security', 'Camunatan',  '09171000037', 37, 'available'),
(9,  'Transportation Team',              'multi',    'Camunatan',  '09171000038', 38, 'available'),
(10, 'Communication Team',               'multi',    'Camunatan',  '09171000040', 40, 'available'),
(11, 'Education Team',                   'multi',    'Camunatan',  '09171000041', 41, 'available'),
(12, 'Infra & Shelter Team',             'rescue',   'Camunatan',  '09171000043', 43, 'available'),
(13, 'Livelihood Team',                  'multi',    'Camunatan',  '09171000044', 44, 'available'),
(14, 'PDANA Team',                       'multi',    'Camunatan',  '09171000045', 45, 'available'),
(15, 'RDANA Team',                       'multi',    'Camunatan',  '09171000046', 46, 'available')
ON DUPLICATE KEY UPDATE
  team_name=VALUES(team_name), unit_type=VALUES(unit_type),
  coverage_area=VALUES(coverage_area), contact_no=VALUES(contact_no),
  leader_id=VALUES(leader_id), status='available';

-- ── 7. Team memberships (leader is also a member)
DELETE FROM team_members WHERE team_id BETWEEN 1 AND 15;

INSERT INTO team_members (team_id, user_id) VALUES
-- Security & Safety (leader: Amado Bulauan 30)
(1, 30),
-- SRR (leader: Romeo Baricaua 31)
(2, 31),
-- Evacuation & Camp Mngt (leader: Solita Noriega 32)
(3, 32),
-- Relief Distribution (leader: Sherley Aganinta 33)
(4, 33),
-- Health/First Aid (leader: Michelle Nuez 34)
(5, 34),
-- Fire Management (leader: Ricardo Zipagan 35)
(6, 35),
-- Damage Control (leader: Melanio Tuliao 36)
(7, 36),
-- Protection (leader: Melanio Casasola 37)
(8, 37), (8, 42),  -- Simfroso Casasola also in protection area
-- Transportation (leaders: Gloria Candelaria 38, Joselito Gabiran 39)
(9, 38), (9, 39),
-- Communication (leader: Placido Del Rosario 40)
(10, 40),
-- Education (leader: Fatima Tumamang 41)
(11, 41),
-- Infra & Shelter (leader: Liezel Del Rosario 43)
(12, 43),
-- Livelihood (leader: Catalina Padagas 44)
(13, 44),
-- PDANA (leader: Glenda Rivero 45)
(14, 45),
-- RDANA (leader: Amado Bulauan RDANA 46)
(15, 46);
