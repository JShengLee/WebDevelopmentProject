<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Patient Dashboard | BrightSmile Dental Clinic</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/patient_homepage.css">
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
                            <span class="page-label">
                                PATIENT DASHBOARD
                            </span>
                            <h1>
                                Good day, Joshua 👋
                            </h1>
                            <p>
                                Here's a quick overview of your dental care.
                            </p>
                        </div>

                        <a href="book-appointment.php" class="primary-button">
                            + Book Appointment
                        </a>
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
                                16 Oct
                            </h2>

                            <p class="card-description">
                                10:00 AM · General Check-up
                            </p>
                        </div>

                        <!-- Treatment Progress -->
                        <div class="summary-card">
                            <div class="card-top">
                                <div class="card-icon treatment-icon">
                                    %
                                </div>

                                <span class="card-status">
                                    Ongoing
                                </span>
                            </div>

                            <p class="card-label">
                                TREATMENT PROGRESS
                            </p>

                            <h2>
                                65%
                            </h2>

                            <div class="progress-bar">
                                <div class="progress-value"></div>
                            </div>

                            <p class="card-description">
                                Orthodontic treatment
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
                                4
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
                                <div class="doctor-avatar">
                                    DR
                                </div>

                                <div class="doctor-info">
                                    <strong>
                                        Dr. Sarah Lim
                                    </strong>

                                    <span>
                                        General Dentistry
                                    </span>
                                </div>

                                <div class="appointment-info">
                                    <strong>
                                        16 October 2026
                                    </strong>

                                    <span>
                                        10:00 AM · BrightSmile Clinic
                                    </span>
                                </div>
                            </div>

                            <div class="appointment-status">
                                <span class="status-dot"></span>
                                Confirmed
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
                            <div class="activity-item">
                                <div class="activity-marker">
                                    ✓
                                </div>

                                <div class="activity-content">
                                    <strong>
                                        Dental check-up completed
                                    </strong>

                                    <span>
                                        General dental examination
                                    </span>
                                </div>

                                <time>
                                    08 Sep 2026
                                </time>
                            </div>

                            <div class="activity-item">
                                <div class="activity-marker">
                                    +
                                </div>

                                <div class="activity-content">
                                    <strong>
                                        Appointment confirmed
                                    </strong>

                                    <span>
                                        General check-up with Dr. Sarah Lim
                                    </span>
                                </div>

                                <time>
                                    05 Sep 2026
                                </time>
                            </div>

                            <div class="activity-item">
                                <div class="activity-marker">
                                    ◇
                                </div>

                                <div class="activity-content">
                                    <strong>
                                        Orthodontic record updated
                                    </strong>

                                    <span>
                                        Braces adjustment visit recorded
                                    </span>
                                </div>

                                <time>
                                    28 Aug 2026
                                </time>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
    </body>
</html>