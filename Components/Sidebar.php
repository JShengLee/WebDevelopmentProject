<link rel="stylesheet" href="../Styling/Sidebar.css">

<!-- Sidebar -->
<aside class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <img src="../Logo.png" alt="BrightSmile Dental Clinic Logo">
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <?php include 'SidebarNavItems.php'; ?>
    </nav>

    <!-- Sidebar Bottom -->
    <div class="sidebar-bottom">
        <?php if (($_SESSION['role'] ?? '') !== 'Dentist'): ?>
            <a class="help-box" href="contact.php">
                <div class="help-icon">
                    ?
                </div>

                <div>
                    <strong>Need help?</strong>
                    <span>Contact the clinic</span>
                </div>
            </a>
        <?php endif; ?>

        <a href="logout.php" class="logout-button">
            <span>↪</span>
            <span>Log Out</span>
        </a>
    </div>
</aside>