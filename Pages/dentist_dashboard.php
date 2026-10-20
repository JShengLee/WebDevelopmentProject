<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

// Verify authentication & get current logged-in user profile (Dentist)
$currentDentist = require_patient($pdo);

// Extract DentistID and Name from session-authenticated data
$dentistID = $currentDentist['DentistID'];
$dentistName = $currentDentist['FullName'];

// 1. Count today's appointments
$sqlAppointments = "SELECT COUNT(*) AS total
                    FROM appointment
                    WHERE DentistID = :dentist_id
                    AND AppointmentDate = CURDATE()
                    AND AppointmentStatus != 'Cancelled'";
$stmt = $pdo->prepare($sqlAppointments);
$stmt->execute(['dentist_id' => $dentistID]);
$appointmentCount = $stmt->fetchColumn();

// 2. Count today's pending appointments (waiting patients)
$sqlWaiting = "SELECT COUNT(*) AS total
               FROM appointment
               WHERE DentistID = :dentist_id
               AND AppointmentDate = CURDATE()
               AND AppointmentStatus = 'Pending'";
$stmt = $pdo->prepare($sqlWaiting);
$stmt->execute(['dentist_id' => $dentistID]);
$waitingCount = $stmt->fetchColumn();

// 3. Count active orthodontic records
$sqlOrtho = "SELECT COUNT(*) AS total
             FROM orthodonticrecord
             WHERE DentistID = :dentist_id";
$stmt = $pdo->prepare($sqlOrtho);
$stmt->execute(['dentist_id' => $dentistID]);
$orthodonticCount = $stmt->fetchColumn();

// 4. Retrieve today's appointments
$sqlTodayAppts = "SELECT
                    a.AppointmentID,
                    a.AppointmentTime,
                    a.AppointmentStatus,
                    u.FullName AS PatientName,
                    s.ServiceName
                  FROM appointment a
                  JOIN patient p ON a.PatientID = p.PatientID
                  JOIN user u ON p.UserID = u.UserID
                  JOIN service s ON a.ServiceID = s.ServiceID
                  WHERE a.DentistID = :dentist_id
                  AND a.AppointmentDate = CURDATE()
                  AND a.AppointmentStatus != 'Cancelled'
                  ORDER BY a.AppointmentTime ASC";
$stmt = $pdo->prepare($sqlTodayAppts);
$stmt->execute(['dentist_id' => $dentistID]);
$appointments = $stmt->fetchAll();

// 5. Retrieve recent activity
$sqlActivity = "SELECT *
                FROM (
                    SELECT
                        dr.RecordDate AS ActivityDate,
                        'Dental examination recorded' AS ActivityTitle,
                        u.FullName AS PatientName,
                        'Dental examination' AS ActivityDetail,
                        '✓' AS ActivityIcon
                    FROM dentalrecord dr
                    JOIN patient p ON dr.PatientID = p.PatientID
                    JOIN user u ON p.UserID = u.UserID
                    WHERE dr.DentistID = :dentist_id_1

                    UNION ALL

                    SELECT
                        t.TreatmentDate AS ActivityDate,
                        'Treatment record created' AS ActivityTitle,
                        u.FullName AS PatientName,
                        t.TreatmentName AS ActivityDetail,
                        '✚' AS ActivityIcon
                    FROM treatment t
                    JOIN dentalrecord dr ON t.RecordID = dr.RecordID
                    JOIN patient p ON dr.PatientID = p.PatientID
                    JOIN user u ON p.UserID = u.UserID
                    WHERE dr.DentistID = :dentist_id_2

                    UNION ALL

                    SELECT
                        dr.RecordDate AS ActivityDate,
                        'Prescription recorded' AS ActivityTitle,
                        u.FullName AS PatientName,
                        pr.MedicationName AS ActivityDetail,
                        '▤' AS ActivityIcon
                    FROM prescription pr
                    JOIN dentalrecord dr ON pr.RecordID = dr.RecordID
                    JOIN patient p ON dr.PatientID = p.PatientID
                    JOIN user u ON p.UserID = u.UserID
                    WHERE dr.DentistID = :dentist_id_3
                ) AS activity
                ORDER BY ActivityDate DESC
                LIMIT 4";

$stmt = $pdo->prepare($sqlActivity);
$stmt->execute([
    'dentist_id_1' => $dentistID,
    'dentist_id_2' => $dentistID,
    'dentist_id_3' => $dentistID
]);
$recentActivities = $stmt->fetchAll();

