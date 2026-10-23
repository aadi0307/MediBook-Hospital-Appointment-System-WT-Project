<?php
/**
 * Appointment booking (patient area).
 *
 * How the booking works (one form, two submit buttons):
 *   1. Patient picks a doctor + a date, presses "Show available slots".
 *      PHP reads that doctor's schedule for the weekday of the chosen date
 *      (schedules table), builds 30-minute slots and greys out slots that
 *      are already booked (appointments table).
 *   2. Patient picks a free slot and presses "Confirm booking" - a new row
 *      is inserted with status "pending". The UNIQUE key on
 *      (doctor_id, appointment_date, start_time) is the final safety net
 *      against double booking.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('patient', '../');

$db       = tryGetDB();
$doctors  = getDoctors($db);
$parentId = '../'; // links back to the site root

$errors = [];

// State: ?doctor_id= preselects a doctor, POST keeps the chosen values
$doctorId = isset($_POST['doctor_id']) ? (int) $_POST['doctor_id']
           : (isset($_GET['doctor_id']) ? (int) $_GET['doctor_id'] : 0);
$date     = isset($_POST['date']) ? (string) $_POST['date']
           : (isset($_GET['date']) ? (string) $_GET['date'] : '');
$reason   = trim((string) ($_POST['reason'] ?? ''));
$slot     = (string) ($_POST['start_time'] ?? '');

$doctor   = null;
foreach ($doctors as $candidate) {
    if ((int) $candidate['id'] === $doctorId) {
        $doctor = $candidate;
        break;
    }
}

$showSlots = isset($_POST['show_slots']);
$bookSlot  = isset($_POST['book_slot']);

/* ---------- Step 2: confirm the booking (POST, button "Confirm") ---------- */

