<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';
$nextAppointmentStatement = $pdo->prepare(
        "SELECT a.AppointmentDate, a.AppointmentTime, a.AppointmentStatus, s.ServiceName, u.FullName AS DentistName
         FROM appointment a
         INNER JOIN service s ON s.ServiceID = a.ServiceID
         INNER JOIN dentist d ON d.DentistID = a.DentistID
         INNER JOIN user u ON u.UserID = d.UserID
         WHERE a.PatientID = :patient_id AND a.AppointmentStatus IN ('Pending', 'Confirmed')
             AND TIMESTAMP(a.AppointmentDate, a.AppointmentTime) >= NOW()
         ORDER BY a.AppointmentDate, a.AppointmentTime LIMIT 1"
);
$nextAppointmentStatement->execute(['patient_id' => $currentPatient['PatientID']]);
$nextAppointment = $nextAppointmentStatement->fetch();
$dashboardServicesStatement = $pdo->query(
    'SELECT ServiceID, ServiceName, Category, Description, Price
     FROM service
     ORDER BY ServiceID ASC
     LIMIT 4'
);
$dashboardServices = $dashboardServicesStatement->fetchAll();
$recordCountStatement = $pdo->prepare('SELECT COUNT(*) FROM dentalrecord WHERE PatientID = :patient_id');
$recordCountStatement->execute(['patient_id' => $currentPatient['PatientID']]);
$recordCount = (int) $recordCountStatement->fetchColumn();
$recentRecordsStatement = $pdo->prepare(
        'SELECT RecordDate, Diagnosis, ClinicalNotes FROM dentalrecord
         WHERE PatientID = :patient_id ORDER BY RecordDate DESC, RecordID DESC LIMIT 3'
);
$recentRecordsStatement->execute(['patient_id' => $currentPatient['PatientID']]);
$recentRecords = $recentRecordsStatement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Patient Dashboard | BrightSmile Dental Clinic</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/index.css">
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
                <?php
                    include '../Components/Header.php';
                ?>

                <!-- Page content -->
                <div class="page-content">

                    <!-- Welcome section -->
                    <section class="welcome-section">
                        <div>
                            <h1>
                                Good day, <?= escape_html($currentPatient['FullName']) ?>
                            </h1>
                            <p>
                                Here's a quick overview of your dental care.
                            </p>
                        </div>

                        <a href="book-appointment.php" class="primary-button">
                            + Book Appointment
                        </a>
                    </section>

                    <!-- Our Services -->
                    <section class="dashboard-services" aria-labelledby="dashboard-services-title">
                        <div class="section-heading">
                            <h2 id="dashboard-services-title">Our Services</h2>
                            <a href="services.php">View More</a>
                        </div>

                        <div class="dashboard-service-grid">
                            <?php foreach ($dashboardServices as $service): ?>
                                <article class="dashboard-service-card">
                                    <div class="dashboard-service-icon" aria-hidden="true">+</div>
                                    <div class="dashboard-service-content">
                                        <?php if (!empty($service['Category'])): ?>
                                            <span class="dashboard-service-category"><?= escape_html($service['Category']) ?></span>
                                        <?php endif; ?>
                                        <h3><?= escape_html($service['ServiceName']) ?></h3>
                                        <p><?= escape_html($service['Description'] ?? '') ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Summary cards -->
                    <section class="summary-grid">
                        <!-- Next Appointment -->
                        <div class="summary-card">
                            <div class="card-top">
                                <div class="card-icon appointment-icon">
                                    ▣
                                </div>
                                <span class="card-status">
                                    Upcoming
                                </span>
                            </div>

                            <p class="card-label">
                                NEXT APPOINTMENT
                            </p>

                            <h2>
                                <?= $nextAppointment ? escape_html(date('d M', strtotime($nextAppointment['AppointmentDate']))) : 'None' ?>
                            </h2>

                            <p class="card-description">
                                <?= $nextAppointment ? escape_html(date('g:i A', strtotime($nextAppointment['AppointmentTime'])) . ' · ' . $nextAppointment['ServiceName']) : 'No upcoming appointment' ?>
                            </p>
                        </div>

                        <!-- Dental Records -->
                        <div class="summary-card">
                            <div class="card-top">
                                <div class="card-icon records-icon">
                                    +
                                </div>

                                <span class="card-status">
                                    Updated
                                </span>
                            </div>

                            <p class="card-label">
                                DENTAL RECORDS
                            </p>

                            <h2>
                                <?= $recordCount ?>
                            </h2>

                            <p class="card-description">
                                Recorded dental visits
                            </p>
                        </div>
                    </section>

                    <!-- Dashboard grid -->
                    <section class="dashboard-grid">

                        <!-- Upcoming appointment -->
                        <div class="dashboard-card appointment-card">
                            <div class="section-heading">
                                <div>
                                    <h2>
                                        Upcoming Appointment
                                    </h2>

                                    <p>
                                        Your next scheduled visit
                                    </p>
                                </div>

                                <a href="appointments.php">
                                    View all
                                </a>
                            </div>

                            <div class="appointment-details">
                                <div class="doctor-avatar">DR</div>

                                <div class="doctor-info">
                                    <strong><?= escape_html($nextAppointment['DentistName'] ?? 'No appointment') ?></strong>

                                    <span>
                                        <?= escape_html($nextAppointment['ServiceName'] ?? 'Book a visit to see your provider') ?>
                                    </span>
                                </div>

                                <div class="appointment-info">
                                    <strong><?= $nextAppointment ? escape_html(date('d F Y', strtotime($nextAppointment['AppointmentDate']))) : 'No upcoming visit' ?></strong>

                                    <span>
                                        <?= $nextAppointment ? escape_html(date('g:i A', strtotime($nextAppointment['AppointmentTime'])) . ' · BrightSmile Clinic') : 'Your scheduled visits will appear here' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="appointment-status">
                                <?php if ($nextAppointment): ?><span class="status-dot"></span><?= escape_html($nextAppointment['AppointmentStatus']) ?><?php else: ?>No upcoming appointment<?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick actions -->

                        <div class="dashboard-card">
                            <div class="section-heading">
                                <div>
                                    <h2>
                                        Quick Actions
                                    </h2>

                                    <p>
                                        Common tasks
                                    </p>
                                </div>
                            </div>

                            <div class="quick-actions">
                                <a href="book-appointment.php" class="action-button">
                                    <span class="action-icon">
                                        +
                                    </span>
                                    <div>
                                        <strong>
                                            Book Appointment
                                        </strong>

                                        <span>
                                            Schedule a clinic visit
                                        </span>
                                    </div>
                                </a>

                                <a href="dental-records.php" class="action-button">
                                    <span class="action-icon">
                                        ▤
                                    </span>

                                    <div>
                                        <strong>
                                            View Dental Records
                                        </strong>

                                        <span>
                                            Check your treatment history
                                        </span>
                                    </div>
                                </a>

                                <a href="orthodontics.php" class="action-button">
                                    <span class="action-icon">
                                        ◇
                                    </span>

                                    <div>
                                        <strong>
                                            Treatment Progress
                                        </strong>

                                        <span>
                                            View orthodontic progress
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </section>

                    <!-- Recent activity -->
                    <section class="dashboard-card activity-card">
                        <div class="section-heading">
                            <div>
                                <h2>
                                    Recent Activity
                                </h2>

                                <p>
                                    Your latest dental care updates
                                </p>
                            </div>

                            <a href="dental-records.php">
                                View records
                            </a>
                        </div>

                        <div class="activity-list">
                            <?php foreach ($recentRecords as $recentRecord): ?>
                            <div class="activity-item">
                                <div class="activity-marker">
                                    ✓
                                </div>

                                <div class="activity-content">
                                    <strong>
                                        Dental visit recorded
                                    </strong>

                                    <span>
                                        <?= escape_html($recentRecord['Diagnosis'] ?: $recentRecord['ClinicalNotes'] ?: 'Clinical record added') ?>
                                    </span>
                                </div>

                                <time>
                                    <?= escape_html(date('d M Y', strtotime($recentRecord['RecordDate']))) ?>
                                </time>
                            </div>
                            <?php endforeach; ?>
                            <?php if (!$recentRecords): ?><p>No recent records yet.</p><?php endif; ?>
                        </div>
                    </section>
                </div>
            </main>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
    </body>
</html>