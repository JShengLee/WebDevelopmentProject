<?php 
    if (session_status() !== PHP_SESSION_ACTIVE){
        session_start();
    }
?>

<p class="nav-title">
    MAIN MENU
</p>

<?php if (($_SESSION['role'] ?? '') === 'Patient'): ?>

<a href="index.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-home"></i></span>
    <span>Dashboard</span>
</a>

<a href="appointments.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
    <span>Appointments</span>
</a>

<a href="dental-records.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-file-medical"></i></span>
    <span>Dental Records</span>
</a>

<a href="orthodontics.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-tooth"></i></span>
    <span>Orthodontics</span>
</a>

<a href="services.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
    <span>Services</span>
</a>

<!--<a href="contact.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-envelope"></i></span>
    <span>Contact Us</span>
</a>-->

<?php elseif (($_SESSION['role'] ?? '') === 'Dentist'): ?>

<a href="index.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-home"></i></span>
    <span>Dashboard</span>
</a>

<?php elseif (($_SESSION['role'] ?? '') === 'Admin'): ?>

<a href="admin_dashboard.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-home"></i></span>
    <span>Dashboard</span>
</a>

<a href="admin_dentist_list.php" class="nav-item">
    <span class="nav-icon"><i class="fab fa-black-tie"></i></span>
    <span>Doctors</span>
</a>

<a href="admin_service_list.php" class="nav-item">
    <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
    <span>Services</span>
</a>

<a href="admin_patient_list.php" class="nav-item">
    <span class="nav-icon"><i class="fas fas fa-user"></i></span>
    <span>Patients</span>
</a>

<?php endif; ?>

<p class="nav-title second-title">
    ACCOUNT
</p>

<a href="#" class="nav-item">
    <span class="nav-icon"><i class="fas fa-user"></i></span>
    <span>My Profile</span>
</a>