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
        'INSERT INTO appointment (PatientID, DentistID, ServiceID, AppointmentDate, AppointmentTime, AppointmentStatus, AppointmentNotes)
         VALUES (:patient_id, :dentist_id, :service_id, :appointment_date, :appointment_time, "Confirmed", :notes)'
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

    $today = new DateTimeImmutable('today');
    $oneWeekLater = $today->modify('+7 days');

    $appointment = new DateTimeImmutable($appointmentDate);
    $daysLeft = $today->diff($appointment)->days;

    if ($appointment >= $today && $appointment <= $oneWeekLater) {
        require_once __DIR__ . '/../email_helper.php';

        $subject = "Upcoming Appointment - BrightSmile Dental";

        $body = "
            <div style='
                font-family: Arial, sans-serif;
                background-color: #f6f9fa;
                padding: 30px;
                color: #333333;
            '>

                <div style='
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    border: 1px solid #e5e7eb;
                '>

                    <div style='
                        background-color: #42B1CC;
                        padding: 25px;
                        text-align: center;
                        color: white;
                    '>
                        <h2 style='margin: 0;'>
                            BrightSmile Dental
                        </h2>
                        <p style='margin: 8px 0 0;'>
                            Appointment Notification
                        </p>
                    </div>

                    <div style='padding: 30px;'>

                        <h2 style='color: #333333;'>
                            Upcoming Dental Appointment
                        </h2>

                        <p>
                            Dear " . htmlspecialchars($currentPatient['FullName']) . ",
                        </p>

                        <p style='line-height: 1.6; color: #555555;'>
                            This is a reminder that your dental appointment
                            is scheduled in the next ". $daysLeft ." days.
                        </p>

                        <div style='
                            background-color: #f0f9fb;
                            padding: 20px;
                            border-radius: 8px;
                            margin: 20px 0;
                        '>
                            <h3 style='color: #26869D; margin-top: 0;'>
                                Appointment Details
                            </h3>

                            <p>
                                Appointment Date:
                                <strong>" . htmlspecialchars(date('d M Y', strtotime($appointmentDate))) . "</strong>
                            </p>

                            <p style='margin-bottom: 0;'>
                                Appointment Time:
                                <strong>" . htmlspecialchars(date('g:i A', strtotime($appointmentTime))) . "</strong>
                            </p>
                        </div>

                        <p style='line-height: 1.6; color: #555555;'>
                            Please remember to arrive 10-15 minutes early
                            for your appointment.
                        </p>

                        <p style='line-height: 1.6; color: #555555;'>
                            If you have any questions or need to make changes
                            to your appointment, please contact BrightSmile Dental.
                        </p>

                        <p style='margin-top: 30px;'>
                            Regards,<br>
                            <strong>BrightSmile Dental</strong>
                        </p>

                    </div>

                    <div style='
                        background-color: #f6f9fa;
                        padding: 15px;
                        text-align: center;
                        font-size: 12px;
                        color: #888888;
                    '>
                        &copy; " . date('Y') . " BrightSmile Dental.
                        All rights reserved.
                    </div>

                </div>
            </div>
        ";

        sendEmail(
            $currentPatient['Email'],
            $subject,
            $body
        );
    }

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