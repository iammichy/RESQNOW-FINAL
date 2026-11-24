-- M16b: Add masterlist link to users and residency_verifications table

ALTER TABLE users
  ADD COLUMN masterlist_id    INT UNSIGNED NULL AFTER address,
  ADD COLUMN match_status     ENUM('matched','not_found','unverified','flagged_duplicate') NULL AFTER masterlist_id,
  ADD COLUMN match_confidence TINYINT UNSIGNED NULL AFTER match_status,
  ADD INDEX idx_masterlist_id (masterlist_id);

CREATE TABLE IF NOT EXISTS residency_verifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    masterlist_id   INT UNSIGNED NULL,
    status          ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    match_type      ENUM('exact','fuzzy','multiple','not_found') NOT NULL DEFAULT 'not_found',
    match_details   TEXT NULL,
    verified_by     INT UNSIGNED NULL,
    verified_at     DATETIME NULL,
    notes           TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user   (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
