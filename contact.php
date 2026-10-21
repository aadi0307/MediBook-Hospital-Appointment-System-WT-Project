<?php
/**
 * Contact page - saves the message into the contact_messages table.
 *
 * The form uses POST, and after a successful save we redirect
 * (POST/Redirect/GET pattern) so refreshing the page does not send
 * the message twice.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = tryGetDB();

$errors = [];

// Keep whatever the visitor typed while we show errors
$old = [
    'name'    => trim((string) ($_POST['name'] ?? '')),
    'email'   => trim((string) ($_POST['email'] ?? '')),
    'subject' => trim((string) ($_POST['subject'] ?? '')),
    'message' => trim((string) ($_POST['message'] ?? '')),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Server-side validation (JavaScript checks first, this is the
    //     real security check because JS can be bypassed) ---
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $errors['name'] = 'Please enter your name (max 100 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($old['subject'] === '' || mb_strlen($old['subject']) > 200) {
        $errors['subject'] = 'Please enter a subject (max 200 characters).';
    }
    if ($old['message'] === '') {
        $errors['message'] = 'Please write your message.';
    }

    if (!$errors) {
        if (!$db) {
            $errors['message'] = 'The database is unavailable, so the message could not be saved.';
        } else {
            $sql = 'INSERT INTO contact_messages (name, email, subject, message)
                    VALUES (:name, :email, :subject, :message)';
            $statement = $db->prepare($sql);
            $statement->execute([
                ':name'    => $old['name'],
                ':email'   => $old['email'],
                ':subject' => $old['subject'],
                ':message' => $old['message'],
            ]);

            flashSet('success', 'Thank you! Your message has been sent.');
            header('Location: contact.php');
            exit;
        }
    }
}

$pageTitle = 'Contact Us | MediBook';
$baseUrl   = '';
$activeNav = 'contact';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">Get in touch</span>
        <h1>Contact Us</h1>
        <p>
            Questions about appointments or the website? Send us a message
            and we will get back to you.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">

            <!-- Contact form -->
            <div class="col-lg-7">
                <div class="card-soft" style="height:auto">
                    <h2 style="font-size:20px;font-weight:800">Send a message</h2>
                    <p style="font-size:13px;color:var(--muted)">
                        All fields are required.
                    </p>

                    <form method="post" action="contact.php" data-validate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Your name</label>
                                <input type="text" class="form-control" id="name"
                                       name="name" value="<?= e($old['name']) ?>"
                                       data-label="Your name"
                                       data-rules="required|maxlength:100">
                                <?php if (isset($errors['name'])): ?>
                                    <span class="field-error"><?= e($errors['name']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email"
                                       name="email" value="<?= e($old['email']) ?>"
                                       data-label="Email address"
                                       data-rules="required|email">
                                <?php if (isset($errors['email'])): ?>
                                    <span class="field-error"><?= e($errors['email']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject"
                                       name="subject" value="<?= e($old['subject']) ?>"
                                       data-label="Subject"
                                       data-rules="required|maxlength:200">
                                <?php if (isset($errors['subject'])): ?>
                                    <span class="field-error"><?= e($errors['subject']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message"
                                          name="message"
                                          data-label="Message"
                                          data-rules="required|maxlength:2000"><?= e($old['message']) ?></textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <span class="field-error"><?= e($errors['message']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn-primary-custom">
                                    Send Message →
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact details -->
            <div class="col-lg-5">
                <div class="info-panel">
                    <h3>MediBook support</h3>
                    <div class="info-line">
                        <div>📍</div>
                        <div>
                            <strong>Hospital address</strong>
                            <span>12 Health Avenue, City Centre</span>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>📧</div>
                        <div>
                            <strong>Email</strong>
                            <span>support@medibook.test</span>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>📞</div>
                        <div>
                            <strong>Phone</strong>
                            <span>+91 90000 00000</span>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>🕘</div>
                        <div>
                            <strong>Reception hours</strong>
                            <span>Monday - Saturday, 9:00 AM - 6:00 PM</span>
                        </div>
                    </div>
                </div>

                <div class="info-panel mt-3">
                    <h3>Quick help</h3>
                    <p style="font-size:13px;color:var(--muted);margin-bottom:8px">
                        Want to book a visit right away?
                    </p>
                    <a class="btn-outline-custom" href="doctors.php">Find a Doctor →</a>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
