-- ============================================================
-- WTI website — upgrade script for installs created BEFORE this
-- update (fixes Sinhala text encoding, adds the Announcements /
-- Gallery feature, adds hall photo support).
--
-- Run it like this (the --default-character-set flag matters!):
--
--   mysql --default-character-set=utf8mb4 -u USER -p DBNAME < database/upgrade_v2.sql
--
-- This script is safe to run even if some parts already exist —
-- it uses IF NOT EXISTS / IF EXISTS guards where possible.
-- ============================================================

SET NAMES utf8mb4;

-- 1) Force the database itself to utf8mb4
ALTER DATABASE CURRENT_DB_PLACEHOLDER CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ^ MySQL has no "ALTER DATABASE current" shortcut, so either:
--   a) replace CURRENT_DB_PLACEHOLDER above with your actual database
--      name before running this file, OR
--   b) simply delete that ALTER DATABASE line and run instead:
--        ALTER DATABASE your_db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--      as a separate command before importing the rest of this file.

-- 2) Force every existing table to utf8mb4 (this converts the
--    *table/column* charset going forward; see the note at the
--    bottom about text that is already mangled in the database).
ALTER TABLE admins           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE halls            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE hall_bookings    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE price_categories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE price_items      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE pages            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE enquiries        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3) Add photo support to halls (skipped automatically if the
--    column already exists, on MySQL 8 / MariaDB 10.5+; on older
--    versions, ignore the "duplicate column" error if it appears).
ALTER TABLE halls ADD COLUMN IF NOT EXISTS photo VARCHAR(255) DEFAULT NULL AFTER description;

-- 4) Create the Announcements / Gallery table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_si VARCHAR(200) NOT NULL,
    title_en VARCHAR(200) DEFAULT NULL,
    description_si TEXT,
    description_en TEXT,
    image VARCHAR(255) DEFAULT NULL,
    link_url VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- If Sinhala text ALREADY looks broken in your tables (garbled
-- symbols, not just "??"), the CONVERT TO steps above will not
-- fix it — that means the bytes were mangled at import time and
-- need to be re-imported. Because halls / price_categories /
-- price_items are just reference/config data (not user
-- submissions), the safest fix is to empty and reseed only those
-- three tables, then re-add anything you customised via the
-- admin panel:
--
--   TRUNCATE TABLE price_items;
--   TRUNCATE TABLE price_categories;
--   DELETE FROM halls;
--
-- ...then re-run the INSERT statements from database/schema.sql
-- for halls / price_categories / price_items (or just re-add them
-- from the admin panel — it's often faster).
--
-- Do NOT truncate hall_bookings or enquiries — that is real
-- visitor data, not seed data.
-- ============================================================
