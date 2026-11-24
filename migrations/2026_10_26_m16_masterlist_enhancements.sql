-- M16: Resident masterlist enhancements
-- Adds status, notes, audit columns to resident_masterlist
-- Adds masterlist_match_id to users for linking accounts to masterlist
-- Adds residency_verification table

-- 1. Enhance resident_masterlist
ALTER TABLE resident_masterlist
  ADD COLUMN status       ENUM('active','deceased','transferred','archived') NOT NULL DEFAULT 'active' AFTER name,
  ADD COLUMN notes        VARCHAR(255) NULL AFTER status,
  ADD COLUMN added_by     INT UNSIGNED NULL AFTER notes,
  ADD COLUMN updated_by   INT UNSIGNED NULL AFTER added_by,
  ADD COLUMN updated_at   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER updated_by,
  ADD INDEX idx_status    (status);

-- 2. Add masterlist link to users (which masterlist record matches this account)
ALTER TABLE users
  ADD COLUMN masterlist_id    INT UNSIGNED NULL AFTER address,
  ADD COLUMN match_status     ENUM('matched','not_found','unverified','flagged_duplicate') NULL AFTER masterlist_id,
  ADD COLUMN match_confidence TINYINT UNSIGNED NULL AFTER match_status,
  ADD INDEX idx_masterlist_id (masterlist_id);

-- 3. Residency verification requests table
CREATE TABLE IF NOT EXISTS residency_verifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    masterlist_id   INT UNSIGNED NULL COMMENT 'Linked masterlist record if found',
    status          ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    match_type      ENUM('exact','fuzzy','multiple','not_found') NOT NULL DEFAULT 'not_found',
    match_details   JSON NULL COMMENT 'JSON array of possible matches',
    verified_by     INT UNSIGNED NULL,
    verified_at     DATETIME NULL,
    notes           TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user   (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
