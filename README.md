# MediBook — Hospital Appointment System

A web-based hospital appointment booking system built with PHP and MySQL. Patients can find doctors and book appointments online, doctors can manage their own visit list, and hospital administrators can manage doctors, patients, departments, schedules, and appointments.

## About the Project

MediBook is a Web Technologies college project. It keeps a deliberately small, readable stack (PHP, MySQL, HTML, CSS, Bootstrap, and a little vanilla JavaScript) so the code can be explained easily during a project viva.

## Features

### Patient

- Register a patient account and sign in.
- Browse hospital departments and doctors.
- View doctor profiles, specialisations, fees, and availability.
- Book an appointment from an available schedule slot.
- View upcoming appointments and appointment history.
- Cancel an upcoming appointment.
- Update profile details and change password.

### Doctor

- Role-protected doctor dashboard.
- See today's appointments and upcoming visits.
- View relevant patient details for each appointment.
- Mark appointments as completed or cancelled, limited to that doctor's own records.

### Admin

- Dashboard with summary counts, recent appointments, and a daily snapshot.
- Add doctor accounts and pause/reactivate existing doctors.
- Browse the patient directory and each patient's visit history.
- Add departments and pause/reactivate them.
- Add schedule blocks and hide/show them.
- View and filter all appointments, and update appointment statuses.

### Security and Access Control

- Passwords are stored with `password_hash()` and checked with `password_verify()`.
- Login checks both the credentials and the selected role against the role stored in the database.
- User ID, name, email, and role are stored in the PHP session; the session ID is regenerated on login.
- Every patient, doctor, and admin page calls a server-side `requireRole(...)` guard. Unauthorized visitors are redirected, never shown the page.
- Logout is a POST request with CSRF protection and clears the session and session cookie.
- POST forms include CSRF tokens.
- Database access uses PDO prepared statements.
- There is no public admin registration.
- Open redirects after login are blocked by allowing only local paths.

## Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5, vanilla JavaScript
- **Backend:** PHP (8.0+)
- **Database:** MySQL / MariaDB
- **Database access:** PDO with prepared statements
- **Local development:** XAMPP

## Project Structure

```text
MediBook/
├── admin/                  # Admin dashboard and management pages
├── assets/
│   ├── css/                # Shared, auth, and dashboard styles
│   └── js/                 # Form validation and login behaviour
├── config/
│   └── database.php        # PDO connection settings
├── database/
│   ├── schema.sql          # Tables + sample data
│   └── seed.sql            # phpMyAdmin dump of the same sample data
├── doctor/                 # Doctor dashboard and appointments
├── includes/               # Auth, functions, header, footer, admin nav
├── patient/                # Patient dashboard, booking, history, profile
├── scripts/
│   └── create-admin.php    # CLI-only admin account creation
├── index.php
├── doctors.php
├── login.php
├── logout.php
├── register.php
└── README.md
```

## Local Setup

1. Install XAMPP and start **Apache** and **MySQL**.
2. In phpMyAdmin, create a database named `medibook_db` using the `utf8mb4` character set.
3. Import **`database/schema.sql`**. This file creates all tables **and inserts the sample data** used by the project.
4. Check the connection details in `config/database.php`. The defaults match a standard XAMPP install: host `127.0.0.1`, port `3306`, user `root`, empty password, database `medibook_db`.
5. Place the project folder inside XAMPP's `htdocs` directory.
6. Open `http://localhost/MediBook/` in your browser.

> **Important:** `database/schema.sql` already contains the sample users, doctors, departments, schedules, and appointments. Do **not** import `database/seed.sql` afterwards — it contains the same rows and will fail with duplicate primary-key errors. `seed.sql` is kept only as the original phpMyAdmin data dump.

## Demo Accounts

All sample accounts use the password `password`.

| Role | Email |
| --- | --- |
| Patient | `rahul@example.test` |
| Doctor | `aarav@medibook.test` |
| Admin | `admin@medibook.test` |

The login page has a role selector. The selected role must match the account's role in the database, otherwise sign-in is refused.

## Creating an Admin Account

Admin accounts are created only from the command line, so there is no public admin registration page. From the project folder:

```bash
php scripts/create-admin.php "Admin Name" "admin@example.com" "strong-password"
```

The password must be at least 8 characters, and the email must not already exist. The script refuses to run through a web server.

## How Role Routing Works

- After login, each role is sent to its own dashboard:
  - Patient → `patient/dashboard.php`
  - Doctor → `doctor/dashboard.php`
  - Admin → `admin/dashboard.php`
- Visiting another role's page directly returns a redirect to the signed-in user's own dashboard.
- Guests who open a protected page are redirected to the login page and returned to the requested page after signing in.

## Project Status

The role-based login, dashboards, appointment flows, and admin management pages are implemented. The project is ready for a local demonstration and viva walkthrough.

## Team

Developed as a college Web Technologies project.
