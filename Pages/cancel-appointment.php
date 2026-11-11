<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

$appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token() || !$appointmentId) {
    set_flash('error', 'The appointment could not be cancelled. Please try again.');
    header('Location: appointments.php');
    exit;
}

$statement = $pdo->prepare(
    "UPDATE appointment SET AppointmentStatus = 'Cancelled'
     WHERE AppointmentID = :appointment_id AND PatientID = :patient_id AND AppointmentStatus = 'Confirmed'"
);
$statement->execute([
    'appointment_id' => $appointmentId,
    'patient_id' => $currentPatient['PatientID'],
]);

set_flash($statement->rowCount() === 1 ? 'success' : 'error', $statement->rowCount() === 1
    ? 'Your confirmed appointment was cancelled.'
    : 'Only confirmed appointments can be cancelled.');
header('Location: appointments.php');
exit;