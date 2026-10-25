<?php
require_once __DIR__ . '/patient_helpers.php';
$currentPatient = require_patient($pdo);

// Automatically mark past confirmed/pending appointments as Completed
$sqlAutoComplete = "UPDATE appointment 
                    SET AppointmentStatus = 'Completed'
                    WHERE AppointmentStatus IN ('Confirmed', 'Pending')
                    AND (
                        AppointmentDate < CURDATE()
                        OR (
                            AppointmentDate = CURDATE() 
                            AND ADDTIME(AppointmentTime, SEC_TO_TIME(
                                (SELECT Duration * 3600 FROM service WHERE service.ServiceID = appointment.ServiceID)
                            )) <= CURTIME()
                        )
                    )";

$pdo->query($sqlAutoComplete);