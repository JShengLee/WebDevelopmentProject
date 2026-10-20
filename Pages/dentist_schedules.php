<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

// Verify authentication & retrieve logged-in dentist details
$currentDentist = require_patient($pdo);
$dentistID = $currentDentist['DentistID'];

// Retrieve appointments, patient, service details, and check if a dental record already exists
$sql = "SELECT
            a.AppointmentID,
            a.PatientID,
            a.AppointmentDate,
            a.AppointmentTime,
            a.AppointmentStatus,
            a.AppointmentNotes,
            u.FullName AS PatientName,
            u.Phone AS PatientPhone,
            s.ServiceName,
            s.Duration,
            CASE WHEN dr.RecordID IS NOT NULL THEN 1 ELSE 0 END AS HasRecord
        FROM appointment a
        JOIN patient p ON a.PatientID = p.PatientID
        JOIN user u ON p.UserID = u.UserID
        JOIN service s ON a.ServiceID = s.ServiceID
        LEFT JOIN dentalrecord dr ON a.AppointmentID = dr.AppointmentID
        WHERE a.DentistID = :dentist_id
        ORDER BY a.AppointmentDate ASC,
                 a.AppointmentTime ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['dentist_id' => $dentistID]);
$rows = $stmt->fetchAll();

$appointments = [];

