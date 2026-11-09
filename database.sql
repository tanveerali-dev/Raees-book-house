-- ============================================================
-- Rais Doultana Book House - Database Schema
-- Import this file into phpMyAdmin (inside your existing hosting
-- database — e.g. u863796476_raisbookhouse) before using the site.
-- Do NOT add "CREATE DATABASE" / "USE" lines here: on shared
-- hosting your database is already created via hPanel with a
-- fixed name, and the DB user usually isn't allowed to create a
-- new one — phpMyAdmin's import already runs inside whichever
-- database you have open on the left sidebar.
-- ============================================================

-- ------------------------------------------------------------
-- Admin users (people who can log into /admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin login -> username: admin | password: admin123
-- (change this password immediately after first login from the admin panel)
INSERT INTO admins (username, password) VALUES
('admin', '$2b$10$ziVWgG7aSgPM4/HTjdrVdORPgJ1s9UyvkbXhxXs6NjKWz0L/Copp.')
ON DUPLICATE KEY UPDATE username = username;

-- ------------------------------------------------------------
-- Main Categories (top level, fixed 3 types: Publisher,
-- Countries, School). Each has its own image. Admin can still
-- edit their name/image/description from the admin panel, but
-- normally these 3 stay as-is — everything else is organised
-- underneath them.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS main_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    description VARCHAR(500) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO main_categories (id, name, slug, description, sort_order) VALUES
(1, 'Publisher', 'publisher', 'Browse books by publisher, e.g. Oxford, Cambridge, National Book Foundation.', 1),
(2, 'Countries', 'countries', 'Browse books by country / curriculum origin.', 2),
(3, 'School', 'school', 'Browse books by school / board, e.g. Punjab Board, Federal Board.', 3)
ON DUPLICATE KEY UPDATE name = name;

-- ------------------------------------------------------------
-- Categories (sub-categories, e.g. under "Publisher": "Oxford
-- University Press", "Cambridge"; under "School": "Punjab
-- Textbook Board", "Federal Board"). Admin adds as many as
-- needed inside each Main Category.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    main_category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    description VARCHAR(500) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (main_category_id) REFERENCES main_categories(id) ON DELETE CASCADE
);

-- If you already imported an earlier version of this file and have
-- existing categories/parts/books you don't want to lose, DO NOT
-- re-run this whole file. Instead run ONLY these two lines once:
-- ALTER TABLE categories ADD COLUMN main_category_id INT NOT NULL DEFAULT 1 AFTER id;
-- ALTER TABLE categories ADD CONSTRAINT fk_cat_main FOREIGN KEY (main_category_id) REFERENCES main_categories(id) ON DELETE CASCADE;
-- Then, in the admin panel -> Categories, edit each existing category
-- once and pick the correct Main Category (Publisher/Countries/School)
-- for it — they will all default to "Publisher" until you do this.

-- ------------------------------------------------------------
-- Parts (sub-sections inside a category, e.g. inside
-- "Punjab Text Book Board" -> "Class 1-5", "Class 6-8", "Class 9-10")
-- Names are fully decided by the admin.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Books (the actual product images shown to customers,
-- each with a WhatsApp order button)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    part_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    publisher VARCHAR(150) DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    is_wholesale_special TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE CASCADE
);

-- If you already imported an earlier version of this file and have existing
-- data you don't want to lose, run this ONE line instead of re-importing:
-- ALTER TABLE books ADD COLUMN rating TINYINT NOT NULL DEFAULT 5 AFTER image;


-- ------------------------------------------------------------
-- Site settings (single row) - WhatsApp numbers, phone, email,
-- address, logo path etc. Editable from the admin panel.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY DEFAULT 1,
    site_name VARCHAR(150) DEFAULT 'Rais Doultana Book House',
    tagline VARCHAR(255) DEFAULT 'Wholesale Dealers, Importers & Exporters of Books',
    logo VARCHAR(255) DEFAULT NULL,
    whatsapp_1 VARCHAR(20) DEFAULT '923002873276',
    whatsapp_2 VARCHAR(20) DEFAULT '923019469494',
    phone VARCHAR(20) DEFAULT '042-37229494',
    email VARCHAR(150) DEFAULT 'doultanaraees@gmail.com',
    address_main VARCHAR(255) DEFAULT 'Shop No. 3, Khalid Market, 40 Urdu Bazar, Lahore',
    address_branch VARCHAR(255) DEFAULT 'Branch: Khanewal Doultana Plaza',
    boards_publishers TEXT DEFAULT NULL
);

INSERT INTO settings (id, boards_publishers) VALUES (1,
'Punjab Textbook Board,Federal Textbook Board,Oxford University Press,Cambridge University Press,National Book Foundation,Unique Notes,Topics Academy')
ON DUPLICATE KEY UPDATE id = id;
