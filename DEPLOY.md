# Deploying TSHS Clinic to InfinityFree

---

## Step 1 — Create a free account

1. Go to https://www.infinityfree.com and sign up.
2. Create a new hosting account. You will get a subdomain like:
   `yourname.infinityfreeapp.com`

---

## Step 2 — Create the MySQL database

1. Log in to your InfinityFree **Client Area**.
2. Go to your hosting account → **cPanel** → **MySQL Databases**.
3. Create a new database (e.g. `record_management`).
4. Note down the following — you will need them:
   - **DB Host** — looks like `sql1234.infinityfreeapp.com`
   - **DB Name** — looks like `if0_12345678_record_management`
   - **DB User** — looks like `if0_12345678`
   - **DB Password** — what you set

---

## Step 3 — Import the database

1. In cPanel → open **phpMyAdmin**.
2. Click on your database name in the left sidebar.
3. Click the **Import** tab.
4. Choose file: `database/db.sql`
5. Click **Go**.

> ✅ All tables and predefined symptoms will be created.

---

## Step 4 — Update your `.env` file

Open `.env` in the project root and update it with your InfinityFree credentials:

```
DB_HOST=sql1234.infinityfreeapp.com
DB_NAME=if0_12345678_record_management
DB_USER=if0_12345678
DB_PASS=your_password_here
DB_CHARSET=utf8mb4
```

---

## Step 5 — Upload all files

1. In cPanel → open **File Manager** → go into the `htdocs` folder.
2. Upload **everything** from your project root into `htdocs`:

```
htdocs/
├── .env                  ← with your production credentials
├── .htaccess
├── app/
├── database/
├── public/
│   ├── admin/
│   ├── assets/
│   ├── css/
│   ├── js/
│   ├── index.php
│   └── login.php
└── routes/
```

> **Tip:** Zip your entire project folder, upload the zip, then extract it inside `htdocs`.
> Make sure you do NOT upload `vendor/`, `.git/`, or any local-only files.

---

## Step 6 — Test the site

Open your browser and visit:
```
http://yourname.infinityfreeapp.com
```

- The **public form** should load at `/`
- The **admin login** should load at `/login.php`
- Admin credentials: `admin` / `admin123`

---

## Step 7 — Post-deployment checklist

- [ ] Public form submits correctly
- [ ] Admin can log in
- [ ] Medicines load in the form dropdown
- [ ] Patient records can be added, edited, deleted
- [ ] Analytics page loads with charts
- [ ] Print report works
- [ ] Low stock notification bell works

---

## Common Issues

| Problem | Fix |
|---|---|
| Blank white page | Check that `.htaccess` uploaded correctly |
| Database connection error | Double-check `.env` credentials |
| 403 Forbidden | Make sure `htdocs` folder permissions are set to `755` |
| CSS/JS not loading | Clear browser cache (Ctrl+Shift+R) |
| Session not persisting | InfinityFree supports sessions — should work as-is |

---

## Security Notes

- The `.env` file is protected by `.htaccess` — no one can read it from the browser.
- `app/`, `routes/`, and `database/` folders are blocked from direct web access.
- Error messages are hidden in production automatically.
- Change the admin password from `admin123` to something strong in `routes/auth_api.php`.

---

> Built with PHP 8 · MySQL · Chart.js · Boxicons
> TSHS Clinic Management System — Talavera Senior High School