foreach ($rows as $row) {
    // Calculate the appointment end time based on service duration
    $startTime = new DateTime($row['AppointmentTime']);
    $endTime = clone $startTime;
    $endTime->modify('+' . (int)$row['Duration'] . ' hours');

    // Generate patient initials
    $nameParts = preg_split('/\s+/', trim($row['PatientName']));
    $initials = '';

    foreach ($nameParts as $part) {
        if ($part !== '') {
            $initials .= mb_substr($part, 0, 1);
        }

        if (mb_strlen($initials) >= 2) {
            break;
        }
    }

    // Format appointment details for frontend JavaScript consumption
    $appointments[] = [
        'raw_id' => $row['AppointmentID'],
        'raw_patient_id' => $row['PatientID'],
        'id' => 'A' . str_pad($row['AppointmentID'], 3, '0', STR_PAD_LEFT),
        'patient_id' => $row['PatientID'],
        'date' => $row['AppointmentDate'],
        'start' => $startTime->format('H:i'),
        'end' => $endTime->format('H:i'),
        'patient' => $row['PatientName'],
        'initials' => mb_strtoupper($initials),
        'treatment' => $row['ServiceName'],
        'phone' => $row['PatientPhone'] ?? '-',
        'status' => $row['AppointmentStatus'],
        'notes' => $row['AppointmentNotes'] ?? '-',
        'has_record' => (bool)$row['HasRecord']
    ];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/x-icon" href="../Logo.png">

    <title>Dentist Schedules | BrightSmile Dental Clinic</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="../Styling/dentist_dashboard.css">
    <link rel="stylesheet" href="../Styling/dentist_schedules.css">
</head>

<body>

<div class="app-container">

    <!-- Sidebar -->
    <?php include '../Components/Sidebar.php'; ?>

    <main class="main-content">

        <!-- Header -->
        <?php include '../Components/Header.php'; ?>

        <div class="page-content">

            <!-- Page heading -->
            <section class="welcome-section">
                <div>
                    <h1>Schedules</h1>

                    <p>
                        View and manage your scheduled patient appointments.
                    </p>
                </div>
            </section>

            <!-- Appointment summary -->
            <section class="appointment-summary">

                <div class="appointment-summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>

                    <div>
                        <p>Total Appointments</p>
                        <h2 id="totalAppointments">0</h2>
                    </div>
                </div>

                <div class="appointment-summary-card">
                    <div class="summary-icon waiting-icon">
                        <i class="fas fa-clock"></i>
                    </div>

                    <div>
                        <p>Pending</p>
                        <h2 id="waitingAppointments">0</h2>
                    </div>
                </div>

                <div class="appointment-summary-card">
                    <div class="summary-icon completed-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <div>
                        <p>Completed</p>
                        <h2 id="completedAppointments">0</h2>
                    </div>
                </div>

            </section>

            <!-- Schedule card -->
            <section class="appointments-card">

                <div class="appointments-heading">
                    <div>
                        <h2>Daily Schedule</h2>

                        <p>
                            Your appointments arranged by time.
                        </p>
                    </div>

                    <span class="schedule-indicator">
                        <i class="fas fa-circle"></i>
                        Hourly View
                    </span>
                </div>

                <!-- Date navigation -->
                <div class="schedule-toolbar">

                    <div class="date-navigation">

                        <button type="button"
                                class="date-nav-button"
                                id="previousDay"
                                aria-label="Previous day">
                            <i class="fas fa-chevron-left"></i>
                        </button>

                        <input type="date"
                               id="appointmentDate"
                               aria-label="Select appointment date">

                        <button type="button"
                                class="date-nav-button"
                                id="nextDay"
                                aria-label="Next day">
                            <i class="fas fa-chevron-right"></i>
                        </button>

                        <button type="button"
                                class="today-button"
                                id="todayButton">
                            Today
                        </button>

                    </div>

                    <div class="appointment-filter">
                        <i class="fas fa-filter"></i>

                        <select id="scheduleStatusFilter"
                                aria-label="Filter by appointment status">
                            <option value="All">All Statuses</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                </div>

                <div class="selected-date-heading">
                    <div>
                        <h3 id="selectedDateText">Today's Schedule</h3>
                        <p id="appointmentCountText">Loading appointments...</p>
                    </div>
                </div>

                <!-- Hourly timeline generated by JavaScript -->
                <div class="schedule-container">

                    <div class="schedule-timeline" id="scheduleTimeline">
                        <!-- JavaScript inserts hourly rows here -->
                    </div>

                </div>

                <!-- Schedule legend -->
                <div class="schedule-legend">
                    <span>
                        <i class="legend-dot confirmed-dot"></i>
                        Confirmed
                    </span>

                    <span>
                        <i class="legend-dot pending-dot"></i>
                        Pending
                    </span>

                    <span>
                        <i class="legend-dot completed-dot"></i>
                        Completed
                    </span>
                </div>

            </section>

        </div>
    </main>
</div>

<!-- Appointment details modal -->
<div class="appointment-modal"
     id="appointmentModal"
     aria-hidden="true">

    <div class="appointment-modal-content"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modalPatientName">

        <div class="modal-heading">
            <div>
                <span class="page-label">APPOINTMENT DETAILS</span>
                <h2 id="modalPatientName">Patient Name</h2>
                <p id="modalPatientId">Patient ID</p>
            </div>

            <button type="button"
                    class="modal-close"
                    id="closeModal"
                    aria-label="Close appointment details">
                &times;
            </button>
        </div>

        <div class="modal-details">

            <div class="detail-item">
                <span>Appointment ID</span>
                <strong id="modalAppointmentId">-</strong>
            </div>

            <div class="detail-item">
                <span>Date</span>
                <strong id="modalDate">-</strong>
            </div>

            <div class="detail-item">
                <span>Time</span>
                <strong id="modalTime">-</strong>
            </div>

            <div class="detail-item">
                <span>Treatment</span>
                <strong id="modalTreatment">-</strong>
            </div>

            <div class="detail-item">
                <span>Contact Number</span>
                <strong id="modalPhone">-</strong>
            </div>

            <div class="detail-item">
                <span>Status</span>
                <strong id="modalStatus">-</strong>
            </div>

            <div class="detail-item notes-detail">
                <span>Appointment Notes</span>
                <p id="modalNotes">-</p>
            </div>

        </div>

        <div class="modal-footer">
            <button type="button"
                    class="start-visit-button"
                    id="startVisitButton">
                <i class="fa-solid fa-plus"></i> Start New Visit
            </button>

            <button type="button"
                    class="modal-done-button"
                    id="doneButton">
                Close
            </button>
        </div>

    </div>
</div>

<!-- Sample data for JavaScript -->
<script>
    const appointmentData = <?= json_encode(
        $appointments,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ) ?>;
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
<script src="../dentist_schedules.js"></script>

</body>
</html>
