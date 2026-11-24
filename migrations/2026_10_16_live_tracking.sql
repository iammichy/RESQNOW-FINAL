-- Live Responder Location Tracking
-- Run against: resqnow

-- 1. Add current location to response_teams
ALTER TABLE response_teams
    ADD COLUMN IF NOT EXISTS current_lat          DECIMAL(10,7) NULL AFTER status,
    ADD COLUMN IF NOT EXISTS current_lng          DECIMAL(10,7) NULL AFTER current_lat,
    ADD COLUMN IF NOT EXISTS location_accuracy    FLOAT         NULL AFTER current_lng,
    ADD COLUMN IF NOT EXISTS location_updated_at  TIMESTAMP     NULL AFTER location_accuracy,
    ADD COLUMN IF NOT EXISTS tracking_active      TINYINT(1) NOT NULL DEFAULT 0 AFTER location_updated_at;

-- 2. Location history (ring-buffer style — keep last N per team)
CREATE TABLE IF NOT EXISTS responder_location_history (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id    INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    lat        DECIMAL(10,7) NOT NULL,
    lng        DECIMAL(10,7) NOT NULL,
    accuracy   FLOAT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_team (team_id),
    KEY idx_user (user_id),
    KEY idx_created (created_at),
    CONSTRAINT fk_rlh_team FOREIGN KEY (team_id) REFERENCES response_teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_rlh_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Live tracking migration complete' AS result;