if ($bookSlot) {
    $today = date('Y-m-d');

    if (!csrfIsValid()) {
        $errors[] = 'Your session expired. Please review the form and try again.';
    }
    if (!$doctor) {
        $errors[] = 'Please choose a doctor.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < $today) {
        $errors[] = 'Please choose today or a future date.';
    }
    if ($slot === '') {
        $errors[] = 'Please choose a time slot.';
    }
    if (mb_strlen($reason) > 1000) {
        $errors[] = 'The reason is too long (max 1000 characters).';
    }

    $schedule = null;
    if (!$errors && $doctor) {
        $weekday    = (int) date('w', strtotime($date)); // 0 = Sunday
        $schedule   = getScheduleForDay($db, (int) $doctor['id'], $weekday);
        $slots      = $schedule
            ? generateSlots($schedule['start_time'], $schedule['end_time'], (int) $schedule['slot_duration_minutes'])
            : [];
        $booked     = getBookedStartTimes($db, (int) $doctor['id'], $date);

        if (!slotIsAvailable($slots, $booked, $slot)) {
            $errors[] = 'That time slot is not available any more. Please pick another one.';
        } elseif ($date === $today && $slot <= date('H:i')) {
            $errors[] = 'That time has already passed today. Please pick a later slot.';
        } elseif ($schedule) {
            // All good - insert the appointment
            try {
                $sql = 'INSERT INTO appointments
                            (patient_id, doctor_id, appointment_date,
                             start_time, end_time, reason, status)
                        VALUES (:patient, :doctor, :date, :start, :end, :reason, \'pending\')';

                $statement = $db->prepare($sql);
                $statement->execute([
                    ':patient' => currentUser()['id'],
                    ':doctor'  => (int) $doctor['id'],
                    ':date'    => $date,
                    ':start'   => $slot . ':00',
                    ':end'     => slotEndTime($slot, (int) $schedule['slot_duration_minutes']),
                    ':reason'  => $reason !== '' ? $reason : null,
                ]);

                flashSet('success', 'Appointment booked for ' . formatDate($date)
                    . ' at ' . formatTime($slot . ':00')
                    . '. It is waiting for confirmation.');
                header('Location: my-appointments.php');
                exit;
            } catch (PDOException $exception) {
                // 23000 = duplicate key: someone else took the same slot
                $errors[] = $exception->getCode() === '23000'
                    ? 'Sorry, that slot was just booked by someone else. Pick another one.'
                    : 'The appointment could not be saved. Please try again.';
            }
        }
    }
}

/* ---------- Values used when rendering the form ---------- */

$weekdays  = $doctor ? getAvailableWeekdays($db, (int) $doctor['id']) : [];
$schedule  = null;
$slots     = [];
$booked    = [];

if ($doctor && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $date >= date('Y-m-d')) {
    $weekday  = (int) date('w', strtotime($date));
    $schedule = getScheduleForDay($db, (int) $doctor['id'], $weekday);
    if ($schedule) {
        $slots  = generateSlots($schedule['start_time'], $schedule['end_time'], (int) $schedule['slot_duration_minutes']);
        $booked = getBookedStartTimes($db, (int) $doctor['id'], $date);
    }
}

$pageTitle = 'Book an Appointment | MediBook';
$baseUrl   = '../';
$activeNav = 'appointments';

require __DIR__ . '/../includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">Patient area</span>
        <h1>Book an Appointment</h1>
        <p>Choose your doctor, pick a date the doctor works, then select a free time slot.</p>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php if (!$db): ?>
            <?= dbUnavailableNotice() ?>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endforeach; ?>

        <div class="card-soft" style="height:auto;max-width:820px;margin:0 auto">
            <form method="post" action="appointment.php" data-validate id="bookingForm">
                <?= csrfField() ?>

                <!-- Step 1: doctor + date -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="doctor_id" class="form-label">1. Choose a doctor</label>
                        <select class="form-select" id="doctor_id" name="doctor_id"
                                data-label="Doctor" data-rules="required" required>
                            <option value="">-- Select doctor --</option>
                            <?php foreach ($doctors as $option): ?>
                            <option value="<?= (int) $option['id'] ?>"
                                <?= $doctorId === (int) $option['id'] ? 'selected' : '' ?>>
                                <?= e($option['name']) ?> - <?= e($option['department_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="date" class="form-label">2. Choose a date</label>
                        <input type="date" class="form-control" id="date" name="date"
                               value="<?= e($date) ?>"
                               min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+6 months')) ?>"
                               data-label="Date" data-rules="required">
                    </div>
                </div>

                <button type="submit" name="show_slots" value="1"
                        class="btn-outline-custom mb-4">
                    Show available slots
                </button>

                <!-- Step 3: slots -->
                <?php if ($doctor && $date && $schedule): ?>
                <div id="slotsSection">
                    <label class="form-label">3. Choose a time slot
                        <span class="text-muted fw-normal">
                            (<?= e(weekdayName((int) date('w', strtotime($date)))) ?>,
                            <?= formatDate($date) ?>)
                        </span>
                    </label>

                    <div class="slot-grid mb-3">
                        <?php foreach ($slots as $time): ?>
                            <?php $isBooked = in_array($time, $booked, true); ?>
                            <?php $isSelected = $time === $slot && !$isBooked; ?>
                            <label class="slot-btn <?= $isBooked ? 'booked' : '' ?>
                                            <?= $isSelected ? 'selected' : '' ?>">
                                <input type="radio" name="start_time" value="<?= $time ?>"
                                    <?= $isSelected ? 'checked' : '' ?>
                                    <?= $isBooked ? 'disabled' : '' ?>>
                                <?= formatTime($time . ':00') ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <p class="selected-slot-note mb-3" id="selectedSlotNote" role="status">
                        <?php if ($slot): ?>
                            Selected: <?= formatTime($slot . ':00') ?>
                        <?php else: ?>
                            Select one of the free slots above.
                        <?php endif; ?>
                    </p>

                    <div class="mb-3">
                        <label for="reason" class="form-label">
                            Reason for visit <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea class="form-control" id="reason" name="reason"
                                  rows="3" data-label="Reason" data-rules="maxlength:1000"
                                  placeholder="e.g. Routine check-up"><?= e($reason) ?></textarea>
                    </div>

                    <button type="submit" name="book_slot" value="1"
                            class="btn-primary-custom w-100">
                        Confirm Booking →
                    </button>
                </div>

                <?php elseif ($doctor && $date && $doctorId && $date >= date('Y-m-d')): ?>
                    <div class="alert alert-info mb-0">
                        <?= e($doctor['name']) ?> does not work on
                        <?= e(weekdayName((int) date('w', strtotime($date)))) ?>s.
                        <?php if ($weekdays): ?>
                            Available days:
                            <strong>
                                <?= e(implode(', ', array_map('weekdayName', $weekdays))) ?>
                            </strong>.
                        <?php endif; ?>
                        Pick another date above.
                    </div>
                <?php elseif ($doctor && $weekdays): ?>
                    <p class="form-text mb-0">
                        <?= e($doctor['name']) ?> works on
                        <strong><?= e(implode(', ', array_map('weekdayName', $weekdays))) ?></strong>.
                        Pick a date above to see free slots.
                    </p>
                <?php endif; ?>

            </form>
        </div>

        <p class="text-center mt-4 mb-0">
            <a href="<?= $parentId ?>doctors.php">← Back to all doctors</a>
        </p>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
