<?php
/**
 * Small reusable functions shared by every MediBook page.
 * Every database function accepts ?PDO $db and returns [] when the
 * database is unavailable, so public pages still render.
 */

const APPOINTMENT_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

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

    $statement = $db->prepare($sql);
    $statement->execute();

    return $statement->fetchAll();
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

/**
 * The doctors-table row belonging to a logged-in doctor user.
 * Used by every doctor page to scope queries to the correct doctor.
 */
function getDoctorByUserId(?PDO $db, int $userId): ?array
{
    if (!$db) {
        return null;
    }

    $sql = 'SELECT d.id, d.user_id, d.department_id, d.specialization,
                   d.qualification, d.experience_years, d.consultation_fee,
                   d.bio, d.is_active,
                   u.name, u.email, u.phone,
                   dep.name AS department_name
            FROM doctors d
            JOIN users u ON u.id = d.user_id
            JOIN departments dep ON dep.id = d.department_id
            WHERE d.user_id = :user
            LIMIT 1';

    $statement = $db->prepare($sql);
    $statement->execute([':user' => $userId]);
    $doctor = $statement->fetch();

    return $doctor ?: null;
}

/** Appointments for one doctor on one exact date, earliest first. */
function getDoctorAppointmentsForDate(?PDO $db, int $doctorId, string $date): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT a.id, a.appointment_date, a.start_time, a.end_time,
                   a.reason, a.status,
                   p.name AS patient_name, p.email AS patient_email,
                   p.phone AS patient_phone
            FROM appointments a
            JOIN users p ON p.id = a.patient_id
            WHERE a.doctor_id = :doctor AND a.appointment_date = :date
            ORDER BY a.start_time';

    $statement = $db->prepare($sql);
    $statement->execute([':doctor' => $doctorId, ':date' => $date]);

    return $statement->fetchAll();
}

