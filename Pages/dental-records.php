<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';
$recordStatement = $pdo->prepare(
    'SELECT r.RecordID, r.RecordDate, r.Diagnosis, r.ClinicalNotes, duser.FullName AS DentistName,
            s.ServiceName, a.AppointmentID
     FROM dentalrecord r
     INNER JOIN patient p ON p.PatientID = r.PatientID
     INNER JOIN user patient_user ON patient_user.UserID = p.UserID AND patient_user.Role = \'Patient\'
     INNER JOIN appointment a ON a.AppointmentID = r.AppointmentID
         AND a.PatientID = r.PatientID AND LOWER(a.AppointmentStatus) = \'completed\'
     INNER JOIN dentist d ON d.DentistID = r.DentistID
     INNER JOIN user duser ON duser.UserID = d.UserID AND duser.Role = \'Dentist\'
     INNER JOIN service s ON s.ServiceID = a.ServiceID
     WHERE p.PatientID = :record_patient_id AND patient_user.UserID = :record_user_id

     UNION ALL

     SELECT NULL AS RecordID, a.AppointmentDate AS RecordDate, NULL AS Diagnosis, NULL AS ClinicalNotes,
            duser.FullName AS DentistName, s.ServiceName, a.AppointmentID
     FROM appointment a
     INNER JOIN patient p ON p.PatientID = a.PatientID
     INNER JOIN user patient_user ON patient_user.UserID = p.UserID AND patient_user.Role = \'Patient\'
     INNER JOIN dentist d ON d.DentistID = a.DentistID
     INNER JOIN user duser ON duser.UserID = d.UserID AND duser.Role = \'Dentist\'
     INNER JOIN service s ON s.ServiceID = a.ServiceID
     LEFT JOIN dentalrecord r ON r.AppointmentID = a.AppointmentID AND r.PatientID = a.PatientID
     WHERE p.PatientID = :appointment_patient_id AND patient_user.UserID = :appointment_user_id
         AND LOWER(a.AppointmentStatus) = \'completed\' AND r.RecordID IS NULL
     ORDER BY RecordDate DESC, RecordID DESC, AppointmentID DESC'
);
$recordStatement->execute([
    'record_patient_id' => $currentPatient['PatientID'],
    'record_user_id' => $currentPatient['UserID'],
    'appointment_patient_id' => $currentPatient['PatientID'],
    'appointment_user_id' => $currentPatient['UserID'],
]);
$records = $recordStatement->fetchAll();
$treatmentStatement = $pdo->prepare('SELECT RecordID, TreatmentName, TreatmentDetails FROM treatment WHERE RecordID IN (SELECT RecordID FROM dentalrecord WHERE PatientID = :patient_id) ORDER BY TreatmentDate DESC');
$treatmentStatement->execute(['patient_id' => $currentPatient['PatientID']]);
$treatmentsByRecord = [];
foreach ($treatmentStatement->fetchAll() as $treatment) {
    $treatmentsByRecord[$treatment['RecordID']][] = $treatment;
}
$prescriptionStatement = $pdo->prepare('SELECT RecordID, MedicationName, Dosage, Instructions FROM prescription WHERE RecordID IN (SELECT RecordID FROM dentalrecord WHERE PatientID = :patient_id) ORDER BY PrescriptionID');
$prescriptionStatement->execute(['patient_id' => $currentPatient['PatientID']]);
$prescriptionsByRecord = [];
foreach ($prescriptionStatement->fetchAll() as $prescription) {
    $prescriptionsByRecord[$prescription['RecordID']][] = $prescription;
}
$lastRecord = $records[0] ?? null;
$activePlanStatement = $pdo->prepare('SELECT COUNT(*) FROM orthodonticrecord WHERE PatientID = :patient_id');
$activePlanStatement->execute(['patient_id' => $currentPatient['PatientID']]);
$activePlanCount = (int) $activePlanStatement->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Dental Records | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Styling/patient-pages.css">
</head>
<body>
    <!-- Main application -->
    <div class="app-container">

        <!-- Sidebar -->
        <?php
            include '../Components/Sidebar.php';
        ?>

        <!-- Main content -->
        <main class="main-content">

            <!-- Header -->
            <?php
                include '../Components/Header.php';
            ?>

            <!-- Page content -->
            <section class="page-content patient-page">
                <div class="page-heading page-heading-row">
                    <div>
                        <h1>Dental Records</h1>
                        <p class="page-description">A clear history of your visits, treatments, and care notes.</p>
                    </div>
                    <a class="primary-button" href="appointments.php">View appointments <span aria-hidden="true">→</span></a>
                </div>

                <section class="metric-strip" aria-label="Dental record summary">
                    <div><span>RECORDED VISITS</span><strong><?= count($records) ?></strong><small>Available clinical records</small></div>
                    <div><span>ACTIVE CARE PLAN</span><strong><?= $activePlanCount ?></strong><small>Orthodontic records</small></div>
                    <div><span>LAST VISIT</span><strong><?= $lastRecord ? escape_html(date('d M', strtotime($lastRecord['RecordDate']))) : '—' ?></strong><small><?= $lastRecord ? escape_html(date('Y', strtotime($lastRecord['RecordDate']))) . ' · ' . escape_html($lastRecord['ServiceName'] ?: 'Dental visit') : 'No recorded visits' ?></small></div>
                </section>

                <section class="table-panel">
                    <div class="table-toolbar">
                        <div><h2>Visit history</h2><p>Clinical records available in your patient profile</p></div>
                        <label>
                            <span class="visually-hidden">Filter records by type</span>
                            <select id="record-filter" class="table-filters-select">
                                <option value="all">All record types</option>
                                <option value="orthodontics">Orthodontics</option>
                                <option value="general">General dentistry</option>
                            </select>
                        </label>
                    </div>
                    <div class="table-scroll">
                        <table class="data-table" id="records-table">
                            <thead><tr><th>Date</th><th>Visit</th><th>Provider</th><th>Clinical note</th><th>Record type</th></tr></thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <?php
                                        $recordType = preg_match('/ortho|brace/i', (string) $record['ServiceName']) ? 'orthodontics' : 'general';
                                        $recordTreatments = $record['RecordID'] !== null ? ($treatmentsByRecord[$record['RecordID']] ?? []) : [];
                                        $recordPrescriptions = $record['RecordID'] !== null ? ($prescriptionsByRecord[$record['RecordID']] ?? []) : [];
                                    ?>
                                    <tr data-type="<?= escape_html($recordType) ?>">
                                        <td><?= escape_html(date('d M Y', strtotime($record['RecordDate']))) ?></td>
                                        <td><strong><?= escape_html($recordTreatments[0]['TreatmentName'] ?? $record['ServiceName'] ?? 'Dental visit') ?></strong><small><?= escape_html($recordType === 'orthodontics' ? 'Orthodontics' : 'General dentistry') ?></small></td>
                                        <td><?= escape_html($record['DentistName']) ?></td>
                                        <td>
                                            <?php if ($record['Diagnosis']): ?><div><?= escape_html($record['Diagnosis']) ?></div><?php endif; ?>
                                            <?php if ($record['ClinicalNotes']): ?><div><?= escape_html($record['ClinicalNotes']) ?></div><?php endif; ?>
                                            <?php foreach ($recordTreatments as $treatment): ?>
                                                <div><strong><?= escape_html($treatment['TreatmentName']) ?>:</strong> <?= escape_html($treatment['TreatmentDetails']) ?></div>
                                            <?php endforeach; ?>
                                            <?php foreach ($recordPrescriptions as $prescription): ?>
                                                <div><strong>Prescription:</strong> <?= escape_html($prescription['MedicationName']) ?><?= $prescription['Dosage'] ? ' · ' . escape_html($prescription['Dosage']) : '' ?><?= $prescription['Instructions'] ? ' · ' . escape_html($prescription['Instructions']) : '' ?></div>
                                            <?php endforeach; ?>
                                            <?php if (!$record['Diagnosis'] && !$record['ClinicalNotes'] && !$recordTreatments && !$recordPrescriptions): ?>—<?php endif; ?>
                                        </td>
                                        <td><?= $recordType === 'orthodontics' ? 'Orthodontics' : 'General dentistry' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="empty-state" id="records-empty" hidden>No records match this type.</p>
                    </div>
                    <div class="table-footnote">Records are provided for your personal reference. Contact the clinic for corrections.</div>
                </section>
            </section>
        </main>
    </div>
    <script>
        const recordFilter = document.getElementById('record-filter');
        const recordRows = [...document.querySelectorAll('#records-table tbody tr')];
        const recordsEmpty = document.getElementById('records-empty');

        recordFilter.addEventListener('change', () => {
            let visibleCount = 0;
            recordRows.forEach((row) => {
                row.hidden = recordFilter.value !== 'all' && row.dataset.type !== recordFilter.value;
                if (!row.hidden) visibleCount += 1;
            });
            recordsEmpty.hidden = visibleCount > 0;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>