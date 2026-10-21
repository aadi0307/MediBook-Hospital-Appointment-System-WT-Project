<?php
/**
 * Shared page footer: footer + scripts.
 *
 * Optional variable a page may set before including:
 *   $extraJs - array of extra script paths, e.g. ['assets/js/booking.js']
 */

$extraJs = $extraJs ?? [];
?>
</main>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="footer-brand">
                    <span class="footer-plus">✚</span> MediBook.
                </div>
                <p class="footer-description">
                    A hospital appointment system designed to make
                    finding doctors and managing appointments simpler.
                </p>
            </div>

            <div class="col-6 col-lg-2">
                <div class="footer-heading">Quick Links</div>
                <div class="footer-links">
                    <a href="<?= $baseUrl ?>index.php">Home</a>
                    <a href="<?= $baseUrl ?>doctors.php">Doctors</a>
                    <a href="<?= $baseUrl ?>about.php">About Us</a>
                    <a href="<?= $baseUrl ?>contact.php">Contact</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="footer-heading">Patient Care</div>
                <div class="footer-links">
                    <a href="<?= $baseUrl ?>doctors.php">Find a Doctor</a>
                    <a href="<?= $baseUrl ?>patient/appointment.php">Book Appointment</a>
                    <a href="<?= $baseUrl ?>patient/my-appointments.php">My Appointments</a>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="footer-heading">Project Information</div>
                <p class="footer-description">
                    Developed as a Web Technologies college project
                    using PHP, MySQL, HTML, CSS and JavaScript.
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; <?= date('Y') ?> MediBook. All rights reserved.
            <br>Healthcare made simpler.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $baseUrl ?>assets/js/main.js"></script>
<?php foreach ($extraJs as $jsFile): ?>
<script src="<?= $baseUrl . e($jsFile) ?>"></script>
<?php endforeach; ?>

</body>
</html>
