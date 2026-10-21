<?php
/**
 * MediBook homepage.
 *
 * Layout and design were taken from the original index.php; the CSS now
 * lives in assets/css/ and the navbar/footer are shared includes, so every
 * page of the site looks the same.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db          = tryGetDB();
$departments = getDepartments($db);

// Small icon for each department name (extra departments added later by the
// admin panel automatically get the default hospital icon).
$departmentIcons = [
    'Cardiology'   => '❤️',
    'Neurology'    => '🧠',
    'Orthopedics'  => '🦴',
    'Pediatrics'   => '👶',
    'Dermatology'  => '✨',
];

$pageTitle = 'MediBook | Your Health, Our Priority';
$baseUrl   = '';
$activeNav = 'home';
$extraCss  = ['assets/css/home.css'];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" id="home">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="eyebrow">♥ &nbsp; Your health, our priority</span>

                <h1>
                    Healthcare Made<br>
                    <span>Simple &amp; Smart.</span>
                </h1>

                <p class="hero-description">
                    Find the right doctors, explore medical departments,
                    and manage your hospital appointments — all in one place.
                </p>

                <div class="hero-actions">
                    <a href="#appointment" class="btn-primary-custom">
                        Book an Appointment <span aria-hidden="true">→</span>
                    </a>
                    <a href="doctors.php" class="btn-outline-custom">
                        Explore Doctors
                    </a>
                </div>

                <div class="hero-checks">
                    <div class="check-item"><span class="check-mark">✓</span> Easy booking</div>
                    <div class="check-item"><span class="check-mark">✓</span> Trusted care</div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="hero-image-frame">
                        <img
                            src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=1000&q=85"
                            alt="Doctor ready to provide healthcare"
                        >
                    </div>

                    <div class="floating-card card-one">
                        <div class="floating-icon">🩺</div>
                        <div>
                            <strong>Professional Care</strong>
                            <small>Care that puts you first</small>
                        </div>
                    </div>

                    <div class="floating-card card-two">
                        <div class="floating-icon">📅</div>
                        <div>
                            <strong>Easy Appointments</strong>
                            <small>Plan your visit simply</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctor search (submits to doctors.php, where results are shown) -->
<section class="search-panel" id="appointment">
    <div class="container">
        <div class="search-box">
            <form method="get" action="doctors.php" data-validate>
                <div class="row align-items-center g-3">
                    <div class="col-lg-3">
                        <div class="search-heading">Find the right doctor</div>
                        <div class="search-subtitle">Start your healthcare journey</div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="department" class="form-label small fw-semibold">
                            Medical department
                        </label>
                        <select class="form-select" id="department" name="department">
                            <option value="">All departments</option>
                            <?php foreach ($departments as $department): ?>
                            <option value="<?= (int) $department['id'] ?>">
                                <?= e($department['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="q" class="form-label small fw-semibold">
                            Doctor's name
                        </label>
                        <input type="text" class="form-control" id="q" name="q"
                               data-label="Doctor's name" data-rules="maxlength:100"
                               placeholder="Enter doctor's name">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <button type="submit" class="btn-primary-custom w-100">
                            Search Doctors →
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Trust Features -->
<section class="trust-section">
    <div class="container">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">🩺</div>
                    <div>
                        <strong>Experienced Doctors</strong>
                        <small>Find care by specialty</small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">🏥</div>
                    <div>
                        <strong>Medical Departments</strong>
                        <small>Explore available specialties</small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">📅</div>
                    <div>
                        <strong>Easy Appointments</strong>
                        <small>Plan your hospital visit</small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">🔒</div>
                    <div>
                        <strong>Privacy Matters</strong>
                        <small>Designed with privacy in mind</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Departments (loaded from the departments table) -->
<section class="section section-soft" id="departments">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">Our Departments</span>
            <h2>Find Care in Your Specialty</h2>
            <p>
                Explore medical specialties and find the right department
                for your healthcare needs.
            </p>
        </div>

        <?php if (!$db): ?>
            <?= dbUnavailableNotice() ?>
        <?php elseif (!$departments): ?>
            <p class="text-center text-muted">
                No departments have been added yet.
            </p>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($departments as $department): ?>
            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card"
                   href="doctors.php?department=<?= (int) $department['id'] ?>">
                    <div class="department-icon">
                        <?= $departmentIcons[$department['name']] ?? '🏥' ?>
                    </div>
                    <h3><?= e($department['name']) ?></h3>
                    <p><?= e($department['description'] ?? 'Medical care') ?></p>
                    <span class="department-arrow">Explore →</span>
                </a>
            </div>
            <?php endforeach; ?>

            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card" href="doctors.php">
                    <div class="department-icon" style="background:#fff5d9">＋</div>
                    <h3>All Departments</h3>
                    <p>Explore specialties</p>
                    <span class="department-arrow">View all →</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works -->
<section class="section" id="how-it-works">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">Simple &amp; Convenient</span>
            <h2>Your Appointment in Three Steps</h2>
            <p>Our booking workflow makes arranging a visit easier.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <h3>Find Your Doctor</h3>
                    <p>
                        Explore medical departments and identify a doctor
                        based on your healthcare needs.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">02</div>
                    <h3>Choose a Schedule</h3>
                    <p>
                        View available appointment times and choose a
                        suitable visit time.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">03</div>
                    <h3>Manage Your Visit</h3>
                    <p>
                        Review your appointment details and manage your
                        booking through your patient account.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta" id="about">
    <div class="container">
        <div class="cta-panel">
            <div>
                <span class="section-label">Your health comes first</span>
                <h2>Ready to take the next step?</h2>
                <p>Explore departments and start planning your hospital visit.</p>
            </div>
            <a href="doctors.php" class="btn-primary-custom">
                Find a Doctor →
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
