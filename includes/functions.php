<?php
/**
 * Small reusable functions shared by every MediBook page.
 * Every database function accepts ?PDO $db and returns [] when the
 * database is unavailable, so public pages still render.
 */

/** Escape text before printing it into HTML (prevents XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Store a one-time message for the next page load (success / danger / info). */
function flashSet(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Read and clear the one-time message. */
function flashGet(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** Friendly message shown when MySQL is not running / not imported. */
function dbUnavailableNotice(): string
{
    return '<div class="alert alert-warning">'
        . '<strong>Database unavailable.</strong> Start MySQL in XAMPP and import '
        . '<code>database/schema.sql</code> (and <code>database/seed.sql</code>) '
        . 'in phpMyAdmin, then reload this page.'
        . '</div>';
}

/* ---------------------------------------------------------------
 * Database reads
 * ------------------------------------------------------------- */

/** All active departments, ordered by name. */
function getDepartments(?PDO $db): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT id, name, description
            FROM departments
            WHERE is_active = 1
            ORDER BY name';

    return $db->query($sql)->fetchAll();
}

/**
 * Doctors (joined with users + departments), optionally filtered by
 * department id and/or doctor name. Used by doctors.php and the
 * homepage search box.
 */
function getDoctors(?PDO $db, int $departmentId = 0, string $name = ''): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT d.id, d.specialization, d.qualification,
                   d.experience_years, d.consultation_fee, d.bio,
                   u.name, u.phone,
                   dep.id AS department_id, dep.name AS department_name
            FROM doctors d
            JOIN users u ON u.id = d.user_id
            JOIN departments dep ON dep.id = d.department_id
            WHERE d.is_active = 1 AND u.is_active = 1';

    $params = [];

    if ($departmentId > 0) {
        $sql .= ' AND d.department_id = :department';
        $params[':department'] = $departmentId;
    }

    if ($name !== '') {
        $sql .= ' AND u.name LIKE :name';
        $params[':name'] = '%' . $name . '%';
    }

    $sql .= ' ORDER BY u.name';

    $statement = $db->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/** One doctor by id, or null when not found. */
function getDoctor(?PDO $db, int $doctorId): ?array
{
    $doctors = getDoctors($db, 0, '');
    foreach ($doctors as $doctor) {
        if ((int) $doctor['id'] === $doctorId) {
            return $doctor;
        }
    }

    return null;
}

/**
 * The schedule row of a doctor on one weekday (for booking a specific day),
 * or null when the doctor does not work that day.
 * day_of_week: 0 = Sunday ... 6 = Saturday (same as PHP date('w')).
 */
function getScheduleForDay(?PDO $db, int $doctorId, int $dayOfWeek): ?array
{
    if (!$db) {
        return null;
    }

    $sql = 'SELECT start_time, end_time, slot_duration_minutes
            FROM schedules
            WHERE doctor_id = :doctor AND day_of_week = :day AND is_available = 1
            LIMIT 1';

    $statement = $db->prepare($sql);
    $statement->execute([':doctor' => $doctorId, ':day' => $dayOfWeek]);

    $schedule = $statement->fetch();

    return $schedule ?: null;
}

/** Which weekdays (numbers) a doctor works, e.g. [2, 4] = Tuesday, Thursday. */
function getAvailableWeekdays(?PDO $db, int $doctorId): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT day_of_week
            FROM schedules
            WHERE doctor_id = :doctor AND is_available = 1
            ORDER BY day_of_week';

    $statement = $db->prepare($sql);
    $statement->execute([':doctor' => $doctorId]);

    return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

/** Start times already booked for a doctor on a date (pending + confirmed). */
function getBookedStartTimes(?PDO $db, int $doctorId, string $date): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT start_time
            FROM appointments
            WHERE doctor_id = :doctor
              AND appointment_date = :date
              AND status IN (\'pending\', \'confirmed\')';

    $statement = $db->prepare($sql);
    $statement->execute([':doctor' => $doctorId, ':date' => $date]);

    $booked = [];
    foreach ($statement->fetchAll() as $row) {
        // '09:00:00' -> '09:00' so it matches the generated slot format
        $booked[] = substr($row['start_time'], 0, 5);
    }

    return $booked;
}

/** All appointments of one patient, newest date first. */
function getPatientAppointments(?PDO $db, int $patientId): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT a.id, a.appointment_date, a.start_time, a.end_time,
                   a.reason, a.status,
                   u.name AS doctor_name, dep.name AS department_name
            FROM appointments a
            JOIN doctors d ON d.id = a.doctor_id
            JOIN users u ON u.id = d.user_id
            JOIN departments dep ON dep.id = d.department_id
            WHERE a.patient_id = :patient
            ORDER BY a.appointment_date DESC, a.start_time DESC';

    $statement = $db->prepare($sql);
    $statement->execute([':patient' => $patientId]);

    return $statement->fetchAll();
}

/* ---------------------------------------------------------------
 * Pure helper functions (no database) - easy to explain and test
 * ------------------------------------------------------------- */

/**
 * Build the list of slot start times between two clock times.
 * generateSlots('09:00:00', '12:00:00', 30)
 *   => ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30']
 */
function generateSlots(string $startTime, string $endTime, int $minutes): array
{
    $slots = [];
    $from  = DateTime::createFromFormat('H:i:s', $startTime);
    $until = DateTime::createFromFormat('H:i:s', $endTime);

    if (!$from || !$until) {
        return [];
    }

    for ($time = clone $from; $time < $until; $time->modify('+' . $minutes . ' minutes')) {
        $slots[] = $time->format('H:i');
    }

    return $slots;
}

/** True when a chosen slot exists in the schedule and is not booked yet. */
function slotIsAvailable(array $slots, array $bookedSlots, string $chosen): bool
{
    return in_array($chosen, $slots, true)
        && !in_array($chosen, $bookedSlots, true);
}

/** 'HH:MM' start time + duration => 'HH:MM:SS' end time for the database. */
function slotEndTime(string $startTime, int $minutes): string
{
    $time = DateTime::createFromFormat('H:i', $startTime);
    if (!$time) {
        return $startTime . ':00';
    }

    return $time->modify('+' . $minutes . ' minutes')->format('H:i:s');
}

/** Weekday number (0 = Sunday) => full name. */
function weekdayName(int $day): string
{
    $names = [
        0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
    ];

    return $names[$day] ?? '';
}

/** Short month name + day, e.g. '2026-10-20' => '20 Oct 2026'. */
function formatDate(?string $date): string
{
    if (!$date) {
        return '';
    }

    $parsed = DateTime::createFromFormat('Y-m-d', $date);

    return $parsed ? $parsed->format('d M Y') : $date;
}

/** '09:00:00' => '9:00 AM' for display. */
function formatTime(?string $time): string
{
    if (!$time) {
        return '';
    }

    $parsed = DateTime::createFromFormat('H:i:s', $time);

    return $parsed ? $parsed->format('g:i A') : $time;
}

/** Bootstrap badge class for an appointment status. */
function statusBadge(string $status): string
{
    $badges = [
        'pending'   => 'bg-warning text-dark',
        'confirmed' => 'bg-success',
        'completed' => 'bg-secondary',
        'cancelled' => 'bg-danger',
    ];

    return $badges[$status] ?? 'bg-secondary';
}

/** First letters of a name, for the avatar circle on doctor cards. */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $first = substr($parts[0] ?? 'M', 0, 1);
    $last  = isset($parts[1]) ? substr($parts[1], 0, 1) : '';

    return strtoupper($first . $last);
}
