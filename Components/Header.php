<link rel="stylesheet" href="../Styling/Header.css">

<!-- Top header -->
                <header class="top-header">
                    <div class="mobile-logo">
                        <!-- Empty on desktop -->
                    </div>

                    <button class="navbar-toggler burger-icon"
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#mainmenu"><i class="fa-solid fa-bars"></i></button>

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
                                JL
                            </div>
                            <div class="user-info">
                                <strong>Joshua Lee</strong>
                                <span>Patient</span>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="burger-navbar collapse" id="mainmenu">
                    <?php include 'SidebarNavItems.php'; ?>

                    <div class="help-box">
                        <div class="help-icon">
                            ?
                        </div>

                        <div>
                            <strong>Need help?</strong>
                            <span>Contact the clinic</span>
                        </div>
                    </div>

                    <a href="login.php" class="logout-button">
                        <span>↪</span>
                        <span>Log Out</span>
                    </a>
                </div>