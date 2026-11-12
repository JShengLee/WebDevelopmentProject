<?php
require_once __DIR__ . '/../includes/view_helpers.php';
require_once __DIR__ . '/../includes/patient_bootstrap.php';
$headerName = $currentPatient['FullName'];
$headerRole = $_SESSION['role'] ?? 'Patient';

// Define dynamic dashboard target per role
$dashboardPage = match ($headerRole) {
    'Dentist' => 'dentist_dashboard.php',
    'Admin'   => 'admin_dashboard.php',
    default   => 'index.php',
};

// Map script names to full breadcrumb chains (excluding Dashboard, which is prepended automatically)
$headerBreadcrumbsMap = [
    'appointments.php'           => [['label' => 'Appointments']],
    'book-appointment.php'       => [['label' => 'Appointments', 'url' => 'appointments.php'], ['label' => 'Book Appointment']],
    'contact.php'                => [['label' => 'Contact']],
    'dental-records.php'         => [['label' => 'Dental Records']],
    'orthodontics.php'           => [['label' => 'Orthodontics']],
    'profile.php'                => [['label' => 'My Profile']],
    'services.php'               => [['label' => 'Services']],
    'dentist_schedules.php'      => [['label' => 'Schedules']],
    'dentist_patients.php'       => [['label' => 'Patients']],
    'dentist_patient_record.php' => [['label' => 'Patients', 'url' => 'dentist_patients.php'], ['label' => 'Patient Record']],
    'dentist_profile.php'        => [['label' => 'My Profile']],
    'admin_dentist_list.php'     => [['label' => 'Dentist List']],
    'admin_service_list.php'     => [['label' => 'Service List']],
    'admin_patient_list.php'     => [['label' => 'Patient List']],
    'admin_appointment_list.php'     => [['label' => 'Appointment List']],
    'admin_message_list.php'     => [['label' => 'Message List']],
];

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$pageBreadcrumbs = $headerBreadcrumbsMap[$currentPage] ?? [];

$headerNameParts = preg_split('/\s+/', trim($headerName), -1, PREG_SPLIT_NO_EMPTY);
$headerInitials = strtoupper(
    substr($headerNameParts[0] ?? '', 0, 1) . 
    (count($headerNameParts) > 1 ? substr($headerNameParts[count($headerNameParts) - 1], 0, 1) : '')
);
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

        <nav class="header-breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?= $dashboardPage ?>">Dashboard</a></li>
                <?php foreach ($pageBreadcrumbs as $index => $crumb): ?>
                    <?php if ($index === count($pageBreadcrumbs) - 1): ?>
                        <li aria-current="page"><?= escape_html($crumb['label']) ?></li>
                    <?php else: ?>
                        <li><a href="<?= $crumb['url'] ?>"><?= escape_html($crumb['label']) ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>

        <button 
            class="navbar-toggler burger-icon"
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#mainmenu"
            id="burger-icon"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="middle-logo">
            <div class="mobile-logo">
                <img src="../Logo.png" alt="BrightSmile Dental Clinic Logo">
            </div>
            <p>BrightSmile Dental</p>
        </div>

        <div class="header-right">
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

        <?php if ($headerRole !== 'Dentist'): ?>
            <div class="help-box">
                <div class="help-icon">
                    ?
                </div>
                <div>
                    <strong>Need help?</strong>
                    <span>Contact the clinic</span>
                </div>
            </div>
        <?php endif; ?>

        <a href="logout.php" class="logout-button">
            <span>↪</span>
            <span>Log Out</span>
        </a>
    </div>
</div>

<script>
    // Toggle the burger icon between bars and times
    const button = document.getElementById("burger-icon");

    button.addEventListener("click", function () {
        const icon = button.querySelector("i");
        if (icon) {
            icon.classList.toggle("fa-bars");
            icon.classList.toggle("fa-xmark");
        }
    });
</script>