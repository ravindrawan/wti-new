# Wayamba Training Institute — Wariyapola | Website + CMS + Hall Booking

A responsive PHP/MySQL website for WTI Wariyapola with:

- **Public site** — home page, lecture hall listing, full facilities/rate list (rendered from the database, editable in the CMS), online hall booking form, booking status tracker, contact form.
- **Admin CMS** (`/admin`) — login-protected backend to manage halls, rate categories & items (hall extras, equipment, accommodation, hostel, beverages, short eats, desserts, juices…), review/approve/reject hall booking requests, manage site page text, view contact enquiries, and manage admin users.

## 1. Requirements

- PHP 8.0+ with PDO MySQL extension
- MySQL 5.7+ / MariaDB 10.3+
- Any standard web server (Apache/Nginx) or `php -S` for local testing

## 2. Setup

1. **Create the database and import the schema:**
   ```bash
   mysql -u root -p -e "CREATE DATABASE wti_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p wti_db < database/schema.sql
   ```
   This creates all tables and seeds:
   - the 9 lecture halls with their real rates,
   - 8 rate categories (hall extras, equipment, guest rooms, hostel, beverages, short eats, desserts, fresh juices) with sample items,
   - a default admin account,
   - editable "About" and "Contact" page text.

2. **Configure the database connection.** Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'wti_db');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('SITE_URL', '/'); // e.g. '/wti-website/' if not installed at the domain root
   ```

3. **Set folder permissions** so PHP can write to `uploads/` if you later add photo uploads:
   ```bash
   chmod 755 uploads
   ```

4. **Run it locally** for a quick test:
   ```bash
   php -S localhost:8000
   ```
   Then open `http://localhost:8000/`.

   For production, point your Apache/Nginx document root at this folder.

## 3. Deploying on Red Hat OpenShift

Two things trip people up on OpenShift specifically — both are already handled by this codebase, but need one config step from you:

**a) Sinhala text showing as `????` or garbled symbols**
This is almost always a MySQL *client* charset issue at import time, not a PHP issue (the PHP side already forces `utf8mb4` on every connection — see `includes/config.php`). Fix:

1. Re-import using the explicit charset flag:
   ```bash
   mysql --default-character-set=utf8mb4 -u USER -p DBNAME < database/schema.sql
   ```
2. If you already imported it once without that flag (common on a fresh OpenShift MySQL template, which pre-creates the database with its own default charset), run `database/upgrade_v2.sql` instead — it forces every table to `utf8mb4` and explains how to reseed the reference tables (halls / rates) if the Sinhala text is already corrupted beyond repair.
3. Double-check your OpenShift MySQL template wasn't created with `latin1` — some older templates default to it. You can check with:
   ```sql
   SHOW VARIABLES LIKE 'character_set_database';
   ```

**b) Connecting to the database**
`includes/config.php` now auto-detects common OpenShift MySQL environment variable names (`MYSQL_SERVICE_HOST`, `MYSQL_SERVICE_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`) so you usually don't need to hard-code credentials at all — they come from the Secret/service your MySQL template created. If your setup uses different variable names, either rename them or edit the `env_or([...])` lists in `includes/config.php`.

**c) Uploaded photos (hall photos, announcements) need persistent storage**
By default, an OpenShift pod's filesystem is ephemeral — anything written to `uploads/` disappears on the next redeploy or pod restart/scale event. Mount a Persistent Volume Claim at the `uploads/` folder so photos survive:

```yaml
volumeMounts:
  - name: wti-uploads
    mountPath: /opt/app-root/src/uploads   # adjust to your image's app path
volumes:
  - name: wti-uploads
    persistentVolumeClaim:
      claimName: wti-uploads-pvc
```

**d) File permissions (arbitrary UID)**
OpenShift runs containers as a random UID with group `root` (GID 0) unless you've relaxed the SCC. Make sure the `uploads/` folder is group-writable *before* building the image, or as a post-deploy step:
```bash
chmod -R g=u uploads
```

## 4. Default admin login

- URL: `/admin/login.php`
- Username: `admin`
- Password: `ChangeMe123!`

