<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    set_flash('error', 'Please submit the appointment form again.');
    header('Location: book-appointment.php');
    exit;
}

$serviceId = filter_input(INPUT_POST, 'service', FILTER_VALIDATE_INT);
$dentistId = filter_input(INPUT_POST, 'dentist', FILTER_VALIDATE_INT);
$appointmentDate = trim($_POST['appointment_date'] ?? '');
$appointmentTime = trim($_POST['appointment_time'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$date = DateTime::createFromFormat('!Y-m-d', $appointmentDate);
$time = DateTime::createFromFormat('!H:i', $appointmentTime);
$minimumAppointmentDate = (new DateTimeImmutable('today'))->modify('+2 days')->format('Y-m-d');
$availableTimes = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];

if (!$serviceId || !$dentistId || !$date || $date->format('Y-m-d') !== $appointmentDate
    || !$time || $time->format('H:i') !== $appointmentTime
    || !in_array($appointmentTime, $availableTimes, true)) {
    set_flash('error', 'Choose a valid service, dentist, date, and time.');
    header('Location: book-appointment.php');
    exit;
}

if ($appointmentDate < $minimumAppointmentDate) {
    set_flash('error', 'Appointments must be booked at least two days in advance.');
    header('Location: book-appointment.php');
    exit;
}

$serviceStatement = $pdo->prepare('SELECT ServiceID, Duration, Category FROM service WHERE ServiceID = :service_id');
$serviceStatement->execute(['service_id' => $serviceId]);
$serviceRow = $serviceStatement->fetch(PDO::FETCH_ASSOC);

if (!$serviceRow) {
    set_flash('error', 'Choose a valid service, dentist, date, and time.');
    header('Location: book-appointment.php');
    exit;
}

$assignmentStatement = $pdo->prepare(
    "SELECT 1
     FROM service s
     INNER JOIN dentist d ON d.DentistID = :dentist_id
     INNER JOIN user u ON u.UserID = d.UserID AND u.Role = 'Dentist'
     LEFT JOIN department dep ON dep.DeptID = d.DeptID
     WHERE s.ServiceID = :service_id
         AND (
             (LOWER(s.Category) = 'general'
                 AND (LOWER(dep.DepartmentName) = 'general' OR LOWER(d.Specialty) = 'general'))
             OR (LOWER(s.Category) LIKE 'ortho%'
                 AND (LOWER(dep.DepartmentName) LIKE 'ortho%' OR LOWER(d.Specialty) LIKE 'ortho%'))
         )"
);
$assignmentStatement->execute(['dentist_id' => $dentistId, 'service_id' => $serviceId]);

if (!$assignmentStatement->fetchColumn()) {
    set_flash('error', 'Choose a valid service, dentist, date, and time.');
    header('Location: book-appointment.php');
    exit;
}

$bookingStart = strtotime($appointmentDate . ' ' . $appointmentTime);
$serviceDurationMinutes = max(60, (int) round((float) ($serviceRow['Duration'] ?? 1) * 60));
$bookingEnd = $bookingStart + $serviceDurationMinutes * 60;

$overlapStatement = $pdo->prepare(
    "SELECT a.AppointmentID, a.AppointmentDate, a.AppointmentTime,
            s.Duration AS ServiceDurationHours
     FROM appointment a
     INNER JOIN service s ON s.ServiceID = a.ServiceID
     WHERE a.DentistID = :dentist_id
       AND a.AppointmentDate = :appointment_date
       AND a.AppointmentStatus NOT IN ('Cancelled', 'Rejected')
       AND a.AppointmentID IS NOT NULL"
);
$overlapStatement->execute(['dentist_id' => $dentistId, 'appointment_date' => $appointmentDate]);

while ($existing = $overlapStatement->fetch(PDO::FETCH_ASSOC)) {
    $existingStart = strtotime($appointmentDate . ' ' . $existing['AppointmentTime']);
    $existingDurationMinutes = max(60, (int) round((float) ($existing['ServiceDurationHours'] ?? 1) * 60));
    $existingEnd = $existingStart + $existingDurationMinutes * 60;

    if ($bookingStart < $existingEnd && $existingStart < $bookingEnd) {
        set_flash('error', 'The selected time slot is no longer available. Please choose another time.');
        header('Location: book-appointment.php');
        exit;
    }
}

try {
    $statement = $pdo->prepare(
        'INSERT INTO appointment (PatientID, DentistID, ServiceID, AppointmentDate, AppointmentTime, AppointmentNotes)
         VALUES (:patient_id, :dentist_id, :service_id, :appointment_date, :appointment_time, :notes)'
    );
    $statement->execute([
        'patient_id' => $currentPatient['PatientID'],
        'dentist_id' => $dentistId,
        'service_id' => $serviceId,
        'appointment_date' => $appointmentDate,
        'appointment_time' => $appointmentTime,
        'notes' => $notes !== '' ? $notes : null,
    ]);
    set_flash(
        'appointment_success',
        'Your appointment request has been submitted successfully! Please wait for the administrator to review and approve your booking.'
    );
    header('Location: appointments.php');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000' && (int) ($exception->errorInfo[1] ?? 0) === 1062) {
        set_flash('error', 'That dentist already has an appointment at this date and time. Please choose another time.');
    } else {
        set_flash('error', 'The appointment could not be booked. Please try again.');
    }
    header('Location: book-appointment.php');
}
exit;