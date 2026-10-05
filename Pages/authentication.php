<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Login | BrightSmile Dental Clinic</title>

        <!-- Login Page CSS -->
        <link rel="stylesheet" href="../Styling/authentication.css">
    </head>

    <body>
        <main class="login-container">
            <!-- LEFT SIDE - BRANDING -->
            <section class="brand-section">
                <div class="brand-content">
                    <!-- BrightSmile Logo -->
                    <div class="logo">
                        <div class="logo-icon">
                            <img src="../Logo.png" alt="BrightSmile Dental Clinic Logo">
                        </div>
                        <div class="logo-text">
                            <h1>BrightSmile</h1>
                            <span>Dental Clinic</span>
                        </div>
                    </div>

                    <!-- Main Branding Message -->
                    <div class="brand-message">
                        <h2>
                            Your smile.<br>
                            Our care.
                        </h2>
                        <p>
                            A simpler way to manage your dental care.
                            Keep track of your appointments, dental records
                            and treatment progress in one place.
                        </p>
                    </div>

                    <!-- System Highlights -->
                    <div class="system-highlights">
                        <div class="highlight-item">
                            <span class="highlight-check">✓</span>
                            <span>Easy appointment scheduling</span>
                        </div>

                        <div class="highlight-item">
                            <span class="highlight-check">✓</span>
                            <span>Access your dental records</span>
                        </div>

                        <div class="highlight-item">
                            <span class="highlight-check">✓</span>
                            <span>Track your treatment progress</span>
                        </div>
                    </div>
                </div>
            </section>


            <!-- RIGHT SIDE - LOGIN FORM -->
            <section class="login-section">
                <div class="login-box">
                    <!-- Login Header -->
                    <div class="login-header">
                        <span class="welcome-label">
                            WELCOME BACK
                        </span>

                        <h2>Sign in to your account</h2>

                        <p>
                            Please enter your details to access the
                            BrightSmile management system.
                        </p>
                    </div>

                    <!-- Login Form -->
                    <form class="login-form" action="#" method="post">

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">
                                Email Address
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email address"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <div class="password-label">
                                <label for="password">
                                    Password
                                </label>

                                <a href="#">
                                    Forgot password?
                                </a>
                            </div>

                            <div class="password-wrapper">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required
                                >
                                <button
                                    type="button"
                                    class="show-password"
                                    onclick="togglePassword()"
                                    aria-label="Show password"
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="form-options">
                            <label class="remember-me">
                                <input
                                    type="checkbox"
                                    name="remember"
                                >
                                <span>
                                    Remember me
                                </span>
                            </label>
                        </div>

                        <!-- Login Button -->
                        <button
                            type="submit"
                            class="login-button"
                        >
                            Sign In
                        </button>
                    </form>

                    <!-- Security Notice -->
                    <div class="security-notice">

                        <span class="security-icon">
                            🔒
                        </span>
                        <div>
                            <strong>Secure Access</strong>
                            <p>
                                Your account information is protected
                                and only authorised users can access
                                the system.
                            </p>
                        </div>
                    </div>

                    <!-- Public Pages -->
                    <div class="public-links">
                        <span>Need more information?</span>
                        <div class="links">
                            <a href="about.php">About</a>
                            <a href="services.php">Services</a>
                            <a href="contact.php">Contact</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showPasswordButton = document.querySelector('.show-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showPasswordButton.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                showPasswordButton.innerHTML = '<i class="fa-regular fa-eye"></i>';
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</html>