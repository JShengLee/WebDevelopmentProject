<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<p class="nav-title">
                        MAIN MENU
                    </p>

                    <a href="index.php" class="nav-item<?php echo $currentPage === 'index.php' ? ' active' : ''; ?>">
                        <span class="nav-icon"><i class="fas fa-home"></i></span>
                        <span>Dashboard</span>
                    </a>

                    <a href="appointments.php" class="nav-item<?php echo in_array($currentPage, ['appointments.php', 'book-appointment.php'], true) ? ' active' : ''; ?>">
                        <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
                        <span>Appointments</span>
                    </a>

                    <a href="dental-records.php" class="nav-item<?php echo $currentPage === 'dental-records.php' ? ' active' : ''; ?>">
                        <span class="nav-icon"><i class="fas fa-file-medical"></i></span>
                        <span>Dental Records</span>
                    </a>

                    <a href="orthodontics.php" class="nav-item<?php echo $currentPage === 'orthodontics.php' ? ' active' : ''; ?>">
                        <span class="nav-icon"><i class="fas fa-tooth"></i></span>
                        <span>Orthodontics</span>
                    </a>

                    <a href="services.php" class="nav-item<?php echo $currentPage === 'services.php' ? ' active' : ''; ?>">
                        <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
                        <span>Services</span>
                    </a>

                    <!--<a href="contact.php" class="nav-item">
                        <span class="nav-icon"><i class="fas fa-envelope"></i></span>
                        <span>Contact Us</span>
                    </a>-->

                    <p class="nav-title second-title">
                        ACCOUNT
                    </p>

                    <a href="profile.php" class="nav-item<?php echo $currentPage === 'profile.php' ? ' active' : ''; ?>">
                        <span class="nav-icon"><i class="fas fa-user"></i></span>
                        <span>My Profile</span>
                    </a>