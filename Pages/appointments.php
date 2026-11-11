<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';
$flash = take_flash();

$upcomingStatement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM appointment a
         INNER JOIN patient p ON p.PatientID = a.PatientID
         INNER JOIN user patient_user ON patient_user.UserID = p.UserID AND patient_user.Role = 'Patient'
         WHERE p.PatientID = :patient_id AND patient_user.UserID = :user_id
             AND LOWER(a.AppointmentStatus) = 'confirmed'
             AND TIMESTAMP(a.AppointmentDate, a.AppointmentTime) >= NOW()"
);
$upcomingStatement->execute(['patient_id' => $currentPatient['PatientID'], 'user_id' => $currentPatient['UserID']]);
$upcomingCount = (int) $upcomingStatement->fetchColumn();
$completedStatement = $pdo->prepare(
    "SELECT COUNT(*)
     FROM appointment a
     INNER JOIN patient p ON p.PatientID = a.PatientID
     INNER JOIN user patient_user ON patient_user.UserID = p.UserID AND patient_user.Role = 'Patient'
     WHERE p.PatientID = :patient_id AND patient_user.UserID = :user_id
         AND LOWER(a.AppointmentStatus) = 'completed'"
);
$completedStatement->execute(['patient_id' => $currentPatient['PatientID'], 'user_id' => $currentPatient['UserID']]);
$completedCount = (int) $completedStatement->fetchColumn();
$cancelledStatement = $pdo->prepare(
    "SELECT COUNT(*)
     FROM appointment a
     INNER JOIN patient p ON p.PatientID = a.PatientID
     INNER JOIN user patient_user ON patient_user.UserID = p.UserID AND patient_user.Role = 'Patient'
     WHERE p.PatientID = :patient_id AND patient_user.UserID = :user_id
         AND LOWER(a.AppointmentStatus) = 'cancelled'"
);
$cancelledStatement->execute(['patient_id' => $currentPatient['PatientID'], 'user_id' => $currentPatient['UserID']]);
$cancelledCount = (int) $cancelledStatement->fetchColumn();
$appointmentStatement = $pdo->prepare(
        'SELECT a.AppointmentID, a.AppointmentDate, a.AppointmentTime, a.AppointmentStatus,
                        s.ServiceName, u.FullName AS DentistName
         FROM appointment a
         INNER JOIN patient p ON p.PatientID = a.PatientID
         INNER JOIN user patient_user ON patient_user.UserID = p.UserID AND patient_user.Role = \'Patient\'
         INNER JOIN service s ON s.ServiceID = a.ServiceID
         INNER JOIN dentist d ON d.DentistID = a.DentistID
         INNER JOIN user u ON u.UserID = d.UserID AND u.Role = \'Dentist\'
         WHERE p.PatientID = :patient_id AND patient_user.UserID = :user_id
         ORDER BY a.AppointmentDate DESC, a.AppointmentTime DESC'
);
$appointmentStatement->execute(['patient_id' => $currentPatient['PatientID'], 'user_id' => $currentPatient['UserID']]);
$appointments = $appointmentStatement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Appointments | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Styling/patient-pages.css?v=7">
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
                        <h1>Appointments</h1>
                        <p class="page-description">Review your upcoming visits and appointment history.</p>
                    </div>
                    <a class="primary-button" href="book-appointment.php"><span aria-hidden="true">+</span> Book appointment</a>
                </div>

                <section class="metric-strip" aria-label="Appointment summary">
                    <div><span>UPCOMING</span><strong><?= $upcomingCount ?></strong><small>Confirmed visits</small></div>
                    <div><span>COMPLETED</span><strong><?= $completedCount ?></strong><small>Visits recorded</small></div>
                    <div><span>CANCELLED</span><strong><?= $cancelledCount ?></strong><small>Cancelled visits</small></div>
                </section>

                <?php if ($flash && $flash['type'] !== 'appointment_success'): ?><p class="empty-state" role="status"><?= escape_html($flash['message']) ?></p><?php endif; ?>
                <section class="table-panel">
                    <div class="table-toolbar">
                        <div>
                            <h2>All appointments</h2>
                            <p>Your scheduled and previous clinic visits</p>
                        </div>
                        <div class="table-filters">
                            <label class="search-field">
                                <span class="visually-hidden">Search appointments</span>
                                <span aria-hidden="true">⌕</span>
                                <input id="appointment-search" type="search" placeholder="Search appointments">
                            </label>
                            <label>
                                <span class="visually-hidden">Filter by status</span>
                                <select id="appointment-status">
                                    <option value="all">All statuses</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="table-scroll">
                        <table class="data-table" id="appointments-table">
                            <thead>
                                <tr><th>Appointment</th><th>Dentist</th><th>Date &amp; time</th><th>Location</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <?php $statusClass = strtolower($appointment['AppointmentStatus']); ?>
                                    <tr data-status="<?= escape_html($statusClass) ?>">
                                        <td><strong><?= escape_html($appointment['ServiceName']) ?></strong><small>BrightSmile Clinic</small></td>
                                        <td><?= escape_html($appointment['DentistName']) ?></td>
                                        <td><?= escape_html(date('d M Y', strtotime($appointment['AppointmentDate']))) ?><small><?= escape_html(date('g:i A', strtotime($appointment['AppointmentTime']))) ?></small></td>
                                        <td>BrightSmile Clinic</td>
                                        <td>
                                            <div class="appointment-status-actions" style="display:flex;gap:5px;">
                                                <span class="status-pill <?= escape_html($statusClass) ?>"><?= escape_html($appointment['AppointmentStatus']) ?></span>
                                                <?php if ($statusClass === 'confirmed'): ?>
                                                <form class="cancel-appointment-form" action="cancel-appointment.php" method="post">
                                                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                                                    <input type="hidden" name="appointment_id" value="<?= (int) $appointment['AppointmentID'] ?>">
                                                    <button class="text-link" type="submit">Cancel</button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="empty-state" id="appointments-empty" hidden>No appointments match your filters.</p>
                    </div>
                    <div class="table-footnote">Showing <?= count($appointments) ?> appointments</div>
                </section>
            </section>
        </main>
    </div>
    <script>
        const searchInput = document.getElementById('appointment-search');
        const statusFilter = document.getElementById('appointment-status');
        const appointmentRows = [...document.querySelectorAll('#appointments-table tbody tr')];
        const emptyState = document.getElementById('appointments-empty');

        function filterAppointments() {
            const query = searchInput.value.trim().toLowerCase();
            const status = statusFilter.value;
            let visibleCount = 0;

            appointmentRows.forEach((row) => {
                const matchesText = row.textContent.toLowerCase().includes(query);
                const matchesStatus = status === 'all' || row.dataset.status === status;
                row.hidden = !(matchesText && matchesStatus);
                if (!row.hidden) visibleCount += 1;
            });

            emptyState.hidden = visibleCount > 0;
        }

        searchInput.addEventListener('input', filterAppointments);
        statusFilter.addEventListener('change', filterAppointments);
        document.querySelectorAll('.cancel-appointment-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm('Are you sure you want to cancel this appointment?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($flash && $flash['type'] === 'appointment_success'): ?>
        <div class="modal fade" id="appointmentSuccessModal" tabindex="-1" aria-labelledby="appointmentSuccessTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="appointmentSuccessTitle">Appointment confirmed</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"><?= escape_html($flash['message']) ?></div>
                    <div class="modal-footer">
                        <button type="button" class="primary-button" data-bs-dismiss="modal">Got it</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            new bootstrap.Modal(document.getElementById('appointmentSuccessModal')).show();
        </script>
    <?php endif; ?>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>