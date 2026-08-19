-- ============================================================
-- Wayamba Training Institute - Wariyapola (WTI)
-- Database schema + seed data
--
-- IMPORTANT — Sinhala text will show as "????" or mangled symbols
-- if this file is imported with the wrong client charset. Always
-- import it like this (note --default-character-set=utf8mb4):
--
--   mysql --default-character-set=utf8mb4 -u USER -p DBNAME < schema.sql
--
-- If your database already existed before running this file (this
-- is common on OpenShift, where the MySQL template pre-creates an
-- empty database for you), the CREATE DATABASE line below is
-- skipped and the pre-existing database may still be using
-- latin1/utf8 instead of utf8mb4. Run this first in that case:
--
--   ALTER DATABASE your_db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--
-- See database/upgrade_v2.sql if you are fixing an existing install.
-- ============================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS wti_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wti_db;
ALTER DATABASE wti_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Admins (CMS users)
-- ---------------------------------------------------------
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(120) DEFAULT NULL,
    role ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username = admin / password = ChangeMe123!
-- (hash generated with PHP password_hash('ChangeMe123!', PASSWORD_DEFAULT))
INSERT INTO admins (username, password_hash, full_name, role) VALUES
('admin', '$2y$10$BKMP555kgX2diCKx7eYi.ew3d44XZ.siD54qNn229RMFMznuhPpnG', 'Site Administrator', 'super_admin');

-- ---------------------------------------------------------
-- Halls (lecture halls / auditoriums that can be booked)
-- ---------------------------------------------------------
CREATE TABLE halls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_si VARCHAR(150) NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    capacity_min INT DEFAULT NULL,
    capacity_max INT NOT NULL,
    price_ac DECIMAL(10,2) DEFAULT NULL,      -- price per day with A/C
    price_non_ac DECIMAL(10,2) DEFAULT NULL,  -- price per day without A/C
    has_ac TINYINT(1) DEFAULT 1,
    description TEXT,
    photo VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO halls (name_si, name_en, capacity_min, capacity_max, price_ac, price_non_ac, has_ac, sort_order) VALUES
('ප්‍රධාන ශාලාව', 'Main Hall', NULL, 150, 10000.00, 8000.00, 1, 1),
('ශාලා අංක 02', 'Hall No. 02', 35, 40, 6000.00, 5000.00, 1, 2),
('ශාලා අංක 03', 'Hall No. 03', 70, 80, 8000.00, 6000.00, 1, 3),
('ශාලා අංක 04', 'Hall No. 04', NULL, 50, 5000.00, 5000.00, 1, 4),
('ශාලා අංක 05', 'Hall No. 05', 80, 100, NULL, 8000.00, 0, 5),
('ශාලා අංක 06', 'Hall No. 06', 25, 35, NULL, 6000.00, 0, 6),
('ශාලා අංක 07', 'Hall No. 07', NULL, 80, 9000.00, 7000.00, 1, 7),
('ශාලා අංක 08', 'Hall No. 08', 40, 50, 6000.00, 5000.00, 1, 8),
('පරිගණක දේශනාගාරය', 'Computer Lecture Hall', NULL, 15, 8000.00, NULL, 1, 9);

-- ---------------------------------------------------------
-- Hall bookings (online booking requests submitted by public)
-- ---------------------------------------------------------
CREATE TABLE hall_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hall_id INT NOT NULL,
    requester_name VARCHAR(150) NOT NULL,
    organization VARCHAR(200) DEFAULT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    nic VARCHAR(20) DEFAULT NULL,
    event_title VARCHAR(200) DEFAULT NULL,
    event_date DATE NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    participants INT DEFAULT NULL,
    ac_required TINYINT(1) DEFAULT 0,
    notes TEXT,
    status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    admin_remark VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Price categories (CMS-managed: hall extras, equipment,
-- accommodation, meals, beverages, etc.)
-- ---------------------------------------------------------
CREATE TABLE price_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_si VARCHAR(150) NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    icon VARCHAR(60) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO price_categories (name_si, name_en, slug, icon, sort_order) VALUES
('ශාලා අමතර ගාස්තු', 'Hall Extra Charges', 'hall-extras', 'building', 1),
('පුහුණු උපකරණ ගාස්තු', 'Training Equipment Charges', 'equipment', 'projector', 2),
('සංචාරක නිවාස', 'Guest Accommodation', 'guest-rooms', 'bed', 3),
('නේවාසිකාගාර', 'Hostel / Dormitory', 'hostel', 'building-2', 4),
('පාන වර්ග', 'Beverages', 'beverages', 'cup', 5),
('කෙටි කෑම වර්ග', 'Short Eats', 'short-eats', 'utensils', 6),
('අතුරුපස වර්ග', 'Desserts', 'desserts', 'ice-cream', 7),
('නැවුම් පලතුරු බීම', 'Fresh Fruit Juices', 'fresh-juices', 'glass-water', 8);

