# MediBook — Hospital Appointment System

A web-based hospital appointment booking system designed to make it easier for patients to find doctors and book appointments online.

## About the Project

MediBook is a Web Technologies college project built using PHP and MySQL. It provides a platform for patients to access doctor information and manage hospital appointments, with administrative features for managing the system.

## Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Database access:** PDO with prepared statements
- **Local development:** XAMPP
- **Code editor:** Visual Studio Code

## Planned Features

- Patient registration and login
- Browse hospital departments
- View doctors and their availability
- Book and manage appointments
- Admin dashboard
- Manage doctors, departments, and appointment records

## Project Structure

```text
MediBook/
├── admin/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
├── database/
│   ├── schema.sql
│   └── seed.sql
├── includes/
├── patient/
└── README.md
```

## Local Setup

1. Install XAMPP and start Apache and MySQL.
2. Create a MySQL database named `medibook_db` in phpMyAdmin.
3. Import `database/schema.sql`.
4. Import `database/seed.sql`.
5. Configure the local database connection.
6. Place the project folder inside XAMPP's `htdocs` directory.
7. Open `http://localhost/MediBook/` in your browser.

## Project Status

Currently in development.

## Team

Developed as a college Web Technologies project.
