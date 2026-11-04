<?php 
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Get current filename safely
    $current_page = basename($_SERVER['PHP_SELF'] ?? '');

    // Prevent fatal PHP crash if function is already declared elsewhere
    if (!function_exists('isActive')) {
        function isActive($pageName, $currentPage) {
            return ($pageName === $currentPage) ? ' active' : '';
        }
    }

    // Determine role & profile page URL
    $role = $_SESSION['role'] ?? '';
    $profile_page = 'profile.php'; // Default fallback

    if ($role === 'Dentist') {
        $profile_page = 'dentist_profile.php';
    } elseif ($role === 'Admin') {
        $profile_page = 'admin_profile.php';
    }
?>

<p class="nav-title">MAIN MENU</p>

<?php if ($role === 'Patient'): ?>

    <a href="index.php" class="nav-item<?= isActive('index.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-home"></i></span>
        <span>Dashboard</span>
    </a>

    <a href="appointments.php" class="nav-item<?= isActive('appointments.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
        <span>Appointments</span>
    </a>

    <a href="dental-records.php" class="nav-item<?= isActive('dental-records.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-file-medical"></i></span>
        <span>Dental Records</span>
    </a>

    <a href="orthodontics.php" class="nav-item<?= isActive('orthodontics.php', $current_page) ?>">
        <span class="nav-icon"><i class="fa-solid fa-teeth"></i></span>
        <span>Orthodontics</span>
    </a>

    <a href="services.php" class="nav-item<?= isActive('services.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-tooth"></i></span>
        <span>Services</span>
    </a>

<?php elseif ($role === 'Dentist'): ?>

    <a href="dentist_dashboard.php" class="nav-item<?= isActive('dentist_dashboard.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-home"></i></span>
        <span>Dashboard</span>
    </a>

    <a href="dentist_schedules.php" class="nav-item<?= isActive('dentist_schedules.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
        <span>Schedules</span>
    </a>

    <a href="dentist_patients.php" class="nav-item<?= isActive('dentist_patients.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-user-injured"></i></span>
        <span>Patients</span>
    </a>

<?php elseif ($role === 'Admin'): ?>

    <a href="admin_dashboard.php" class="nav-item<?= isActive('admin_dashboard.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fa-home"></i></span>
        <span>Dashboard</span>
    </a>

    <a href="admin_dentist_list.php" class="nav-item<?= isActive('admin_dentist_list.php', $current_page) ?>">
        <span class="nav-icon"><i class="fab fa-black-tie"></i></span>
        <span>Dentists</span>
    </a>

<a href="admin_department_list.php" class="nav-item <?= isActive('admin_department_list.php', $current_page) ?>">
    <span class="nav-icon"><i class="fas fa-sitemap"></i></span>
    <span>Department</span>
</a>

<a href="admin_service_list.php" class="nav-item <?= isActive('admin_service_list.php', $current_page) ?>">
    <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
    <span>Services</span>
</a>

    <a href="admin_patient_list.php" class="nav-item<?= isActive('admin_patient_list.php', $current_page) ?>">
        <span class="nav-icon"><i class="fas fas fa-user"></i></span>
        <span>Patients</span>
    </a>

<a href="admin_appointment_list.php" class="nav-item <?= isActive('admin_appointment_list.php', $current_page) ?>">
    <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
    <span>Appointments</span>
</a>

<a href="admin_stock_list.php" class="nav-item <?= isActive('admin_stock_list.php', $current_page) ?>">
    <span class="nav-icon"><i class="fas fa-boxes"></i></span>
    <span>Inventory</span>
</a>

<a href="admin_message_list.php" class="nav-item <?= isActive('admin_message_list.php', $current_page) ?>">
    <span class="nav-icon"><i class="fas fa-envelope"></i></span>
    <span>Messages</span>
</a>

<?php endif; ?>

<?php if ($role !== "Admin"): ?>
<p class="nav-title second-title">ACCOUNT</p>

<a href="<?= htmlspecialchars($profile_page); ?>" class="nav-item<?= isActive($profile_page, $current_page) ?>">
    <span class="nav-icon"><i class="fas fa-user"></i></span>
    <span>My Profile</span>
</a>
<?php endif; ?>