-- ---------------------------------------------------------
-- Price items (individual line items within a category)
-- ---------------------------------------------------------
CREATE TABLE price_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    code VARCHAR(20) DEFAULT NULL,
    name_si VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) DEFAULT NULL,
    unit VARCHAR(100) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES price_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hall extra charges (from institute price list)
INSERT INTO price_items (category_id, name_si, name_en, unit, price, sort_order) VALUES
(1, 'ශාලා පහසුකම් සවස 5.00 පසු පැයකට', 'Hall use after 5.00pm', 'per hour', 700.00, 1),
(1, 'පරිගණක දේශනාගාර සහායක', 'Computer lab assistant', 'per session', 500.00, 2),
(1, 'උපකරණ නඩත්තු ගාස්තු', 'Equipment maintenance fee', 'per person / day', 125.00, 3),
(1, 'අන්තර්ජාල ගාස්තු (දේශනාගාරය සඳහා)', 'Internet fee (lecture hall)', 'per hall', 200.00, 4),
(1, 'ලැප්ටොප් භාවිතය - දිනකට', 'Laptop use - per day', 'per day', 1000.00, 5),
(1, 'ආයතන හා පරිපාලන ගාස්තු', 'Institutional & admin charge', 'per day', 1150.00, 6);

-- Training equipment
INSERT INTO price_items (category_id, name_si, name_en, unit, price, sort_order) VALUES
(2, 'මල්ටිමීඩියා / OHP (පැය 4ට වැඩි)', 'Multimedia / OHP (over 4 hrs)', 'per day', 1750.00, 1),
(2, 'මල්ටිමීඩියා / OHP (පැය 4ට අඩු)', 'Multimedia / OHP (under 4 hrs)', 'per day', 1000.00, 2),
(2, 'රූපවාහිනී / DVD / VCD (පැය 4ට වැඩි)', 'TV / DVD / VCD (over 4 hrs)', 'per day', 500.00, 3),
(2, 'රූපවාහිනී / DVD / VCD (පැය 4ට අඩු)', 'TV / DVD / VCD (under 4 hrs)', 'per day', 250.00, 4),
(2, 'ඡායා පිටපත් (තනි පිටුව)', 'Photocopy (single side)', 'per page', 8.00, 5),
(2, 'ඡායා පිටපත් (දෙපැත්ත)', 'Photocopy (double side)', 'per page', 15.00, 6);

-- Guest accommodation
INSERT INTO price_items (category_id, name_si, name_en, unit, price, sort_order) VALUES
(3, 'ඇසල නිල නිවාස (වායු සමනය රහිත)', 'Asala guest room (non A/C)', 'per night', 3000.00, 1),
(3, 'ඇසල නිල නිවාස (වායු සමනය සහිත)', 'Asala guest room (A/C)', 'per night', 5000.00, 2),
(3, 'නිකණි නිල නිවාස', 'Nikani guest room', 'per night', 3000.00, 3),
(3, 'බිනර නිල නිවාස (වායු සමනය රහිත)', 'Binara guest room (non A/C)', 'per night', 3000.00, 4),
(3, 'බිනර නිල නිවාස (වායු සමනය සහිත)', 'Binara guest room (A/C)', 'per night', 5000.00, 5);

-- Hostel / dormitory
INSERT INTO price_items (category_id, name_si, name_en, unit, price, sort_order) VALUES
(4, 'ඇදන් 6ක් සහිත කාමර', 'Room with 6 beds', 'per person / day', 375.00, 1),
(4, 'ඇදන් 2ක් සහිත කාමර', 'Room with 2 beds', 'per person / day', 375.00, 2);

