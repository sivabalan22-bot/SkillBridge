# SkillBridge – Student Skill Exchange Platform

SkillBridge is a small PHP and MySQL website for students to share skills, find peer teachers, and propose skill exchanges. It uses PHP sessions, password hashing, prepared SQL statements, and CSRF-protected forms.

## Requirements

- XAMPP with Apache, PHP 8.1+ and MySQL/MariaDB
- A modern web browser

## Run with XAMPP

1. Copy the `SkillBridge` project folder into XAMPP's `htdocs` directory. For example: `C:\xampp\htdocs\SkillBridge`.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Visit `http://localhost/phpmyadmin`.
4. Choose **Import**, select `database.sql` from the project folder, and click **Import**. The script creates the `skillbridge` database and its tables.
5. If your local MySQL credentials are different from XAMPP's defaults, edit the host, database name, username, and password at the top of `includes/config.php`. The defaults are `127.0.0.1`, `skillbridge`, `root`, and an empty password.
6. Open `http://localhost/SkillBridge/` in your browser and create a student account.

## Main pages

- `index.php` – public homepage
- `register.php` and `login.php` – student authentication
- `dashboard.php` – your overview and incoming requests
- `profile.php` – update your name, course, year, and introduction
- `skills.php` – manage skills you can teach and want to learn
- `discover.php` – search students and propose an exchange
- `requests.php` – accept, decline, and review requests

## How an exchange works

Add at least one teaching skill to your profile. In **Discover**, pick a skill you can offer and one the other student can teach, optionally add a short note, and send the request. The recipient can accept or decline it from **Requests**. Accepted exchanges are shown in both students' request lists.

Student email addresses are used for login and are not displayed in discovery. This mini project does not include messaging or contact sharing; students can arrange accepted exchanges in person.