**Change this password immediately** after first login: go to *Admin Users* (visible to super admins) and create a new super admin account with your own credentials, then delete or repurpose the default one — or add a "change password" flow if you extend the project.

## 5. How the CMS maps to the institute's printed rate sheet

The original rate sheet (halls, equipment, accommodation, hostel, meals, beverages, short eats, desserts, extra-order dishes) has hundreds of line items across many meal "cycles" (BM/LM/DM 1–11). Rather than hard-coding every one, the site stores them as:

- **`price_categories`** — one row per section of the rate sheet (e.g. "Beverages", "Short Eats").
- **`price_items`** — one row per line item, with an optional code (e.g. `SD 1`), Sinhala + English names, unit, and price.

A representative starter set has been seeded for each category. Add the remaining items (all the BM/LM/DM meal cycles, extra-order seafood dishes, etc.) from the admin panel under **Facilities → Rate Categories / Rate Items** — no code changes needed. You can also add brand-new categories (e.g. "Meal Cycle BM-3") the same way.

## 6. Managing photos (hall photos & announcements/gallery)

Two places now support real photo uploads directly from the admin panel — no code editing needed:

- **Admin → Lecture Halls → Edit** — upload a photo for each hall. It replaces the placeholder square shown on the homepage and halls page.
- **Admin → Announcements & Gallery** — add photo notices (event flyers, institute photos, updates). These appear on the homepage ("Notices & Gallery" section) and on the public `/announcements.php` page. Each entry needs a photo plus a Sinhala title; English title/description and a link are optional.

Accepted formats: JPG, PNG, WEBP, GIF, up to 4MB each. Files are stored under `uploads/halls/` and `uploads/announcements/` with randomised filenames (the originals are never trusted/executed — see `uploads/.htaccess`). **Remember the OpenShift persistent-volume note in section 3(c) above**, or uploaded photos will disappear when the pod restarts.

## 7. How hall booking works

1. A visitor picks a hall + date on `/booking.php` and submits the form (no login required).
2. The request is stored in `hall_bookings` with status `pending`, and the visitor gets a reference number (e.g. `WTI-00012`) plus a link to `/track_booking.php`.
3. Admin staff review the request in **Admin → Hall Bookings**, and set the status to `approved`, `rejected`, or `cancelled`, optionally leaving a remark.
4. The visitor (or anyone with the reference number, optionally + the phone number used) can check status anytime on `/track_booking.php`.

> Note: this is a *request/approval* workflow, not a live seat-lock calendar — two people could request the same hall/date and the admin resolves conflicts manually when reviewing. If you need hard double-booking prevention, add a uniqueness check on `(hall_id, event_date)` for `approved` bookings in `booking.php` before insert.

## 8. Security notes

- Passwords are hashed with PHP's `password_hash()` (bcrypt).
- All forms use CSRF tokens (`includes/functions.php`).
- All database queries use PDO prepared statements.
- Change the default admin password and consider adding HTTPS + rate limiting on `/admin/login.php` for production use.

## 9. Folder structure

```
wti-website/
├── admin/                    # CMS (login required)
│   ├── includes/             # admin auth, header, footer
│   ├── dashboard.php
│   ├── halls.php / hall_form.php        # hall CRUD + photo upload
│   ├── categories.php / items.php       # rate list CRUD
│   ├── bookings.php / booking_view.php  # booking review/approve
│   ├── announcements.php                # photo notices / gallery CRUD
│   ├── enquiries.php
│   ├── pages.php
│   ├── admins.php
│   ├── login.php / logout.php
├── includes/                 # shared public config, functions, header, footer
├── assets/css, assets/js     # styling & behaviour
├── database/
│   ├── schema.sql             # full schema + seed data (fresh installs)
│   └── upgrade_v2.sql         # for existing installs: encoding fix + gallery table
├── index.php, halls.php, facilities.php, booking.php,
│   booking_success.php, track_booking.php, contact.php,
│   announcements.php          # public Notices & Gallery page
└── uploads/
    ├── halls/                 # hall photos
    └── announcements/         # gallery / notice photos
```
