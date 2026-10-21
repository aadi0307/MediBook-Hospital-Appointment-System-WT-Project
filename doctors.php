<?php
/**
 * Doctors page - lists doctors from the database.
 *
 * The search box on the homepage submits here with two GET parameters:
 *   ?department=<id>  and  ?q=<doctor name>
 * GET is used because searching should not create any data (safe + the
 * user can bookmark or share the result link).
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = tryGetDB();

// Read and clean the search values from the URL
$departmentId = isset($_GET['department']) ? (int) $_GET['department'] : 0;
$searchName   = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$departments = getDepartments($db);
$doctors     = getDoctors($db, $departmentId, $searchName);

$pageTitle = 'Find a Doctor | MediBook';
$baseUrl   = '';
$activeNav = 'doctors';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">Our Doctors</span>
        <h1>Find the Right Doctor</h1>
        <p>
            Search by department or doctor name to see qualifications,
            experience and consultation fees.
        </p>
    </div>
</section>

<!-- Search / filter box -->
<section class="search-panel">
    <div class="container">
        <div class="search-box">
            <form method="get" action="doctors.php" data-validate>
                <div class="row align-items-center g-3">
                    <div class="col-lg-3">
                        <div class="search-heading">Search doctors</div>
                        <div class="search-subtitle">
                            Filter the list below instantly
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="department" class="form-label small fw-semibold">
                            Medical department
                        </label>
                        <select class="form-select" id="department" name="department">
                            <option value="">All departments</option>
                            <?php foreach ($departments as $department): ?>
                            <option value="<?= (int) $department['id'] ?>"
                                <?= $departmentId === (int) $department['id'] ? 'selected' : '' ?>>
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
                               value="<?= e($searchName) ?>"
                               data-label="Doctor's name" data-rules="maxlength:100"
                               placeholder="Enter doctor's name">
                    </div>

                    <div class="col-lg-2">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <button type="submit" class="btn-primary-custom w-100">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php if (!$db): ?>
            <?= dbUnavailableNotice() ?>

        <?php elseif (!$doctors): ?>
            <div class="text-center py-4">
                <div class="avatar-circle mx-auto mb-3" style="border-radius:18px">🔍</div>
                <h2 style="font-size:20px">No doctors found</h2>
                <p class="text-muted">
                    Nothing matched your search.
                    <a href="doctors.php">Show all doctors</a>
                </p>
            </div>

        <?php else: ?>

            <p class="text-muted mb-4">
                Showing <?= count($doctors) ?>
                doctor<?= count($doctors) === 1 ? '' : 's' ?>
                <?= $departmentId ? 'in this department' : '' ?>
                <?= $searchName ? 'matching "' . e($searchName) . '"' : '' ?>
            </p>

            <div class="row g-4">
                <?php foreach ($doctors as $doctor): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card-soft">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-circle">
                                <?= e(initials($doctor['name'])) ?>
                            </div>
                            <div>
                                <h3 style="font-size:17px;font-weight:800;margin:0">
                                    <?= e($doctor['name']) ?>
                                </h3>
                                <span class="text-primary fw-semibold" style="font-size:13px">
                                    <?= e($doctor['specialization']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="badge rounded-pill text-bg-light border">
                                <?= e($doctor['department_name']) ?>
                            </span>
                        </div>

                        <ul class="list-unstyled mb-3" style="font-size:13px;color:var(--muted)">
                            <li class="mb-1"><strong>Qualification:</strong>
                                <?= e($doctor['qualification']) ?></li>
                            <li class="mb-1"><strong>Experience:</strong>
                                <?= (int) $doctor['experience_years'] ?> years</li>
                            <li class="mb-1"><strong>Consultation fee:</strong>
                                ₹<?= number_format((float) $doctor['consultation_fee'], 0) ?></li>
                        </ul>

                        <p style="font-size:13px;color:var(--muted)">
                            <?= e($doctor['bio'] ?? '') ?>
                        </p>

                        <a class="btn-primary-custom w-100 mt-2"
                           href="patient/appointment.php?doctor_id=<?= (int) $doctor['id'] ?>">
                            Book Appointment →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
