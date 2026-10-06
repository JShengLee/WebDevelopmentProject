<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Appointments | BrightSmile Dental Clinic</title>
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
                        <p class="page-label">PATIENT CARE</p>
                        <h1>Appointments</h1>
                        <p class="page-description">Review your upcoming visits and appointment history.</p>
                    </div>
                    <a class="primary-button" href="book-appointment.php"><span aria-hidden="true">+</span> Book appointment</a>
                </div>

                <section class="metric-strip" aria-label="Appointment summary">
                    <div><span>UPCOMING</span><strong>1</strong><small>Next visit: 16 Oct 2026</small></div>
                    <div><span>COMPLETED</span><strong>2</strong><small>Visits recorded</small></div>
                    <div><span>REQUESTS</span><strong>1</strong><small>Awaiting confirmation</small></div>
                </section>

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
                                    <option value="pending">Pending</option>
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
                                <tr data-status="confirmed"><td><strong>General check-up</strong><small>Routine examination</small></td><td>Dr. Sarah Lim</td><td>16 Oct 2026<small>10:00 AM</small></td><td>BrightSmile Clinic</td><td><span class="status-pill confirmed">Confirmed</span></td></tr>
                                <tr data-status="pending"><td><strong>Braces adjustment</strong><small>Orthodontics</small></td><td>Dr. Sarah Lim</td><td>23 Oct 2026<small>2:00 PM</small></td><td>BrightSmile Clinic</td><td><span class="status-pill pending">Pending</span></td></tr>
                                <tr data-status="completed"><td><strong>Dental cleaning</strong><small>Preventive care</small></td><td>Dr. Marcus Tan</td><td>21 Aug 2026<small>11:30 AM</small></td><td>BrightSmile Clinic</td><td><span class="status-pill completed">Completed</span></td></tr>
                                <tr data-status="completed"><td><strong>Orthodontic review</strong><small>Treatment review</small></td><td>Dr. Sarah Lim</td><td>24 Jul 2026<small>9:00 AM</small></td><td>BrightSmile Clinic</td><td><span class="status-pill completed">Completed</span></td></tr>
                                <tr data-status="cancelled"><td><strong>Dental consultation</strong><small>General dentistry</small></td><td>Dr. Marcus Tan</td><td>12 Jun 2026<small>3:00 PM</small></td><td>BrightSmile Clinic</td><td><span class="status-pill cancelled">Cancelled</span></td></tr>
                            </tbody>
                        </table>
                        <p class="empty-state" id="appointments-empty" hidden>No appointments match your filters.</p>
                    </div>
                    <div class="table-footnote">Showing 5 appointments</div>
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>