// Display a person's initials for the avatar
function getInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';

    foreach ($words as $word) {
        if ($word !== '') {
            $initials .= mb_substr($word, 0, 1);
        }

        if (mb_strlen($initials) >= 2) {
            break;
        }
    }

    return mb_strtoupper($initials);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Dentist Dashboard | BrightSmile Dental Clinic</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/dentist_dashboard.css">
    </head>

    <body>
        <div class="app-container">
            <!-- Sidebar -->
            <?php
                include '../Components/Sidebar.php';
            ?>


            <main class="main-content">
                <?php
                    include '../Components/Header.php';
                ?>

                <div class="page-content">
                    <section class="welcome-section">
                        <div>
                            <h1>
                                Good day, Dr. <?= escape_html($dentistName) ?> 👋
                            </h1>

                            <p>
                                Here's an overview of your dental practice today.
                            </p>

                        </div>
                    </section>

                    <section class="summary-grid">
                        <div class="summary-card">
                            <div class="card-top">

                                <div class="card-icon appointment-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>

                                <span class="card-status">
                                    Today
                                </span>
                            </div>

                            <p class="card-label">
                                TODAY'S APPOINTMENTS
                            </p>

                            <h2>
                                <?= (int)$appointmentCount ?>
                            </h2>

                            <p class="card-description">
                                Scheduled appointments
                            </p>
                        </div>

                        <div class="summary-card">
                            <div class="card-top">
                                <div class="card-icon treatment-icon">
                                    <i class="fas fa-user-injured"></i>
                                </div>

                                <span class="card-status">
                                    Waiting
                                </span>
                            </div>

                            <p class="card-label">
                                WAITING PATIENTS
                            </p>

                            <h2>
                                <?= (int)$waitingCount ?>
                            </h2>

                            <p class="card-description">
                                Patients waiting for consultation
                            </p>
                        </div>

                        <div class="summary-card">
                            <div class="card-top">
                                <div class="card-icon records-icon">
                                    ✚
                                </div>

                                <span class="card-status">
                                    Ongoing
                                </span>
                            </div>

                            <p class="card-label">
                                ACTIVE ORTHODONTICS
                            </p>

                            <h2>
                                <?= (int)$orthodonticCount ?>
                            </h2>

                            <p class="card-description">
                                Ongoing patient orthodontic treatments
                            </p>
                        </div>
                    </section>

                    <section class="dashboard-grid">
                        <div class="dashboard-card">
                            <div class="section-heading">
                                <div>

                                    <h2>
                                        Today's Appointments
                                    </h2>

                                    <p>
                                        Your scheduled patient visits
                                    </p>
                                </div>

                                <a href="dentist_schedules.php">
                                    View all
                                </a>
                            </div>

                            
                            <div class="appointment-list">

                                <?php if (count($appointments) > 0): ?>

                                    <?php foreach ($appointments as $appointment): ?>

                                        <?php
                                        $statusClass = strtolower(
                                            $appointment['AppointmentStatus']
                                        );
                                        ?>

                                        <div class="appointment-row">

                                            <div class="appointment-time">
                                                <?= date(
                                                    'h:i A',
                                                    strtotime($appointment['AppointmentTime'])
                                                ) ?>
                                            </div>

                                            <div class="patient-avatar">
                                                <?= escape_html(getInitials($appointment['PatientName'])) ?>
                                            </div>

                                            <div class="patient-info">
                                                <strong>
                                                    <?= escape_html($appointment['PatientName']) ?>
                                                </strong>

                                                <span>
                                                    <?= escape_html($appointment['ServiceName']) ?>
                                                </span>
                                            </div>

                                            <span class="status-badge <?= escape_html($statusClass) ?>">
                                                <?= escape_html($appointment['AppointmentStatus']) ?>
                                            </span>

                                        </div>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <p class="empty-message">No appointments scheduled for today.</p>

                                <?php endif; ?>

                            </div>

                        </div>

                        <div class="dashboard-card">
                            <div class="section-heading">
                                <div>
                                    <h2>
                                        Quick Actions
                                    </h2>

                                    <p>
                                        Common dentist tasks
                                    </p>
                                </div>
                            </div>

                            <div class="quick-actions">
                                <a href="dentist_schedules.php"
                                class="action-button">
                                    <span class="action-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>

                                    <div>
                                        <strong>
                                            Schedules
                                        </strong>

                                        <span>
                                            View today's appointments
                                        </span>
                                    </div>
                                </a>

                                <a href="dentist_patients.php"
                                class="action-button">
                                    <span class="action-icon">
                                        <i class="fas fa-user-injured"></i>
                                    </span>

                                    <div>
                                        <strong>
                                            Patients
                                        </strong>

                                        <span>
                                            Search patients to view or start new records
                                        </span>
                                    </div>
                                </a>

                                <a href="dentist_profile.php"
                                class="action-button">
                                    <span class="action-icon">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div>
                                        <strong>
                                            Profile
                                        </strong>

                                        <span>
                                            View and update profile information
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </section>

                    <section class="dashboard-card activity-card">
                        <div class="section-heading">
                            <div>
                                <h2>
                                    Recent Patient Activity
                                </h2>

                                <p>
                                    Latest updates from your patients
                                </p>
                            </div>

                            <a href="dentist_patients.php">
                                View records
                            </a>
                        </div>


                        
                        <div class="activity-list">

                            <?php if (count($recentActivities) > 0): ?>

                                <?php foreach ($recentActivities as $activity): ?>

                                    <?php
                                    $activityDate = $activity['ActivityDate'];

                                    if ($activityDate === date('Y-m-d')) {
                                        $dateLabel = 'Today';
                                    } elseif (
                                        $activityDate === date(
                                            'Y-m-d',
                                            strtotime('-1 day')
                                        )
                                    ) {
                                        $dateLabel = 'Yesterday';
                                    } else {
                                        $dateLabel = date(
                                            'd M Y',
                                            strtotime($activityDate)
                                        );
                                    }
                                    ?>

                                    <div class="activity-item">

                                        <div class="activity-marker">
                                            <?= escape_html($activity['ActivityIcon']) ?>
                                        </div>

                                        <div class="activity-content">

                                            <strong>
                                                <?= escape_html($activity['ActivityTitle']) ?>
                                            </strong>

                                            <span>
                                                <?= escape_html($activity['PatientName']) ?>
                                                ·
                                                <?= escape_html($activity['ActivityDetail']) ?>
                                            </span>

                                        </div>

                                        <time>
                                            <?= escape_html($dateLabel) ?>
                                        </time>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <p class="empty-message">No recent patient activity found.</p>

                            <?php endif; ?>

                        </div>

                    </section>
                </div>
            </main>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
    </body>
</html>