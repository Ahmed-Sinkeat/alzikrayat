# Alzikrayat

Photo sharing web app for the web programming course (Project 1). Users sign up, upload photos with a short story, browse the gallery, comment, and delete their own photos.

Plain PHP + MySQL, no framework. Handwritten MVC with a small regex router.

# how to run 
## Quick run (no Apache)

Needs PHP 8 and MySQL/MariaDB running.

```bash
mysql -u root < database/schema.sql
php -S localhost:8000 -t public public/index.php
```

Then open `http://localhost:8000`.

## Setup (XAMPP)

1. Copy the folder into `htdocs` (e.g. `C:\xampp\htdocs\alzikrayat`).
2. Start Apache and MySQL.
3. Import `database/schema.sql` in phpMyAdmin. It creates the `alzikrayat` database.
4. Make sure `mod_rewrite` is on and `AllowOverride All` is set for `htdocs`.
5. Open `http://localhost/alzikrayat/public/`.

The default DB login is `root` with no password (`config/database.php`). You can override it with Apache `SetEnv DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS` (or `DB_SOCKET`).

On Linux, `public/images/uploads/` must be writable by Apache.

## Routes

| Method | Route | |
| --- | --- | --- |
| GET | `/` | home |
| GET | `/about` | about |
| GET/POST | `/register`, `/login` | auth |
| POST | `/logout` | logout |
| GET | `/photos` | gallery |
| GET | `/photos/create` | upload form |
| POST | `/photo/store` | save upload |
| GET | `/photo/{id}` | photo + comments |
| POST | `/photo/{id}/comments` | add comment |
| POST | `/photo/{id}/delete` | delete own photo |

## Notes

- Validation happens in three places: HTML5 attributes, `public/js/validation.js`, and again in PHP.
- Passwords use `password_hash`, all queries use prepared statements, forms have CSRF tokens.
- Uploads: JPEG/PNG/GIF/WebP up to 5 MB, checked by MIME type and saved with a random name.
- Extra feature: "Memory Mood" on the photo page switches between original, warm, and monochrome filters (CSS only, the file isn't changed).

## Tests

```bash
php tests/router_test.php
php tests/security_test.php
php tests/mysql_integration_test.php   # needs the database
```

The architecture report is in `docs/architecture-report.pdf`.

Name: Ahmed Mohammed Omer
