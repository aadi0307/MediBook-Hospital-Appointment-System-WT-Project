<?php
/**
 * About page - static information about MediBook.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us | MediBook';
$baseUrl   = '';
$activeNav = 'about';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">About MediBook</span>
        <h1>Healthcare Made Simple</h1>
        <p>
            MediBook is a web-based hospital appointment system that helps
            patients find doctors and manage their visits online.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7 content-block">
                <span class="section-label">Who we are</span>
                <h2>One place for your hospital visits</h2>
                <p>
                    MediBook brings the hospital's departments, doctors and
                    appointment schedules into one simple website. Patients
                    can browse doctors, check which days a doctor is
                    available, book a time slot and keep track of their
                    appointments - without calling the hospital.
                </p>
                <p>
                    Everything you see on this site is powered by PHP and a
                    MySQL database. Doctor information, working schedules and
                    appointments are read from the database, so the list you
                    see always matches the hospital's real records.
                </p>

                <div class="row g-4 mt-1">
                    <div class="col-sm-6">
                        <div class="card-soft">
                            <div class="step-number">01</div>
                            <h3 style="font-size:16px;font-weight:800">For patients</h3>
                            <p style="font-size:13px;color:var(--muted);margin:0">
                                Register once, then book, review and cancel
                                your own appointments from your profile.
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card-soft">
                            <div class="step-number">02</div>
                            <h3 style="font-size:16px;font-weight:800">For the hospital</h3>
                            <p style="font-size:13px;color:var(--muted);margin:0">
                                Doctors, departments and appointment records
                                are managed centrally through the hospital's
                                admin panel.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="info-panel">
                    <h3>Project quick facts</h3>
                    <div class="info-line">
                        <div>🖥️</div>
                        <div>
                            <strong>Built with</strong>
                            <span>HTML, CSS, JavaScript, Bootstrap 5, PHP, MySQL</span>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>🔒</div>
                        <div>
                            <strong>Database access</strong>
                            <span>PDO with prepared statements</span>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>🎓</div>
                        <div>
                            <strong>Purpose</strong>
                            <span>Web Technologies college project</span>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>🧩</div>
                        <div>
                            <strong>Modules</strong>
                            <span>Public pages, patient account, admin panel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">How it works</span>
            <h2>Booking in Three Steps</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <h3>Find Your Doctor</h3>
                    <p>Search doctors by department or by name.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">02</div>
                    <h3>Choose a Schedule</h3>
                    <p>Pick a date the doctor works, then a free 30-minute slot.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">03</div>
                    <h3>Manage Your Visit</h3>
                    <p>Review, track and cancel bookings from "My Appointments".</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
