# Rais Doultana Book House — Website

A full-stack wholesale book dealer website built with HTML, CSS, Bootstrap, vanilla JavaScript, PHP and MySQL.
<<<<<<< HEAD
# Rais Doultana Book House — Website

A full-stack wholesale book dealer website built with **HTML, CSS, Bootstrap-style responsive layout, vanilla JavaScript, PHP and MySQL**.

Public site: attractive animated homepage → Main Categories (Publisher / Countries / School) → Categories → Sections (Parts) → Books, each with a **WhatsApp order button**.
Admin panel: fully dynamic — the admin manages the 3 main categories, creates categories inside each, creates sections inside each category, and adds books (with a cover photo) inside each section. No limit on how many categories, sections or books.

---

## 1. Requirements

- PHP 8.0 or newer
- MySQL / MariaDB
- Any standard web host (or XAMPP / WAMP / Laragon for local testing)

## 2. Installation

1. **Copy all files** in this folder to your server (e.g. `htdocs/rais-book-house` in XAMPP, or your hosting's `public_html`).

2. **Create the database.**
   - Open phpMyAdmin (or the MySQL CLI).
   - Import `database.sql`. This creates the database `rais_book_house`, all tables, a default admin account, and sensible default settings (using the WhatsApp numbers, phone, email and addresses already on the shop's business card).

3. **Connect the site to your database.**
   - Open `config/db.php`.
   - Update these four lines with your actual database details:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'rais_book_house');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Set folder permissions** (Linux/shared hosting only) so the site can save uploaded images:
   ```
   chmod -R 755 uploads/
   ```

5. **Open the site** in your browser, e.g. `http://localhost/rais-book-house/index.php`.

## 3. Admin Panel Login

Go to `/admin/login.php`

```
Username: admin
Password: admin123
```

**Change this password immediately** after your first login (there isn't a change-password screen yet — the safest option for now is to generate a new hash and update the `admins` table in phpMyAdmin: `UPDATE admins SET password = '<new_bcrypt_hash>' WHERE username = 'admin';`, or ask your developer to add a change-password screen).

## 4. How content is organised

```
Main Category   (Publisher / Countries / School — fixed 3, each with its own image)
 └── Category    (e.g. "Punjab Textbook Board")       ← admin adds as many as needed
      └── Section / Part   (e.g. "Class 9-10")        ← name fully decided by admin
           └── Book   (cover photo + WhatsApp button) ← as many as needed
```

From the admin panel sidebar:

1. **Main Categories** — the 3 fixed top-level types (Publisher, Countries, School) shown as cards on the homepage. Upload/update the image, description and display order for each — you normally don't need to add more than these 3.
2. **Categories** — pick a Main Category, then add/edit/delete sub-categories inside it, each with an optional cover image, description, and display order.
3. **Sections / Parts** — choose a parent category, then add as many named sections inside it as you like.
4. **Books** — choose a section, upload a cover photo, add title/publisher/price, and optionally mark it "Wholesale Special" (shows a red badge). A WhatsApp order button is generated automatically using the book's title.
5. **Site Settings** — update the site name, tagline, logo, both WhatsApp numbers, phone, email, both shop addresses, and the scrolling list of boards/publishers shown on the homepage.

## 5. WhatsApp ordering

Every book's "Order on WhatsApp" button opens a chat to the **Primary WhatsApp number** set in Site Settings, with a pre-filled message naming the exact book, publisher, category and section — the customer just taps Send.

## 6. Logo

Upload your logo any time from **Admin → Site Settings → Logo**. Until you upload one, the header shows a placeholder badge with your initials.

## 7. Folder structure

```
/config/db.php            → database connection + shared helper functions
/includes/header.php       → shared public site header/nav
/includes/footer.php       → shared public site footer
/assets/css/style.css      → all styling (single stylesheet)
/assets/js/main.js         → mobile menu, marquee, scroll animations
/index.php                 → homepage (shows the 3 Main Category cards)
/main-category.php         → lists categories inside a Main Category
/category.php              → lists sections inside a category
/part.php                  → lists books inside a section (WhatsApp buttons)
/admin/                    → admin panel (login required)
/uploads/                  → uploaded main-category/category/book images + logo
/database.sql              → run this once in phpMyAdmin (see section 9 below if upgrading an existing site)
```

## 8. Notes for whoever hosts this

- The design (colors, fonts, layout) matches the shop's own signboard styling (royal blue, red, gold) so it feels recognisably "theirs," not a generic template.
- Everything customer-facing is in **English**, as requested.
- All admin forms use prepared SQL statements and escaped output, so it's safe against common SQL-injection/XSS issues out of the box.
- If you outgrow shared hosting or want a staging/production split, you only need to update the four values in `config/db.php` per environment.

## 9. Upgrading an existing live site (already has categories/books)

If this replaces a version of the site that only had one "Categories" level, **do not re-import `database.sql`** — it will overwrite your data. Instead, in phpMyAdmin's SQL tab run only:

```sql
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
(1, 'Publisher', 'publisher', 'Browse books by publisher.', 1),
(2, 'Countries', 'countries', 'Browse books by country / curriculum origin.', 2),
(3, 'School', 'school', 'Browse books by school / board.', 3);

ALTER TABLE categories ADD COLUMN main_category_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE categories ADD CONSTRAINT fk_cat_main FOREIGN KEY (main_category_id) REFERENCES main_categories(id) ON DELETE CASCADE;
```

Then upload the new files (overwriting the old ones), and:

1. Go to **Admin → Main Categories** and upload the image the client designed for Publisher, Countries and School.
2. Go to **Admin → Categories** and open each existing category once — it will currently show "Publisher" selected by default — and pick the correct Main Category for it.
=======
# Raees-book-house
>>>>>>> 38580a15f01bd9b7adfb946df3344b0fc7c70b6d