-- Beverages (sample)
INSERT INTO price_items (category_id, code, name_si, name_en, unit, price, sort_order) VALUES
(5, 'SD 1', 'ඉඟුරු තේ', 'Ginger tea', NULL, 40.00, 1),
(5, 'SD 3', 'කිරි තේ', 'Milk tea', NULL, 120.00, 2),
(5, 'SD 5', 'කෝපි', 'Coffee', NULL, 90.00, 3),
(5, 'SD 18', 'සරුවත් (300ml)', 'Cordial (300ml)', NULL, 100.00, 4),
(5, 'SD 19', 'ෆලූඩා (300ml)', 'Falooda (300ml)', NULL, 150.00, 5);

-- Short eats (sample)
INSERT INTO price_items (category_id, code, name_si, name_en, unit, price, sort_order) VALUES
(6, 'SN 1', 'ඉඹුල් කිරිබත් (150g)', 'Ash plantain milk rice (150g)', NULL, 75.00, 1),
(6, 'SN 16', 'බිත්තර සමෝස (100g)', 'Egg samosa (100g)', NULL, 90.00, 2),
(6, 'SN 22', 'බටර් කේක් (80g)', 'Butter cake (80g)', NULL, 110.00, 3),
(6, 'SN 44', 'එළවළු රෝල්ස් (100g)', 'Vegetable rolls (100g)', NULL, 80.00, 4);

-- Desserts (sample)
INSERT INTO price_items (category_id, code, name_si, name_en, unit, price, sort_order) VALUES
(7, 'DS 1', 'අයිස් ක්‍රීම් (කප්)', 'Ice cream (cup)', NULL, 100.00, 1),
(7, 'DS 5', 'යෝගට්', 'Yoghurt', NULL, 80.00, 2),
(7, 'DS 10', 'චොක්ලට් මූස්', 'Chocolate mousse', NULL, 180.00, 3);

-- Fresh juices (sample)
INSERT INTO price_items (category_id, code, name_si, name_en, unit, price, sort_order) VALUES
(8, 'FFJ 1', 'දෙහි / ලෙමන් (300ml)', 'Lime / lemon (300ml)', NULL, 90.00, 1),
(8, 'FFJ 4', 'කොමඩු (300ml)', 'Watermelon (300ml)', NULL, 120.00, 2),
(8, 'FFJ 10', 'මිශ්‍ර පලතුරු (300ml)', 'Mixed fruit (300ml)', NULL, 150.00, 3);

-- ---------------------------------------------------------
-- CMS-editable static pages (About, Contact, Notices, etc.)
-- ---------------------------------------------------------
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title_si VARCHAR(200) NOT NULL,
    title_en VARCHAR(200) NOT NULL,
    content_si TEXT,
    content_en TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pages (slug, title_si, title_en, content_si, content_en) VALUES
('about', 'ආයතනය පිළිබඳව', 'About the Institute',
 'වයඹ පුහුණු ආයතනය - වාරියපොල රාජ්‍ය හා පෞද්ගලික ආයතන සඳහා පුහුණු වැඩසටහන්, ශාලා, නේවාසික පහසුකම් හා ආහාර පහසුකම් සපයයි.',
 'Wayamba Training Institute - Wariyapola provides training programmes, lecture halls, accommodation and catering facilities for government and private sector organisations.'),
('contact', 'අප අමතන්න', 'Contact Us',
 'දුරකථන: 037 2267370 | ෆැක්ස්: 037 2057547 | විද්‍යුත් තැපෑල: wtiwariyapola@gmail.com',
 'Telephone: 037 2267370 | Fax: 037 2057547 | Email: wtiwariyapola@gmail.com');

-- ---------------------------------------------------------
-- Announcements / photo notices (shown on the homepage and the
-- public "Notices & Gallery" page — fully managed from the CMS,
-- including the image upload).
-- ---------------------------------------------------------
CREATE TABLE announcements (
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

INSERT INTO announcements (title_si, title_en, description_si, description_en, sort_order) VALUES
('ආයතනයට සාදරයෙන් පිළිගනිමු', 'Welcome to WTI Wariyapola',
 'වයඹ පුහුණු ආයතනය - වාරියපොල ශාලා, නේවාසික සහ ආහාර පහසුකම් සමඟ ඔබගේ පුහුණු වැඩසටහන් සඳහා සූදානම්.',
 'Add real photos of the institute here from the admin panel — Announcements → Add.', 1);

-- ---------------------------------------------------------
-- Simple contact / enquiry messages
-- ---------------------------------------------------------
CREATE TABLE enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
