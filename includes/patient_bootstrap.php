<?php
require_once __DIR__ . '/patient_helpers.php';
$currentPatient = require_patient($pdo);

// Automatically mark past confirmed appointments as Completed
$sqlAutoComplete = "UPDATE appointment 
                    SET AppointmentStatus = 'Completed'
                    WHERE AppointmentStatus = 'Confirmed'
                    AND (
                        AppointmentDate < CURDATE()
                        OR (
                            AppointmentDate = CURDATE() 
                            AND ADDTIME(AppointmentTime, SEC_TO_TIME(
                                (SELECT Duration * 60 FROM service WHERE service.ServiceID = appointment.ServiceID)
                            )) <= CURTIME()
                        )
                    )";

$autoCompleteStatement = $pdo->query($sqlAutoComplete);
if ($autoCompleteStatement->rowCount() > 0) {
    create_missing_completed_appointment_records($pdo);
}