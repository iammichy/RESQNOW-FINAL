-- M15: Resident Masterlist
-- Official barangay population data for Camunatan
-- 1,411 residents across 458 families in 3 puroks

CREATE TABLE IF NOT EXISTS resident_masterlist (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purok           TINYINT UNSIGNED NOT NULL COMMENT '1, 2, or 3',
    resident_no     SMALLINT UNSIGNED NOT NULL,
    family_no       SMALLINT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_purok        (purok),
    INDEX idx_family       (purok, family_no),
    INDEX idx_name         (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Official resident masterlist of Barangay Camunatan';
