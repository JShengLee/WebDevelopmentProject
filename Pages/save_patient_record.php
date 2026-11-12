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

function is_fresh_dental_record(int $recordID, int $patientID, int $dentistID): bool
{
    $record = $_SESSION['fresh_dental_records'][(string) $recordID] ?? null;

    return is_array($record)
        && (int) ($record['patientID'] ?? 0) === $patientID
        && (int) ($record['dentistID'] ?? 0) === $dentistID;
}

function validate_record_date($value, string $fieldName, bool $allowEmpty = false): ?string
{
    if (($value === null || $value === '') && $allowEmpty) {
        return null;
    }

    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        throw new InvalidArgumentException($fieldName . ' must be a valid date.');
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();

    if (
        !$date
        || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        || $date->format('Y-m-d') !== $value
    ) {
        throw new InvalidArgumentException($fieldName . ' must be a valid date.');
    }

    if ($value > date('Y-m-d')) {
        throw new InvalidArgumentException($fieldName . ' cannot be in the future.');
    }

    return $value;
}

try {
    switch ($action) {

        case 'initialize_record_page':
            unset($_SESSION['active_dental_record'], $_SESSION['fresh_dental_records']);
            echo json_encode(['success' => true]);
            break;

        case 'clear_active_visit':
            unset($_SESSION['active_dental_record']);
            echo json_encode(['success' => true]);
            break;

        case 'activate_visit':
            $recordID = filter_var($data['recordID'] ?? null, FILTER_VALIDATE_INT);
            if (!$recordID) {
                echo json_encode(['success' => false, 'message' => 'Missing Dental Record ID.']);
                break;
            }

            $stmt = $pdo->prepare(
                "SELECT RecordID FROM dentalrecord
                 WHERE RecordID = :record_id AND PatientID = :patient_id"
            );
            $stmt->execute([
                'record_id' => $recordID,
                'patient_id' => $patientID
            ]);

            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Dental record not found for this patient.']);
                break;
            }

            $_SESSION['active_dental_record'] = [
                'recordID' => (int) $recordID,
                'patientID' => (int) $patientID,
                'dentistID' => $dentistID
            ];
            echo json_encode(['success' => true]);
            break;

        case 'create_visit':
            $rawApptID = $data['appointmentID'] ?? null;
            $appointmentID = filter_var($rawApptID, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if ($appointmentID === false || $appointmentID <= 0) {
                $appointmentID = null;
            }

            $appointmentTreatmentName = null;
            if ($appointmentID !== null) {
                $appointmentStmt = $pdo->prepare(
                    "SELECT s.ServiceName
                     FROM appointment a
                     JOIN service s ON s.ServiceID = a.ServiceID
                     WHERE a.AppointmentID = :appointment_id
                       AND a.PatientID = :patient_id
                       AND a.DentistID = :dentist_id
                       AND a.AppointmentStatus = 'Confirmed'"
                );
                $appointmentStmt->execute([
                    'appointment_id' => $appointmentID,
                    'patient_id' => $patientID,
                    'dentist_id' => $dentistID
                ]);
                $appointment = $appointmentStmt->fetch();

                if (!$appointment) {
                    echo json_encode(['success' => false, 'message' => 'Confirmed appointment not found for this patient.']);
                    break;
                }

                $appointmentTreatmentName = $appointment['ServiceName'];
            }

            $recordDate = date('Y-m-d');
            if ($appointmentID !== null) {
                $stmt = $pdo->prepare(
                    "INSERT INTO dentalrecord (PatientID, DentistID, AppointmentID, RecordDate)
                     VALUES (:patient_id, :dentist_id, :appointment_id, :record_date)"
                );
                $stmt->execute([
                    'patient_id' => $patientID,
                    'dentist_id' => $dentistID,
                    'appointment_id' => $appointmentID,
                    'record_date' => $recordDate
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO dentalrecord (PatientID, DentistID, RecordDate)
                     VALUES (:patient_id, :dentist_id, :record_date)"
                );
                $stmt->execute([
                    'patient_id' => $patientID,
                    'dentist_id' => $dentistID,
                    'record_date' => $recordDate
                ]);
            }

            $newRecordID = (int) $pdo->lastInsertId();
            $_SESSION['fresh_dental_records'][(string) $newRecordID] = [
                'patientID' => (int) $patientID,
                'dentistID' => $dentistID
            ];
            $_SESSION['active_dental_record'] = [
                'recordID' => $newRecordID,
                'patientID' => (int) $patientID,
                'dentistID' => $dentistID
            ];
            echo json_encode([
                'success' => true,
                'message' => 'New visit created successfully.',
                'recordID' => $newRecordID,
                'recordDate' => $recordDate,
                'appointmentTreatmentName' => $appointmentTreatmentName
            ]);
            break;

        case 'save_examination':
            $recordID = filter_var($data['recordID'] ?? null, FILTER_VALIDATE_INT);
            $requestedExamDate = $data['examDate'] ?? null;
            $oralHygiene = $data['oralHygiene'] ?? null;
            $gumCondition = $data['gumCondition'] ?? null;
            $examNotes = $data['examNotes'] ?? null;

            if (!$recordID) {
                echo json_encode(['success' => false, 'message' => 'Missing Dental Record ID.']);
                exit;
            }

            $recordStmt = $pdo->prepare(
                "SELECT RecordDate FROM dentalrecord
                 WHERE RecordID = :record_id AND PatientID = :patient_id"
            );
            $recordStmt->execute([
                'record_id' => $recordID,
                'patient_id' => $patientID
            ]);
            $existingRecord = $recordStmt->fetch();

            if (!$existingRecord) {
                echo json_encode(['success' => false, 'message' => 'Dental record not found for this patient.']);
                break;
            }

            if (is_fresh_dental_record((int) $recordID, (int) $patientID, $dentistID)) {
                $examDate = validate_record_date($requestedExamDate, 'Examination Date');
            } else {
                if ($requestedExamDate !== $existingRecord['RecordDate']) {
                    echo json_encode(['success' => false, 'message' => 'Examination Date cannot be changed when editing an existing dental record.']);
                    break;
                }
                $examDate = $existingRecord['RecordDate'];
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
            $requestedTreatmentDate = $data['treatmentDate'] ?? null;
            $diagnosis = $data['diagnosis'] ?? null;
            $treatmentName = $data['treatmentName'] ?? null;
            $treatmentDetails = $data['treatmentDetails'] ?? null;

            if (!$recordID) {
                echo json_encode(['success' => false, 'message' => 'Missing Dental Record ID.']);
                exit;
            }

            $recordStmt = $pdo->prepare(
                "SELECT RecordDate FROM dentalrecord
                 WHERE RecordID = :record_id AND PatientID = :patient_id"
            );
            $recordStmt->execute([
                'record_id' => $recordID,
                'patient_id' => $patientID
            ]);
            $existingRecord = $recordStmt->fetch();

            if (!$existingRecord) {
                echo json_encode(['success' => false, 'message' => 'Dental record not found for this patient.']);
                break;
            }

            if ($treatmentID) {
                $checkTreat = $pdo->prepare(
                    "SELECT TreatmentID, TreatmentDate FROM treatment
                     WHERE RecordID = :record_id AND TreatmentID = :treatment_id"
                );
                $checkTreat->execute([
                    'record_id' => $recordID,
                    'treatment_id' => $treatmentID
                ]);
            } else {
                $checkTreat = $pdo->prepare(
                    "SELECT TreatmentID, TreatmentDate FROM treatment
                     WHERE RecordID = :record_id
                     ORDER BY TreatmentDate DESC, TreatmentID DESC LIMIT 1"
                );
                $checkTreat->execute(['record_id' => $recordID]);
            }
            $existingTreat = $checkTreat->fetch();

            if ($treatmentID && !$existingTreat) {
                echo json_encode(['success' => false, 'message' => 'Treatment does not belong to this dental record.']);
                break;
            }
            if ($existingTreat) {
                $treatmentID = (int) $existingTreat['TreatmentID'];
            }

            if (is_fresh_dental_record((int) $recordID, (int) $patientID, $dentistID)) {
                $treatmentDate = validate_record_date($requestedTreatmentDate, 'Treatment Date');
            } else {
                $savedTreatmentDate = $existingTreat['TreatmentDate'] ?? $existingRecord['RecordDate'];
                if ($requestedTreatmentDate !== $savedTreatmentDate) {
                    echo json_encode(['success' => false, 'message' => 'Treatment Date cannot be changed when editing an existing dental record.']);
                    break;
                }
                $treatmentDate = $savedTreatmentDate;
            }

            $pdo->beginTransaction();

            // 1. Always update Diagnosis on the parent dental record
            $diagStmt = $pdo->prepare(
                "UPDATE dentalrecord SET Diagnosis = :diagnosis
                 WHERE RecordID = :record_id AND PatientID = :patient_id"
            );
            $diagStmt->execute([
                'diagnosis' => $diagnosis,
                'record_id' => $recordID,
                'patient_id' => $patientID
            ]);

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
            $activeRecord = $_SESSION['active_dental_record'] ?? null;
            if (
                !is_array($activeRecord)
                || (int) ($activeRecord['patientID'] ?? 0) !== (int) $patientID
                || (int) ($activeRecord['dentistID'] ?? 0) !== $dentistID
            ) {
                echo json_encode(['success' => false, 'message' => 'Select or start a dental visit before saving a prescription.']);
                break;
            }

            $recordID = (int) $activeRecord['recordID'];
            $medicationName = trim($data['medicationName'] ?? '');
            $dosage = trim($data['dosage'] ?? '');
            $instructions = trim($data['instructions'] ?? '');

            if ($medicationName === '') {
                echo json_encode(['success' => false, 'message' => 'Please enter a medication name.']);
                break;
            }

            $checkRecord = $pdo->prepare(
                "SELECT RecordID FROM dentalrecord
                 WHERE RecordID = :record_id AND PatientID = :patient_id"
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
            $recordID = filter_var($data['recordID'] ?? null, FILTER_VALIDATE_INT);
            $treatmentType = trim($data['treatmentType'] ?? '');
            $requestedStartDate = $data['startDate'] ?? null;
            $currentStage = trim($data['currentStage'] ?? '');
            $nextAdjustment = !empty($data['nextAdjustment']) ? $data['nextAdjustment'] : null;
            $progressNotes = trim($data['progressNotes'] ?? '');

            if (!$recordID) {
                echo json_encode(['success' => false, 'message' => 'Missing Dental Record ID.']);
                break;
            }

            $recordStmt = $pdo->prepare(
                "SELECT RecordID FROM dentalrecord
                 WHERE RecordID = :record_id AND PatientID = :patient_id"
            );
            $recordStmt->execute([
                'record_id' => $recordID,
                'patient_id' => $patientID
            ]);
            $existingRecord = $recordStmt->fetch();

            if (!$existingRecord) {
                echo json_encode(['success' => false, 'message' => 'Dental record not found for this patient.']);
                break;
            }

            $startDate = validate_record_date(
                $requestedStartDate,
                'Orthodontic Start Date',
                true
            );

            $stmt = $pdo->prepare(
                "INSERT INTO orthodonticrecord
                    (PatientID, DentistID, TreatmentType, StartDate, CurrentStage, ProgressNotes, NextAdjustmentDate)
                 VALUES
                    (:patient_id, :dentist_id, :treatment_type, :start_date, :current_stage, :progress_notes, :next_adjustment)"
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

            echo json_encode([
                'success' => true,
                'message' => 'Orthodontic record created successfully.',
                'orthoRecordID' => $orthoRecordID
            ]);
            break;
    }

} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}