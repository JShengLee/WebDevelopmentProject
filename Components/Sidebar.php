<link rel="stylesheet" href="../Styling/Sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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