/** Active appointments after today for one doctor. */
function getDoctorUpcomingAppointments(?PDO $db, int $doctorId, int $limit = 8): array
{
    if (!$db) {
        return [];
    }

    $limit = max(1, min(50, $limit));
    $sql = 'SELECT a.id, a.appointment_date, a.start_time, a.end_time,
                   a.reason, a.status,
                   p.name AS patient_name, p.email AS patient_email,
                   p.phone AS patient_phone
            FROM appointments a
            JOIN users p ON p.id = a.patient_id
            WHERE a.doctor_id = :doctor
              AND a.appointment_date > :today
              AND a.status IN (\'pending\', \'confirmed\')
            ORDER BY a.appointment_date, a.start_time
            LIMIT ' . $limit;

    $statement = $db->prepare($sql);
    $statement->execute([':doctor' => $doctorId, ':today' => date('Y-m-d')]);

    return $statement->fetchAll();
}

/** All appointments belonging to one doctor, optionally filtered by status. */
function getDoctorAppointments(?PDO $db, int $doctorId, string $status = ''): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT a.id, a.appointment_date, a.start_time, a.end_time,
                   a.reason, a.status, a.created_at,
                   p.id AS patient_id, p.name AS patient_name,
                   p.email AS patient_email, p.phone AS patient_phone
            FROM appointments a
            JOIN users p ON p.id = a.patient_id
            WHERE a.doctor_id = :doctor';

    $params = [':doctor' => $doctorId];

    if (in_array($status, APPOINTMENT_STATUSES, true)) {
        $sql .= ' AND a.status = :status';
        $params[':status'] = $status;
    }

    $sql .= ' ORDER BY a.appointment_date DESC, a.start_time DESC';

    $statement = $db->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/** Small summary used on the doctor dashboard. */
function getDoctorAppointmentStats(?PDO $db, int $doctorId): array
{
    if (!$db) {
        return ['today' => 0, 'pending' => 0, 'upcoming' => 0, 'patients' => 0];
    }

    $sql = 'SELECT
                COALESCE(SUM(a.appointment_date = :today_date), 0) AS today_count,
                COALESCE(SUM(a.status = \'pending\'), 0) AS pending_count,
                COALESCE(SUM(a.appointment_date >= :upcoming_date
                             AND a.status IN (\'pending\', \'confirmed\')), 0) AS upcoming_count,
                COUNT(DISTINCT a.patient_id) AS patient_count
            FROM appointments a
            WHERE a.doctor_id = :doctor';

    $statement = $db->prepare($sql);
    $statement->execute([
        ':today_date'    => date('Y-m-d'),
        ':upcoming_date' => date('Y-m-d'),
        ':doctor'        => $doctorId,
    ]);
    $row = $statement->fetch() ?: [];

    return [
        'today'    => (int) ($row['today_count'] ?? 0),
        'pending'  => (int) ($row['pending_count'] ?? 0),
        'upcoming' => (int) ($row['upcoming_count'] ?? 0),
        'patients' => (int) ($row['patient_count'] ?? 0),
    ];
}

/**
 * Appointment list joined with both patient and doctor details.
 * Used by the admin appointment manager and dashboard.
 */
function getAllAppointments(
    ?PDO $db,
    string $status = '',
    string $date = '',
    int $patientId = 0,
    int $limit = 0
): array {
    if (!$db) {
        return [];
    }

    $sql = 'SELECT a.id, a.appointment_date, a.start_time, a.end_time,
                   a.reason, a.status, a.created_at,
                   p.id AS patient_id, p.name AS patient_name,
                   p.email AS patient_email, p.phone AS patient_phone,
                   d.id AS doctor_id, du.name AS doctor_name,
                   dep.name AS department_name
            FROM appointments a
            JOIN users p ON p.id = a.patient_id
            JOIN doctors d ON d.id = a.doctor_id
            JOIN users du ON du.id = d.user_id
            JOIN departments dep ON dep.id = d.department_id
            WHERE 1 = 1';

    $params = [];

    if (in_array($status, APPOINTMENT_STATUSES, true)) {
        $sql .= ' AND a.status = :status';
        $params[':status'] = $status;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $sql .= ' AND a.appointment_date = :appointment_date';
        $params[':appointment_date'] = $date;
    }

    if ($patientId > 0) {
        $sql .= ' AND a.patient_id = :patient';
        $params[':patient'] = $patientId;
    }

    $sql .= ' ORDER BY a.appointment_date DESC, a.start_time DESC';

    if ($limit > 0) {
        // Cast to int before putting LIMIT into SQL (it cannot be bound).
        $sql .= ' LIMIT ' . max(1, min(100, $limit));
    }

    $statement = $db->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/** Summary counts for the admin dashboard. */
function getAdminSummary(?PDO $db): array
{
    $defaults = [
        'patients' => 0, 'doctors' => 0, 'departments' => 0,
        'appointments' => 0, 'today' => 0, 'pending' => 0, 'upcoming' => 0,
    ];

    if (!$db) {
        return $defaults;
    }

    $sql = 'SELECT
                (SELECT COUNT(*) FROM users
                 WHERE role = \'patient\' AND is_active = 1) AS patient_count,
                (SELECT COUNT(*) FROM doctors
                 WHERE is_active = 1) AS doctor_count,
                (SELECT COUNT(*) FROM departments
                 WHERE is_active = 1) AS department_count,
                (SELECT COUNT(*) FROM appointments) AS appointment_count,
                (SELECT COUNT(*) FROM appointments
                 WHERE appointment_date = :today_date
                   AND status IN (\'pending\', \'confirmed\')) AS today_count,
                (SELECT COUNT(*) FROM appointments
                 WHERE status = \'pending\') AS pending_count,
                (SELECT COUNT(*) FROM appointments
                 WHERE appointment_date >= :upcoming_date
                   AND status IN (\'pending\', \'confirmed\')) AS upcoming_count';

    $statement = $db->prepare($sql);
    $statement->execute([
        ':today_date'    => date('Y-m-d'),
        ':upcoming_date' => date('Y-m-d'),
    ]);
    $row = $statement->fetch() ?: [];

    return [
        'patients'     => (int) ($row['patient_count'] ?? 0),
        'doctors'      => (int) ($row['doctor_count'] ?? 0),
        'departments'  => (int) ($row['department_count'] ?? 0),
        'appointments' => (int) ($row['appointment_count'] ?? 0),
        'today'        => (int) ($row['today_count'] ?? 0),
        'pending'      => (int) ($row['pending_count'] ?? 0),
        'upcoming'     => (int) ($row['upcoming_count'] ?? 0),
    ];
}

/** Patient list with appointment totals for the admin area. */
function getPatients(?PDO $db, string $search = ''): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT u.id, u.name, u.email, u.phone, u.is_active, u.created_at,
                   COUNT(a.id) AS appointment_count,
                   MAX(a.appointment_date) AS last_appointment
            FROM users u
            LEFT JOIN appointments a ON a.patient_id = u.id
            WHERE u.role = \'patient\'';

    $params = [];

    if ($search !== '') {
        $sql .= ' AND (u.name LIKE :name OR u.email LIKE :email)';
        $params[':name']  = '%' . $search . '%';
        $params[':email'] = '%' . $search . '%';
    }

    $sql .= ' GROUP BY u.id, u.name, u.email, u.phone, u.is_active, u.created_at
              ORDER BY u.name';

    $statement = $db->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/** Every doctor, including inactive ones, for admin management. */
function getAllDoctorsAdmin(?PDO $db): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT d.id, d.user_id, d.department_id, d.specialization,
                   d.qualification, d.experience_years, d.consultation_fee,
                   d.bio, d.is_active, d.created_at,
                   u.name, u.email, u.phone, u.is_active AS user_active,
                   dep.name AS department_name
            FROM doctors d
            JOIN users u ON u.id = d.user_id
            JOIN departments dep ON dep.id = d.department_id
            ORDER BY u.name';

    $statement = $db->prepare($sql);
    $statement->execute();

    return $statement->fetchAll();
}

/** Every department, including inactive ones, for admin management. */
function getAllDepartmentsAdmin(?PDO $db): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT d.id, d.name, d.description, d.is_active, d.created_at,
                   COUNT(doc.id) AS doctor_count
            FROM departments d
            LEFT JOIN doctors doc ON doc.department_id = d.id
            GROUP BY d.id, d.name, d.description, d.is_active, d.created_at
            ORDER BY d.name';

    $statement = $db->prepare($sql);
    $statement->execute();

    return $statement->fetchAll();
}

/** All working schedules with doctor and department names. */
function getAllSchedulesAdmin(?PDO $db): array
{
    if (!$db) {
        return [];
    }

    $sql = 'SELECT s.id, s.doctor_id, s.day_of_week, s.start_time, s.end_time,
                   s.slot_duration_minutes, s.is_available,
                   u.name AS doctor_name,
                   dep.name AS department_name
            FROM schedules s
            JOIN doctors d ON d.id = s.doctor_id
            JOIN users u ON u.id = d.user_id
            JOIN departments dep ON dep.id = d.department_id
            ORDER BY u.name, s.day_of_week, s.start_time';

    $statement = $db->prepare($sql);
    $statement->execute();

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
