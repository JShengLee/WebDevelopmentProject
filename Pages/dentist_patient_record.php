<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

// Verify authentication & retrieve logged-in dentist details
$currentDentist = require_patient($pdo);
$dentistID = $currentDentist['DentistID'];

// Get the selected patient ID from the URL.
$patientID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// If the URL passes 'P001' instead of 1, clean it:
if (!$patientID && isset($_GET['id'])) {
    $cleanID = preg_replace('/\D/', '', $_GET['id']);
    $patientID = filter_var($cleanID, FILTER_VALIDATE_INT) ?: null;
}

// Fetch all available services for the treatment dropdown using PDO
$services = [];
$serviceQuery = $pdo->query("SELECT ServiceID, ServiceName FROM service ORDER BY ServiceName ASC");
if ($serviceQuery) {
    $services = $serviceQuery->fetchAll();
}

$visits = [];
$recordDetails = [];
$orthodonticRecords = [];
$patient = null;

if ($patientID) {

    // 1. Retrieve the patient profile.
    $sql = "SELECT
                p.PatientID,
                u.FullName,
                u.Phone,
                p.DateOfBirth,
                p.Gender
            FROM patient p
            JOIN user u ON p.UserID = u.UserID
            WHERE p.PatientID = :patient_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['patient_id' => $patientID]);
    $row = $stmt->fetch();

    if ($row) {

        $age = '-';

        if (!empty($row['DateOfBirth'])) {
            $birthDate = new DateTime($row['DateOfBirth']);
            $age = $birthDate->diff(new DateTime())->y;
        }

        $nameParts = preg_split('/\s+/', trim($row['FullName']));
        $initials = '';

        foreach ($nameParts as $part) {
            if ($part !== '') {
                $initials .= mb_substr($part, 0, 1);
            }

            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        $patient = [
            'id' => (int) $row['PatientID'],
            'name' => $row['FullName'],
            'age' => $age,
            'gender' => $row['Gender'] ?: '-',
            'phone' => $row['Phone'] ?: '-',
            'initials' => mb_strtoupper($initials),
            'treatment' => 'No treatment recorded'
        ];
    }

    // Continue only if the patient profile exists
    if ($patient) {

        // 2. Retrieve this patient's dental visits (All dentists)
        $sql = "SELECT
                    dr.RecordID,
                    dr.RecordDate,
                    dr.Diagnosis,
                    dr.ClinicalNotes,
                    dr.oralHygiene,
                    dr.gumCondition,

                    COALESCE(
                        (
                            SELECT GROUP_CONCAT(
                                t.TreatmentName
                                ORDER BY t.TreatmentDate DESC,
                                         t.TreatmentID DESC
                                SEPARATOR ', '
                            )
                            FROM treatment t
                            WHERE t.RecordID = dr.RecordID
                        ),
                        s.ServiceName,
                        'Dental visit'
                    ) AS TreatmentName

                FROM dentalrecord dr
                LEFT JOIN appointment a
                    ON dr.AppointmentID = a.AppointmentID
                LEFT JOIN service s
                    ON a.ServiceID = s.ServiceID

                WHERE dr.PatientID = :patient_id

                ORDER BY dr.RecordDate DESC, dr.RecordID DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientID]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {

            $recordID = (int) $row['RecordID'];

            $visits[] = [
                'id' => $recordID,
                'date' => date(
                    'j M Y',
                    strtotime($row['RecordDate'])
                ),
                'treatment' => $row['TreatmentName']
            ];

            $recordDetails[$recordID] = [
                'id' => $recordID,
                'date' => $row['RecordDate'],
                'diagnosis' => $row['Diagnosis'] ?? '',
                'clinicalNotes' => $row['ClinicalNotes'] ?? '',
                'oralHygiene' => $row['oralHygiene'] ?? '',
                'gumCondition' => $row['gumCondition'] ?? '',
                'treatment' => $row['TreatmentName'],
                'treatments' => [],
                'prescriptions' => [],
                'teeth' => []
            ];
        }

        // 3. Retrieve treatment details for each dental record (All dentists)
        $sql = "SELECT
                    t.TreatmentID,
                    t.RecordID,
                    t.TreatmentName,
                    t.TreatmentDetails,
                    t.TreatmentDate
                FROM treatment t
                JOIN dentalrecord dr ON t.RecordID = dr.RecordID
                WHERE dr.PatientID = :patient_id
                ORDER BY t.TreatmentDate DESC, t.TreatmentID DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientID]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $recordID = (int) $row['RecordID'];

            if (isset($recordDetails[$recordID])) {
                $recordDetails[$recordID]['treatments'][] = [
                    'name' => $row['TreatmentName'],
                    'details' => $row['TreatmentDetails'] ?? '',
                    'date' => $row['TreatmentDate']
                ];
            }
        }

        // 4. Retrieve prescriptions for each dental record (All dentists)
        $sql = "SELECT
                    pr.PrescriptionID,
                    pr.RecordID,
                    pr.MedicationName,
                    pr.Dosage,
                    pr.Instructions
                FROM prescription pr
                JOIN dentalrecord dr ON pr.RecordID = dr.RecordID
                WHERE dr.PatientID = :patient_id
                ORDER BY pr.PrescriptionID DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientID]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $recordID = (int) $row['RecordID'];

            if (isset($recordDetails[$recordID])) {
                $recordDetails[$recordID]['prescriptions'][] = [
                    'id' => (int) $row['PrescriptionID'],
                    'medication' => $row['MedicationName'],
                    'dosage' => $row['Dosage'],
                    'instructions' => $row['Instructions']
                ];
            }
        }

        // 5. Retrieve recorded tooth conditions and notes (All dentists)
        $sql = "SELECT
                    tr.RecordID,
                    t.ToothNumber,
                    tr.ConditionDescription,
                    tr.Notes
                FROM toothrecord tr
                JOIN tooth t ON tr.ToothID = t.ToothID
                JOIN dentalrecord dr ON tr.RecordID = dr.RecordID
                WHERE dr.PatientID = :patient_id
                ORDER BY tr.ToothRecordID ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientID]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $recordID = (int) $row['RecordID'];

            if (isset($recordDetails[$recordID])) {
                $recordDetails[$recordID]['teeth'][] = [
                    'number' => (int) $row['ToothNumber'],
                    'condition' => $row['ConditionDescription'],
                    'notes' => $row['Notes'] ?? ''
                ];
            }
        }

        // 6. Retrieve orthodontic records for this patient (All dentists)
        $sql = "SELECT
                    OrthoRecordID,
                    TreatmentType,
                    StartDate,
                    CurrentStage,
                    ProgressNotes,
                    NextAdjustmentDate
                FROM orthodonticrecord
                WHERE PatientID = :patient_id
                ORDER BY StartDate DESC, OrthoRecordID DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientID]);
        $orthodonticRecords = $stmt->fetchAll();

        // 7. Display the most recent treatment as the profile summary.
        if (!empty($orthodonticRecords) && empty($visits)) {
            $patient['treatment'] = $orthodonticRecords[0]['TreatmentType'];
        } elseif (!empty($visits)) {
            $patient['treatment'] = $visits[0]['treatment'];
        } elseif (!empty($orthodonticRecords)) {
            $patient['treatment'] = $orthodonticRecords[0]['TreatmentType'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Patient Dental Record | BrightSmile Dental Clinic</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="../Styling/dentist_patient_record.css">
</head>

<body>
<div class="app-container">

    <?php include '../Components/Sidebar.php'; ?>

    <main class="main-content">

        <?php include '../Components/Header.php'; ?>

        <div class="page-content">

            <?php if (!$patient): ?>

                <section class="record-card">
                    <h2>Patient Not Found</h2>
                    <p>The selected patient could not be found.</p>
                    <a href="dentist_patients.php" class="back-button">
                        Back to Patients
                    </a>
                </section>

            <?php else: ?>

                <!-- Page heading -->
                <section class="page-heading">
                    <h1>Patient Dental Record</h1>

                    <p>
                        Review the patient's dental history, examinations
                        and treatment information.
                    </p>
                </section>

                <!-- Patient information -->
                <section class="patient-profile-card">
                    <div class="profile-left">
                        <div class="large-patient-avatar">
                            <?= escape_html($patient['initials']) ?>
                        </div>

                        <div class="profile-details">
                            <h2><?= escape_html($patient['name']) ?></h2>

                            <span class="patient-code">
                                Patient ID: <?= escape_html($patient['id']) ?>
                            </span>

                            <div class="patient-meta">
                                <span><?= escape_html($patient['gender']) ?></span>
                                <span><?= escape_html($patient['age']) ?> years old</span>
                                <span><?= escape_html($patient['phone']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="profile-status">
                        <span class="status-label">Current Treatment</span>

                        <span class="treatment-badge">
                            <?= escape_html($patient['treatment']) ?>
                        </span>
                    </div>
                </section>

                
                <!-- Dental Visit History -->
                <section class="visit-section">

                    <div class="visit-heading">
                        <div>
                            <h2>Dental Visits</h2>
                            <p>
                                Select a previous visit to review, or start a new visit.
                            </p>
                        </div>

                        <span class="visit-count" id="visitCountDisplay">
                            <?= count($visits) ?>
                            <?= count($visits) === 1 ? 'Visit' : 'Visits' ?>
                        </span>
                    </div>

                    <!-- Search & Date Filter Bar (Matching dentist_patients_css style) -->
                    <div class="filter-row">
                        <div class="search-box">
                            <span><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" 
                                id="searchRecordID" 
                                placeholder="Search Dental Record ID" 
                                autocomplete="off">
                            <button type="button" class="clear-input-btn" id="clearSearchBtn" hidden>
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="search-box date-box">
                            <span><i class="fa-regular fa-calendar-days"></i></span>
                            <input type="date" 
                                id="filterVisitDate">
                            <button type="button" class="clear-input-btn" id="clearDateBtn" hidden>
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($visits)): ?>

                        <div class="empty-visits">
                            <div class="empty-icon">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>

                            <h3>No Dental Records Yet</h3>

                            <p>
                                There are no previous dental visits for this patient.
                                Start a new visit to begin recording dental information.
                            </p>
                        </div>

                    <?php else: ?>

                        <div class="visit-list" id="visitList">

                            <?php foreach ($visits as $visit): ?>

                                <div class="visit-card"
                                    data-visit-id="<?= (int)$visit['id'] ?>"
                                    data-visit-raw-date="<?= htmlspecialchars($recordDetails[$visit['id']]['date'] ?? '') ?>">

                                    <div class="visit-date-icon">
                                        <i class="fa-regular fa-calendar"></i>
                                    </div>

                                    <div class="visit-details">
                                        <div class="visit-title-row">
                                            <h3>
                                                <?= htmlspecialchars($visit['date']) ?>
                                            </h3>

                                            <span class="existing-badge">Existing</span>
                                        </div>

                                        <p>
                                            <?= htmlspecialchars($visit['treatment']) ?>
                                            · Record #<?= (int)$visit['id'] ?>
                                        </p>

                                        <button type="button"
                                                class="view-visit-button"
                                                data-visit-id="<?= (int)$visit['id'] ?>"
                                                data-visit-date="<?= htmlspecialchars($visit['date'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-visit-treatment="<?= htmlspecialchars($visit['treatment'], ENT_QUOTES, 'UTF-8') ?>">

                                            View this record
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </button>
                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                        <!-- Empty state when search or date yields no results -->
                        <div class="empty-visits" id="noFilterResults" hidden>
                            <div class="empty-icon">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <h3>No Matching Records</h3>
                            <p>No dental visits match your current Record ID or Date filter.</p>
                        </div>

                    <?php endif; ?>

                    <div class="new-visit-area">
                        <button type="button"
                                id="startNewVisit"
                                class="new-visit-button">

                            <i class="fa-solid fa-plus"></i>
                            Start New Visit
                        </button>
                    </div>

                </section>

                <!-- Current Visit Information -->
                <section id="activeVisitBanner" class="active-visit-banner" hidden>

                    <div>
                        <span class="active-visit-label">CURRENTLY SELECTED VISIT</span>

                        <h2 id="activeVisitTitle">Dental Visit</h2>

                        <p id="activeVisitDescription"></p>
                    </div>

                    <button type="button"
                            id="changeVisit"
                            class="change-visit-button">

                        <i class="fa-solid fa-arrow-left"></i>
                        Change Visit
                    </button>

                </section>

                <!-- Six patient record sections -->
                <?php include 'six_record_tabs.php'; ?>

            <?php endif; ?>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/f3ce044c74.js"
        crossorigin="anonymous"></script>
<script>
    const recordDetails = <?= json_encode(
        $recordDetails,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const orthodonticRecords = <?= json_encode(
        $orthodonticRecords,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    // Expose available database services to JS (Optional)
    const availableServices = <?= json_encode(
        $services,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;
</script>
<script src="../dentist_patient_record.js"></script>
</body>
</html>