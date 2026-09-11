# RevSpecs Admin Panel

This version replaces the old `subjects-config.php` hardcoded subject list with MySQL while keeping the existing `revspecs.php`, `reviewer.php`, and `generate_quiz.php` interfaces working.

## 1. Put the files in XAMPP

Copy the contents of this folder into your RevSpecs project under `htdocs`, for example:

`C:\xampp\htdocs\RevSpecs\`

Keep these folders:

- `admin/`
- `config/`
- `uploads/reviewers/`

## 2. Create the database

1. Start **Apache** and **MySQL** in XAMPP.
2. Open phpMyAdmin.
3. Import `database.sql`.

The SQL creates the `revspecs` database, `admins` table, `subjects` table, and imports your current 12 subjects.

## 3. Check database credentials

Open `config/database.php`.

The defaults are:

- host: `127.0.0.1`
- database: `revspecs`
- username: `root`
- password: empty

Change them if your MySQL setup uses a password.

## 4. Create your admin account

Open:

`http://localhost/RevSpecs/admin/setup.php`

Create your username and password.

Then **delete `admin/setup.php`** from the server.

Log in at:

`http://localhost/RevSpecs/admin/login.php`

## 5. Use the panel

From the dashboard you can:

- Add subjects
- Edit subject code/name/year
- Upload reviewer PDFs
- Replace PDFs
- View PDFs
- Delete subjects and their uploaded PDFs

## Important

The old hardcoded `subjects-config.php` is replaced with a database-backed version. Do not keep the old array version or it will overwrite the DB-driven behavior.

Your existing `revspecs.php`, `reviewer.php`, and `generate_quiz.php` can continue using:

`include 'subjects-config.php';`

The admin panel changes the database, and those pages automatically receive the updated subject/PDF data.
