<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    set_flash('error', 'Please submit the appointment form again.');
    header('Location: book-appointment.php');
    exit;
}

$serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
$dentistId = filter_input(INPUT_POST, 'dentist_id', FILTER_VALIDATE_INT);
$appointmentDate = trim($_POST['appointment_date'] ?? '');
$appointmentTime = trim($_POST['appointment_time'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$date = DateTime::createFromFormat('!Y-m-d', $appointmentDate);
$time = DateTime::createFromFormat('!H:i', $appointmentTime);

if (!$serviceId || !$dentistId || !$date || $date->format('Y-m-d') !== $appointmentDate
    || $date->format('Y-m-d') < date('Y-m-d') || !$time || $time->format('H:i') !== $appointmentTime) {
    set_flash('error', 'Choose a valid service, dentist, date, and time.');
    header('Location: book-appointment.php');
    exit;
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
    set_flash('success', 'Your appointment request was submitted.');
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