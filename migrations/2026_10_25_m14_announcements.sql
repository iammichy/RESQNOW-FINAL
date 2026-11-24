-- M14: Announcements table upgrade
-- Adds severity, audience, event_date columns; fixes garbled emoji titles

ALTER TABLE announcements
  ADD COLUMN severity ENUM('information','advisory','warning','critical') NOT NULL DEFAULT 'information' AFTER hazard_type,
  ADD COLUMN audience ENUM('all','residents','responders') NOT NULL DEFAULT 'all' AFTER severity,
  ADD COLUMN event_date DATETIME NULL AFTER audience;

-- Fix garbled emoji titles from seed.sql (UTF-8 encoding issue)
UPDATE announcements SET
  title = 'Elevated Flood Alert — Barangays 124 & 126',
  severity = 'warning',
  audience = 'residents'
WHERE id = 1;

UPDATE announcements SET
  title = 'Fire Safety Campaign This Week',
  severity = 'advisory',
  audience = 'residents'
WHERE id = 2;

UPDATE announcements SET
  title = 'Mass Casualty Drill — October 20',
  severity = 'information',
  audience = 'all',
  event_date = '2026-10-20 09:00:00'
WHERE id = 3;

UPDATE announcements SET
  title = 'Flood Response Complete — Barangay 126',
  severity = 'information',
  audience = 'all'
WHERE id = 4;
