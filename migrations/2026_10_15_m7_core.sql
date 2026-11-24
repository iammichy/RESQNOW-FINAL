-- M7: Maps, Announcements, Audit Logs, Settings, Resident Approval

-- 1. Announcements
CREATE TABLE IF NOT EXISTS announcements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    severity ENUM('info', 'advisory', 'warning', 'critical') DEFAULT 'info',
    active_from DATETIME NULL,
    active_to DATETIME NULL,
    barangay_id INT UNSIGNED NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_announcement_active (active_from, active_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Emergency Contacts
CREATE TABLE IF NOT EXISTS emergency_contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    unit_type VARCHAR(50) NULL, -- e.g. 'fire', 'medical', 'police'
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Barangay Settings
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Users Table Enhancements for Resident Approval & Deactivation
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS approved_by INT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL,
ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL,
ADD COLUMN IF NOT EXISTS home_lat DECIMAL(10, 8) NULL,
ADD COLUMN IF NOT EXISTS home_lng DECIMAL(11, 8) NULL,
ADD COLUMN IF NOT EXISTS suspended_at DATETIME NULL,
ADD COLUMN IF NOT EXISTS user_agent TEXT NULL;

-- 5. Seed initial settings
INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES 
('barangay_name', 'Barangay Camunatan'),
('barangay_city', 'City of Ilagan'),
('barangay_contact', '0997 692 0615'),
('barangay_logo', ''),
('registration_enabled', '1'),
('maintenance_mode', '0');

-- 6. Seed initial emergency contacts
INSERT IGNORE INTO emergency_contacts (name, phone, description, unit_type) VALUES 
('National Emergency', '911', 'General Emergency Line', 'medical'),
('Barangay Dispatch', '09123456789', 'Primary Radio Backup', 'fire'),
('Local Fire Station', '09998887777', 'Bureau of Fire Protection', 'fire'),
('Local Police', '09112223333', 'Philippine National Police', 'police');
