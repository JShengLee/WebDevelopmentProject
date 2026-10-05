<link rel="stylesheet" href="../Styling/header.css">

<div class="header-container">
<!-- Top header -->
                <header class="top-header">
                    <div class="mobile-logo">
                        <!-- Empty on desktop -->
                    </div>

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

                    <a href="authentication.php" class="logout-button">
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