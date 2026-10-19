<?php
require_once __DIR__ . '/../includes/view_helpers.php';
$headerName = $currentPatient['FullName'];
$headerRole = $_SESSION['role'] ?? 'Patient';
$headerBreadcrumbs = [
    'index.php' => 'Dashboard',
    'appointments.php' => 'Appointments',
    'book-appointment.php' => 'Book Appointment',
    'contact.php' => 'Contact',
    'dental-records.php' => 'Dental Records',
    'orthodontics.php' => 'Orthodontics',
    'profile.php' => 'My Profile',
    'services.php' => 'Services',
    "admin_dashboard.php" => "Dashboard",
    "admin_dentist_list.php" => "Dentist List",
    "admin_service_list.php" => "Service List",
    "admin_patient_list.php" => "Patient List"
];
$headerBreadcrumb = $headerBreadcrumbs[basename($_SERVER['SCRIPT_NAME'] ?? '')] ?? '';
$headerNameParts = preg_split('/\s+/', trim($headerName), -1, PREG_SPLIT_NO_EMPTY);
$headerInitials = strtoupper(substr($headerNameParts[0] ?? '', 0, 1)
    . (count($headerNameParts) > 1 ? substr($headerNameParts[count($headerNameParts) - 1], 0, 1) : ''));
?>
<link rel="stylesheet" href="../Styling/header.css">
<link rel="stylesheet" href="../global.css">
<link rel="stylesheet" href="../Styling/app-shell.css">

<div class="header-container">
    <!-- Top header -->
    <header class="top-header">

                    <div class="mobile-logo">
                        <!-- Empty on desktop -->
                    </div>

                    <?php if ($headerBreadcrumb !== ''): ?>
                        <nav class="header-breadcrumb" aria-label="Breadcrumb">
                            <ol>
                                <?php 
                                    if ($headerBreadcrumb !== 'Dashboard'): 
                                        if ($headerRole === "Patient"):
                                ?>
                                        <li><a href="index.php">Dashboard</a></li>
                                    <?php elseif ($headerRole === "Dentist"): ?>
                                        <li><a href="dentist_dashboard.html">Dashboard</a></li>
                                    <?php elseif ($headerRole === "Admin"): ?>
                                        <li><a href="admin_dashboard.php">Dashboard</a></li>
                                <?php
                                        endif; 
                                    endif; 
                                ?>
                                <li aria-current="page"><?= escape_html($headerBreadcrumb) ?></li>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <button 
                            class="navbar-toggler burger-icon"
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#mainmenu"
                            id="burger-icon"><i class="fa-solid fa-bars"></i></button>

                    <div class="middle-logo">
                        <div class="mobile-logo">
                            <img src="../Logo.png" alt="BrightSmile Dental Clinic Logo">
                        </div>
                        <p>BrightSmile Dental</p>
                    </div>

                    <div class="header-right">
                        <!-- Notification -->
                        <button class="notification-button">
                            <span><i class="fa-regular fa-bell"></i></span>
                            <span class="notification-dot"></span>
                        </button>

                        <!-- User -->
                        <div class="user-profile">
                            <div class="user-avatar">
                                <?= escape_html($headerInitials) ?>
                            </div>
                            <div class="user-info">
                                <strong><?= escape_html($headerName) ?></strong>
                                <span><?= escape_html($headerRole) ?></span>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="burger-navbar collapse" id="mainmenu">
                    <?php include "SidebarNavItems.php"; ?>

                    <div class="help-box">
                        <div class="help-icon">
                            ?
                        </div>

                        <div>
                            <strong>Need help?</strong>
                            <span>Contact the clinic</span>
                        </div>
                    </div>

                    <a href="logout.php" class="logout-button">
                        <span>↪</span>
                        <span>Log Out</span>
                    </a>
                </div>
</div>

<script>
    // Toggle the burger icon between bars and times
    const button = document.getElementById("burger-icon");

    button.addEventListener("click", function(){
        const icon = button.querySelector("i");
        if (icon) {
            icon.classList.toggle("fa-bars");
            icon.classList.toggle("fa-xmark");
        }
    });
</script>