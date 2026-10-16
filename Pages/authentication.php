<?php
require_once __DIR__ . '/../includes/patient_helpers.php';
$flash = take_flash();
$activeForm = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';
$loginError = '';
$registrationError = '';
$fullName = '';
$registrationEmail = '';
$phone = '';
$dateOfBirth = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activeForm = ($_POST['form_action'] ?? '') === 'register' ? 'register' : 'login';

    if (!verify_csrf_token()) {
        if ($activeForm === 'register') {
            $registrationError = 'Please submit the registration form again.';
        } else {
            $loginError = 'Please submit the sign-in form again.';
        }
    } elseif ($activeForm === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $statement = $pdo->prepare('SELECT UserID, FullName, PasswordHash, Role FROM user WHERE Email = :email');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['PasswordHash'])) {
            $loginError = 'Email or password is incorrect.';
        } elseif ($user['Role'] === 'Patient') {
            $patientStatement = $pdo->prepare('SELECT PatientID FROM patient WHERE UserID = :user_id');
            $patientStatement->execute(['user_id' => $user['UserID']]);
            $patientId = $patientStatement->fetchColumn();

            if (!$patientId) {
                $loginError = 'This patient account is missing its patient profile. Please contact the clinic.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['UserID'];
                $_SESSION['patient_id'] = (int) $patientId;
                $_SESSION['role'] = $user['Role'];
                $_SESSION['full_name'] = $user['FullName'];
                header('Location: index.php');
                exit;
            }
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['full_name'] = $user['FullName'];
            header('Location: ' . ($user['Role'] === 'Dentist' ? '../dentist_dashboard.html' : '../admin_dashboard.html'));
            exit;
        }
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $registrationEmail = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullName === '' || !filter_var($registrationEmail, FILTER_VALIDATE_EMAIL)) {
            $registrationError = 'Enter your name and a valid email address.';
        } elseif (strlen($password) < 8) {
            $registrationError = 'Password must contain at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $registrationError = 'The passwords do not match.';
        } elseif ($dateOfBirth !== '' && (!DateTime::createFromFormat('!Y-m-d', $dateOfBirth)
            || DateTime::createFromFormat('!Y-m-d', $dateOfBirth)->format('Y-m-d') !== $dateOfBirth)) {
            $registrationError = 'Enter a valid date of birth.';
        } else {
            try {
                $pdo->beginTransaction();
                $userStatement = $pdo->prepare(
                    'INSERT INTO user (FullName, Email, PasswordHash, Phone, Role)
                     VALUES (:full_name, :email, :password_hash, :phone, \'Patient\')'
                );
                $userStatement->execute([
                    'full_name' => $fullName,
                    'email' => $registrationEmail,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'phone' => $phone !== '' ? $phone : null,
                ]);
                $userId = $pdo->lastInsertId();

                $patientStatement = $pdo->prepare(
                    'INSERT INTO patient (UserID, DateOfBirth) VALUES (:user_id, :date_of_birth)'
                );
                $patientStatement->execute([
                    'user_id' => $userId,
                    'date_of_birth' => $dateOfBirth !== '' ? $dateOfBirth : null,
                ]);
                $patientId = $pdo->lastInsertId();
                $pdo->commit();

                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $userId;
                $_SESSION['patient_id'] = (int) $patientId;
                $_SESSION['role'] = 'Patient';
                $_SESSION['full_name'] = $fullName;
                header('Location: index.php');
                exit;
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $registrationError = (string) $exception->getCode() === '23000'
                    ? 'An account with this email address already exists.'
                    : 'Your account could not be created. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Login | BrightSmile Dental Clinic</title>

        <!-- Login Page CSS -->
        <link rel="stylesheet" href="../Styling/authentication.css">
        <link rel="stylesheet" href="../global.css">
    </head>

    <body>
        <main class="login-container">
            <section class="login-section">
                <div class="logo">
                    <div class="logo-icon">
                        <img src="../Logo.png" alt="BrightSmile Dental Clinic Logo">
                    </div>
                    <div class="logo-text">
                        <h1>BrightSmile</h1>
                        <span>Dental Clinic</span>
                    </div>
                </div>

                <div class="login-box">
                    <div class="content-box">
                        <section id="login-panel" <?= $activeForm === 'register' ? 'hidden' : '' ?>>
                        <!-- Login Header -->
                        <div class="login-header">
                            <h2>Sign in to your account</h2>

                            <p>
                                Please enter your details to access the
                                BrightSmile management system.
                            </p>
                        </div>

                        <!-- Login Form -->
                        <?php if ($flash || $loginError): ?><div class="security-notice" role="alert"><p><?= escape_html($loginError ?: $flash['message']) ?></p></div><?php endif; ?>
                        <form class="login-form" action="authentication.php" method="post">
                            <input type="hidden" name="form_action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

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
                        </section>

                        <section id="register-panel" <?= $activeForm === 'login' ? 'hidden' : '' ?>>
                            <div class="login-header">
                                <h2>Create your patient account</h2>
                                <p>Enter your details to get started.</p>
                            </div>
                            <?php if ($registrationError): ?><div class="security-notice" role="alert"><p><?= escape_html($registrationError) ?></p></div><?php endif; ?>
                            <form class="login-form" action="authentication.php" method="post">
                                <input type="hidden" name="form_action" value="register">
                                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                                <div class="form-group"><label for="full_name">Full name</label><input id="full_name" name="full_name" value="<?= escape_html($fullName) ?>" autocomplete="name" required></div>
                                <div class="form-group"><label for="register_email">Email address</label><input id="register_email" name="email" type="email" value="<?= escape_html($registrationEmail) ?>" autocomplete="email" required></div>
                                <div class="form-group"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" value="<?= escape_html($phone) ?>" autocomplete="tel"></div>
                                <div class="form-group"><label for="date_of_birth">Date of birth</label><input id="date_of_birth" name="date_of_birth" type="date" value="<?= escape_html($dateOfBirth) ?>"></div>
                                <div class="form-group"><label for="register_password">Password</label><input id="register_password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
                                <div class="form-group"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required></div>
                                <button type="submit" class="login-button">Create account</button>
                            </form>
                        </section>
                    </div>

                    <div class="public-links" id="login-switch" <?= $activeForm === 'register' ? 'hidden' : '' ?>><span>New to BrightSmile?</span><div class="links"><a href="?mode=register" data-auth-mode="register">Create a patient account</a></div></div>
                    <div class="public-links" id="register-switch" <?= $activeForm === 'login' ? 'hidden' : '' ?>><span>Already registered?</span><div class="links"><a href="?mode=login" data-auth-mode="login">Sign in</a></div></div>
                </div>

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
            </section>
        </main>
    </body>
    <script>
        document.querySelectorAll('[data-auth-mode]').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const showRegistration = link.dataset.authMode === 'register';
                document.getElementById('login-panel').hidden = showRegistration;
                document.getElementById('register-panel').hidden = !showRegistration;
                document.getElementById('login-switch').hidden = showRegistration;
                document.getElementById('register-switch').hidden = !showRegistration;
            });
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showPasswordButton = document.querySelector('#login-panel .show-password');

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