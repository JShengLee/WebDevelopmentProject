<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';
$orthodonticStatement = $pdo->prepare(
    'SELECT o.TreatmentType, o.StartDate, o.CurrentStage, o.ProgressNotes, o.NextAdjustmentDate, u.FullName AS DentistName
     FROM orthodonticrecord o
     INNER JOIN dentist d ON d.DentistID = o.DentistID
     INNER JOIN user u ON u.UserID = d.UserID
     WHERE o.PatientID = :patient_id
     ORDER BY o.OrthoRecordID DESC'
);
$orthodonticStatement->execute(['patient_id' => $currentPatient['PatientID']]);
$treatmentHistory = $orthodonticStatement->fetchAll();
$currentTreatment = $treatmentHistory[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Orthodontics | BrightSmile Dental Clinic</title>
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
                        <h1>Orthodontics</h1>
                        <p class="page-description">Track your braces treatment and keep up with your next adjustment.</p>
                    </div>
                    <a class="primary-button" href="book-appointment.php"><span aria-hidden="true">+</span> Request a visit</a>
                </div>

                <div class="orthodontic-overview">
                    <section class="treatment-banner">
                        <span class="eyebrow"><?= $currentTreatment ? 'ACTIVE TREATMENT · ' . escape_html($currentTreatment['TreatmentType']) : 'TREATMENT' ?></span>
                        <h2><?= $currentTreatment ? escape_html($currentTreatment['CurrentStage'] ?: $currentTreatment['TreatmentType']) : 'No orthodontic treatment recorded' ?></h2>
                        <p><?= $currentTreatment ? escape_html($currentTreatment['ProgressNotes'] ?: 'Your current treatment details are shown below.') : 'Your orthodontic care updates will appear here.' ?></p>
                        <?php if ($currentTreatment): ?><div class="progress-line"><span>Current treatment stage</span><strong><?= escape_html($currentTreatment['CurrentStage'] ?: 'In progress') ?></strong></div><?php endif; ?>
                    </section>
                    <section class="surface-panel next-visit">
                        <div>
                            <span class="eyebrow">NEXT ADJUSTMENT</span>
                            <strong><?= $currentTreatment && $currentTreatment['NextAdjustmentDate'] ? escape_html(date('d F Y', strtotime($currentTreatment['NextAdjustmentDate']))) : 'Not scheduled' ?></strong>
                            <p><?= $currentTreatment ? escape_html($currentTreatment['DentistName']) : 'No orthodontist assigned' ?></p>
                        </div>
                        <a class="text-link" href="appointments.php">View appointment <span aria-hidden="true">→</span></a>
                    </section>
                </div>

                <div class="treatment-grid">
                    <section class="surface-panel">
                        <h2>Treatment overview</h2>
                        <p class="panel-intro">Your current orthodontic plan</p>
                        <dl class="detail-list">
                            <div><dt>Treatment type</dt><dd><?= escape_html($currentTreatment['TreatmentType'] ?? 'Not recorded') ?></dd></div>
                            <div><dt>Started</dt><dd><?= $currentTreatment && $currentTreatment['StartDate'] ? escape_html(date('d F Y', strtotime($currentTreatment['StartDate']))) : 'Not recorded' ?></dd></div>
                            <div><dt>Current stage</dt><dd><?= escape_html($currentTreatment['CurrentStage'] ?? 'Not recorded') ?></dd></div>
                            <div><dt>Orthodontist</dt><dd><?= escape_html($currentTreatment['DentistName'] ?? 'Not assigned') ?></dd></div>
                        </dl>
                    </section>
                    <section class="surface-panel">
                        <h2>Recent milestones</h2>
                        <p class="panel-intro">Notes from your orthodontic visits</p>
                        <div class="timeline">
                            <?php foreach ($treatmentHistory as $treatmentUpdate): ?>
                                <div class="timeline-item"><span class="timeline-dot"></span><div><strong><?= escape_html($treatmentUpdate['CurrentStage'] ?: $treatmentUpdate['TreatmentType']) ?></strong><small><?= $treatmentUpdate['StartDate'] ? escape_html(date('d F Y', strtotime($treatmentUpdate['StartDate'])) . ' · ') : '' ?><?= escape_html($treatmentUpdate['ProgressNotes'] ?: $treatmentUpdate['TreatmentType']) ?></small></div></div>
                            <?php endforeach; ?>
                            <?php if (!$treatmentHistory): ?><p>No treatment milestones recorded.</p><?php endif; ?>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>