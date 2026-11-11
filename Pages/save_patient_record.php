<?php
// save_patient_record.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/patient_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Authenticate current user session as Dentist
$currentDentist = require_patient($pdo);
$dentistID = (int) $currentDentist['DentistID'];

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

$action = $data['action'] ?? '';
$patientID = filter_var($data['patientID'] ?? null, FILTER_VALIDATE_INT);

if (!$patientID) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid Patient ID.']);
    exit;
}

try {
    switch ($action) {

        case 'create_visit':
            $rawApptID = $data['appointmentID'] ?? null;
            $appointmentID = filter_var($rawApptID, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if ($appointmentID === false || $appointmentID <= 0) {
                $appointmentID = null;
            }

            if ($appointmentID !== null) {
                $stmt = $pdo->prepare(
                    "INSERT INTO dentalrecord (PatientID, DentistID, AppointmentID, RecordDate) 
                     VALUES (:patient_id, :dentist_id, :appointment_id, CURDATE())"
                );
                $stmt->execute([
                    'patient_id' => $patientID,
                    'dentist_id' => $dentistID,
                    'appointment_id' => $appointmentID
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO dentalrecord (PatientID, DentistID, RecordDate) 
                     VALUES (:patient_id, :dentist_id, CURDATE())"
                );
                $stmt->execute([
                    'patient_id' => $patientID,
                    'dentist_id' => $dentistID
                ]);
            }

            $newRecordID = (int) $pdo->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'New visit created successfully.',
                'recordID' => $newRecordID,
                'recordDate' => date('Y-m-d')
            ]);
            break;

        case 'save_examination':
            $recordID = filter_var($data['recordID'] ?? null, FILTER_VALIDATE_INT);
            $examDate = $data['examDate'] ?? null;
            $oralHygiene = $data['oralHygiene'] ?? null;
            $gumCondition = $data['gumCondition'] ?? null;
            $examNotes = $data['examNotes'] ?? null;

            if (!$recordID) {
                echo json_encode(['success' => false, 'message' => 'Missing Dental Record ID.']);
                exit;
            }

            $stmt = $pdo->prepare(
                "UPDATE dentalrecord 
                 SET RecordDate = :exam_date, oralHygiene = :oral_hygiene, gumCondition = :gum_condition, ClinicalNotes = :exam_notes 
                 WHERE RecordID = :record_id AND PatientID = :patient_id"
            );
            $stmt->execute([
                'exam_date' => $examDate,
                'oral_hygiene' => $oralHygiene,
                'gum_condition' => $gumCondition,
                'exam_notes' => $examNotes,
                'record_id' => $recordID,
                'patient_id' => $patientID
            ]);

            echo json_encode(['success' => true, 'message' => 'Examination findings saved successfully.']);
            break;

        case 'save_tooth_chart':
            $recordID = filter_var($data['recordID'] ?? null, FILTER_VALIDATE_INT);
            $teethData = $data['teeth'] ?? [];

            if (!$recordID) {
                echo json_encode(['success' => false, 'message' => 'Missing Dental Record ID.']);
                exit;
            }

            $pdo->beginTransaction();

            $toothStmt = $pdo->prepare("SELECT ToothID FROM tooth WHERE ToothNumber = :tooth_number");
            $upsertStmt = $pdo->prepare(
                "INSERT INTO toothrecord (RecordID, ToothID, ConditionDescription, Notes) 
                 VALUES (:record_id, :tooth_id, :condition, :notes) 
                 ON DUPLICATE KEY UPDATE ConditionDescription = VALUES(ConditionDescription), Notes = VALUES(Notes)"
            );

            foreach ($teethData as $item) {
                $toothNumber = (string)$item['number'];
                
                // Format standard options or keep custom text intact
                $condition = trim($item['condition']);
                if (in_array(strtolower($condition), ['healthy', 'cavity', 'treated'])) {
                    $condition = ucfirst(strtolower($condition));
                }

                $notes = $item['notes'] ?? null;

                $toothStmt->execute(['tooth_number' => $toothNumber]);
                $row = $toothStmt->fetch();

                if ($row) {
                    $toothID = $row['ToothID'];
                    $upsertStmt->execute([
                        'record_id' => $recordID,
                        'tooth_id' => $toothID,
                        'condition' => $condition,
                        'notes' => $notes
                    ]);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Tooth chart saved successfully.']);
            break;

        case 'save_treatment':
            $recordID = filter_var($data['recordID'] ?? null, FILTER_VALIDATE_INT);
            $treatmentID = filter_var($data['treatmentID'] ?? null, FILTER_VALIDATE_INT);
            $treatmentDate = $data['treatmentDate'] ?? date('Y-m-d');
            $diagnosis = $data['diagnosis'] ?? null;
            $treatmentName = $data['treatmentName'] ?? null;
            $treatmentDetails = $data['treatmentDetails'] ?? null;

            if (!$recordID) {
                echo json_encode(['success' => false, 'message' => 'Missing Dental Record ID.']);
                exit;
            }

            $pdo->beginTransaction();

            // 1. Always update Diagnosis on the parent dental record
            $diagStmt = $pdo->prepare("UPDATE dentalrecord SET Diagnosis = :diagnosis WHERE RecordID = :record_id");
            $diagStmt->execute([
                'diagnosis' => $diagnosis,
                'record_id' => $recordID
            ]);

            // 2. If treatmentID wasn't passed, check if a treatment already exists for this RecordID
            if (!$treatmentID) {
                $checkTreat = $pdo->prepare(
                    "SELECT TreatmentID FROM treatment WHERE RecordID = :record_id ORDER BY TreatmentID DESC LIMIT 1"
                );
                $checkTreat->execute(['record_id' => $recordID]);
                $existingTreat = $checkTreat->fetch();

                if ($existingTreat) {
                    $treatmentID = (int) $existingTreat['TreatmentID'];
                }
            }

            if ($treatmentID) {
                // UPDATE existing treatment record
                $treatStmt = $pdo->prepare(
                    "UPDATE treatment 
                     SET TreatmentName = :treatment_name,
                         TreatmentDetails = :treatment_details,
                         TreatmentDate = :treatment_date
                     WHERE TreatmentID = :treatment_id AND RecordID = :record_id"
                );
                $treatStmt->execute([
                    'treatment_name' => $treatmentName,
                    'treatment_details' => $treatmentDetails,
                    'treatment_date' => $treatmentDate,
                    'treatment_id' => $treatmentID,
                    'record_id' => $recordID
                ]);

                $message = 'Diagnosis and treatment updated successfully.';
            } else {
                // INSERT new treatment record if none exists for this visit
                $treatStmt = $pdo->prepare(
                    "INSERT INTO treatment (RecordID, TreatmentName, TreatmentDetails, TreatmentDate) 
                     VALUES (:record_id, :treatment_name, :treatment_details, :treatment_date)"
                );
                $treatStmt->execute([
                    'record_id' => $recordID,
                    'treatment_name' => $treatmentName,
                    'treatment_details' => $treatmentDetails,
                    'treatment_date' => $treatmentDate
                ]);

                $treatmentID = (int) $pdo->lastInsertId();
                $message = 'Diagnosis and treatment saved successfully.';
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => $message,
                'treatmentID' => $treatmentID
            ]);
            break;

        case 'save_prescription':
            $recordID = filter_var($data['recordID'] ?? null, FILTER_VALIDATE_INT);
            $medicationName = trim($data['medicationName'] ?? '');
            $dosage = trim($data['dosage'] ?? '');
            $instructions = trim($data['instructions'] ?? '');

            if (!$recordID || $recordID <= 0) {
                echo json_encode(['success' => false, 'message' => 'Missing or invalid Dental Record ID.']);
                break;
            }

            if ($medicationName === '') {
                echo json_encode(['success' => false, 'message' => 'Please enter a medication name.']);
                break;
            }

            // Check that the dental record belongs to this patient
            $checkRecord = $pdo->prepare(
                "SELECT RecordID FROM dentalrecord WHERE RecordID = :record_id AND PatientID = :patient_id"
            );
            $checkRecord->execute([
                'record_id' => $recordID,
                'patient_id' => $patientID
            ]);
            $recordExists = $checkRecord->fetch();

            if (!$recordExists) {
                echo json_encode(['success' => false, 'message' => 'Dental record not found for this patient.']);
                break;
            }

            // Find the most recently created prescription for this visit
            $findPrescription = $pdo->prepare(
                "SELECT PrescriptionID FROM prescription WHERE RecordID = :record_id ORDER BY PrescriptionID DESC LIMIT 1"
            );
            $findPrescription->execute(['record_id' => $recordID]);
            $existingPrescription = $findPrescription->fetch();

            if ($existingPrescription) {
                $prescriptionID = (int) $existingPrescription['PrescriptionID'];

                $stmt = $pdo->prepare(
                    "UPDATE prescription
                     SET MedicationName = :medication_name,
                         Dosage = :dosage,
                         Instructions = :instructions
                     WHERE PrescriptionID = :prescription_id AND RecordID = :record_id"
                );
                $stmt->execute([
                    'medication_name' => $medicationName,
                    'dosage' => $dosage,
                    'instructions' => $instructions,
                    'prescription_id' => $prescriptionID,
                    'record_id' => $recordID
                ]);

                $successMessage = 'Prescription updated successfully.';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO prescription (RecordID, MedicationName, Dosage, Instructions)
                     VALUES (:record_id, :medication_name, :dosage, :instructions)"
                );
                $stmt->execute([
                    'record_id' => $recordID,
                    'medication_name' => $medicationName,
                    'dosage' => $dosage,
                    'instructions' => $instructions
                ]);

                $prescriptionID = (int) $pdo->lastInsertId();
                $successMessage = 'Prescription saved successfully.';
            }

            echo json_encode([
                'success' => true,
                'message' => $successMessage,
                'prescriptionID' => $prescriptionID,
                'recordID' => (int) $recordID,
                'medicationName' => $medicationName,
                'dosage' => $dosage,
                'instructions' => $instructions
            ]);
            break;

        case 'save_orthodontics':
            $orthoRecordID = filter_var($data['orthoRecordID'] ?? null, FILTER_VALIDATE_INT);
            $treatmentType = trim($data['treatmentType'] ?? '');
            $startDate = !empty($data['startDate']) ? $data['startDate'] : null;
            $currentStage = trim($data['currentStage'] ?? '');
            $nextAdjustment = !empty($data['nextAdjustment']) ? $data['nextAdjustment'] : null;
            $progressNotes = trim($data['progressNotes'] ?? '');

            // If orthoRecordID wasn't passed, check if the patient already has an existing orthodontic record
            if (!$orthoRecordID) {
                $checkOrtho = $pdo->prepare(
                    "SELECT OrthoRecordID 
                     FROM orthodonticrecord 
                     WHERE PatientID = :patient_id 
                     ORDER BY OrthoRecordID DESC 
                     LIMIT 1"
                );
                $checkOrtho->execute(['patient_id' => $patientID]);
                $existingOrtho = $checkOrtho->fetch();

                if ($existingOrtho) {
                    $orthoRecordID = (int) $existingOrtho['OrthoRecordID'];
                }
            }

            if ($orthoRecordID) {
                // UPDATE existing orthodontic record
                $stmt = $pdo->prepare(
                    "UPDATE orthodonticrecord
                     SET TreatmentType = :treatment_type,
                         StartDate = :start_date,
                         CurrentStage = :current_stage,
                         ProgressNotes = :progress_notes,
                         NextAdjustmentDate = :next_adjustment
                     WHERE OrthoRecordID = :ortho_record_id AND PatientID = :patient_id"
                );
                $stmt->execute([
                    'treatment_type' => $treatmentType,
                    'start_date' => $startDate,
                    'current_stage' => $currentStage,
                    'progress_notes' => $progressNotes,
                    'next_adjustment' => $nextAdjustment,
                    'ortho_record_id' => $orthoRecordID,
                    'patient_id' => $patientID
                ]);

                $message = 'Orthodontic record updated successfully.';
            } else {
                // INSERT new orthodontic record if no previous record exists
                $stmt = $pdo->prepare(
                    "INSERT INTO orthodonticrecord (PatientID, DentistID, TreatmentType, StartDate, CurrentStage, ProgressNotes, NextAdjustmentDate) 
                     VALUES (:patient_id, :dentist_id, :treatment_type, :start_date, :current_stage, :progress_notes, :next_adjustment)"
                );
                $stmt->execute([
                    'patient_id' => $patientID,
                    'dentist_id' => $dentistID,
                    'treatment_type' => $treatmentType,
                    'start_date' => $startDate,
                    'current_stage' => $currentStage,
                    'progress_notes' => $progressNotes,
                    'next_adjustment' => $nextAdjustment
                ]);

                $orthoRecordID = (int) $pdo->lastInsertId();
                $message = 'Orthodontic record created successfully.';
            }

            echo json_encode([
                'success' => true, 
                'message' => $message,
                'orthoRecordID' => $orthoRecordID
            ]);
            break;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}