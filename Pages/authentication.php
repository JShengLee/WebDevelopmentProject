<?php
require_once __DIR__ . '/../includes/patient_helpers.php';
require_once __DIR__ . '/../includes/role_id_helpers.php';
$flash = take_flash();
$activeForm = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';
$loginError = '';
$registrationError = '';
$fullName = '';
$registrationEmail = '';
$icNumber = '';
$phone = '';
$dateOfBirth = '';

function set_login_session_cookie(bool $remember): void
{
    $cookie = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires' => $remember ? time() + (60 * 60 * 24 * 30) : 0,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'] ?? 'Lax',
    ]);
}

function derive_malaysian_ic_date_of_birth(string $icDigits): ?string
{
    if (!preg_match('/^[0-9]{12}$/D', $icDigits)) {
        return null;
    }

    $yearDigits = (int) substr($icDigits, 0, 2);
    $year = $yearDigits <= (int) date('y') ? 2000 + $yearDigits : 1900 + $yearDigits;
    $dateValue = sprintf('%04d-%02d-%02d', $year, (int) substr($icDigits, 2, 2), (int) substr($icDigits, 4, 2));
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateValue);
    $dateErrors = DateTimeImmutable::getLastErrors();

    if (!$date || ($dateErrors && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        || $date->format('Y-m-d') !== $dateValue || $date > new DateTimeImmutable('today')) {
        return null;
    }

    return $dateValue;
}

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
            $patientStatement = $pdo->prepare('SELECT PatientID, Approval FROM patient WHERE UserID = :user_id');
            $patientStatement->execute(['user_id' => $user['UserID']]);
            $patient = $patientStatement->fetch();

            if (!$patient) {
                $loginError = 'This patient account is missing its patient profile. Please contact the clinic.';
            } elseif ($patient['Approval'] !== 'Yes') {
                $loginError = 'Your registration is still pending approval by an administrator.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['UserID'];
                $_SESSION['patient_id'] = (int) $patient['PatientID'];
                $_SESSION['role'] = $user['Role'];
                $_SESSION['full_name'] = $user['FullName'];
                set_login_session_cookie(isset($_POST['remember']) && $_POST['remember'] === '1');
                header('Location: index.php');
                exit;

                if ($user["Role"] === "Admin") {

                    require_once __DIR__ . '/../email_helper.php';

                    $sql = "
                        SELECT 
                            p.PatientID,
                            u.FullName,
                            u.Email,
                            a.*
                        FROM patient p
                        INNER JOIN user u 
                            ON p.UserID = u.UserID
                        INNER JOIN appointment a 
                            ON p.PatientID = a.PatientID
                        WHERE YEAR(a.AppointmentDate) = YEAR(CURDATE())
                        AND a.AppointmentStatus = 'Completed'
                        ORDER BY p.PatientID

                    ";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $patientReports = [];

                    foreach ($records as $record) {

                        $patientID = $record['PatientID'];

                        if (!isset($patientReports[$patientID])) {
                            $patientReports[$patientID] = [
                                'name' => $record['FullName'],
                                'email' => $record['Email'],
                                'records' => []
                            ];
                        }

                        $patientReports[$patientID]['records'][] = $record;
                    }

                    foreach ($patientReports as $patientID => $patient) {
                        $subject = "Your Dental Report - BrightSmile Dental";

                        $body = "
                            <div style='
                                font-family: Arial, sans-serif;
                                background-color: #f6f9fa;
                                padding: 30px;
                                color: #333333;
                            '>

                                <div style='
                                    max-width: 600px;
                                    margin: 0 auto;
                                    background-color: #ffffff;
                                    border-radius: 10px;
                                    overflow: hidden;
                                    border: 1px solid #e5e7eb;
                                '>

                                    <div style='
                                        background-color: #42B1CC;
                                        padding: 25px;
                                        text-align: center;
                                        color: white;
                                    '>
                                        <h2 style='margin: 0;'>
                                            BrightSmile Dental
                                        </h2>
                                        <p style='margin: 8px 0 0;'>
                                            Your Dental Report
                                        </p>
                                    </div>

                                    <div style='padding: 30px;'>

                                        <h2 style='color: #333333;'>
                                            Dental Report
                                        </h2>

                                        <p>
                                            Dear " . htmlspecialchars($patient['name']) . ",
                                        </p>

                                        <p style='line-height: 1.6; color: #555555;'>
                                            Here is your yearly visitation report
                                            from BrightSmile Dental.
                                        </p>

                                        <div style='
                                            background-color: #f0f9fb;
                                            padding: 20px;
                                            border-radius: 8px;
                                            margin: 20px 0;
                                        '>
                                            <h3 style='color: #26869D; margin-top: 0;'>
                                                Summary
                                            </h3>

                                            <p style='margin-bottom: 0;'>
                                                Completed Visits This Year:
                                                <strong>" . count($patient['records']) . "</strong>
                                            </p>
                                        </div>

                                        <p style='line-height: 1.6; color: #555555;'>
                                            Please contact BrightSmile Dental if you have
                                            any questions regarding your dental records.
                                        </p>

                                        <p style='margin-top: 30px;'>
                                            Regards,<br>
                                            <strong>BrightSmile Dental</strong>
                                        </p>

                                    </div>

                                    <div style='
                                        background-color: #f6f9fa;
                                        padding: 15px;
                                        text-align: center;
                                        font-size: 12px;
                                        color: #888888;
                                    '>
                                        &copy; " . date('Y') . " BrightSmile Dental.
                                        All rights reserved.
                                    </div>

                                </div>
                            </div>
                        ";

                        sendEmail(
                            $patient['email'],
                            $subject,
                            $body
                        );
                    }

                    $remindersql = "
                        SELECT 
                            p.PatientID,
                            u.FullName,
                            u.Email,
                            s.ServiceName,
                            a.*
                        FROM patient p
                        INNER JOIN user u 
                            ON p.UserID = u.UserID
                        INNER JOIN appointment a 
                            ON p.PatientID = a.PatientID
                        INNER JOIN service s
                            ON a.ServiceID = s.ServiceID
                        WHERE a.AppointmentDate >= CURDATE()
                        AND a.AppointmentDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        AND a.AppointmentStatus = 'Confirmed'
                        ORDER BY p.PatientID
                    ";

                    $reminderstmt = $pdo->prepare($remindersql);
                    $reminderstmt->execute();

                    $appointments = $reminderstmt->fetchAll(PDO::FETCH_ASSOC);
                    $today = new DateTimeImmutable('today');

                    foreach($appointments as $appointment){
                        $appointmentDate = new DateTimeImmutable(
                            $appointment["AppointmentDate"]
                        );

                        $daysLeft = $today->diff($appointmentDate)->days;

                        $subject = "Upcoming Appointment - BrightSmile Dental";

                        $body = "
                            <div style='
                                font-family: Arial, sans-serif;
                                background-color: #f6f9fa;
                                padding: 30px;
                                color: #333333;
                            '>

                                <div style='
                                    max-width: 600px;
                                    margin: 0 auto;
                                    background-color: #ffffff;
                                    border-radius: 10px;
                                    overflow: hidden;
                                    border: 1px solid #e5e7eb;
                                '>

                                    <div style='
                                        background-color: #42B1CC;
                                        padding: 25px;
                                        text-align: center;
                                        color: white;
                                    '>
                                        <h2 style='margin: 0;'>
                                            BrightSmile Dental
                                        </h2>
                                        <p style='margin: 8px 0 0;'>
                                            Appointment Notification
                                        </p>
                                    </div>

                                    <div style='padding: 30px;'>

                                        <h2 style='color: #333333;'>
                                            Upcoming Dental Appointment
                                        </h2>

                                        <p>
                                            Dear " . htmlspecialchars($appointment['FullName']) . ",
                                        </p>

                                        <p style='line-height: 1.6; color: #555555;'>
                                            This is a reminder that your dental appointment
                                            is scheduled in the next ". $daysLeft ." days.
                                        </p>

                                        <div style='
                                            background-color: #f0f9fb;
                                            padding: 20px;
                                            border-radius: 8px;
                                            margin: 20px 0;
                                        '>
                                            <h3 style='color: #26869D; margin-top: 0;'>
                                                Appointment Details
                                            </h3>

                                            <p>
                                                Appointment:
                                                <strong>" . htmlspecialchars($appointment['ServiceName']) . "</strong>
                                            </p>

                                            <p>
                                                Appointment Date:
                                                <strong>" . htmlspecialchars($appointmentDate->format('d M Y')) . "</strong>
                                            </p>

                                            <p style='margin-bottom: 0;'>
                                                Appointment Time:
                                                <strong>" . htmlspecialchars(date('g:i A', strtotime($appointment['AppointmentTime']))) . "</strong>
                                            </p>
                                        </div>

                                        <p style='line-height: 1.6; color: #555555;'>
                                            Please remember to arrive 10-15 minutes early
                                            for your appointment.
                                        </p>

                                        <p style='line-height: 1.6; color: #555555;'>
                                            If you have any questions or need to make changes
                                            to your appointment, please contact BrightSmile Dental.
                                        </p>

                                        <p style='margin-top: 30px;'>
                                            Regards,<br>
                                            <strong>BrightSmile Dental</strong>
                                        </p>

                                    </div>

                                    <div style='
                                        background-color: #f6f9fa;
                                        padding: 15px;
                                        text-align: center;
                                        font-size: 12px;
                                        color: #888888;
                                    '>
                                        &copy; " . date('Y') . " BrightSmile Dental.
                                        All rights reserved.
                                    </div>

                                </div>
                            </div>
                        ";

                        sendEmail(
                            $appointment['Email'],
                            $subject,
                            $body
                        );
                    }
                }

                header('Location: ' . ($user['Role'] === 'Dentist' ? 'dentist_dashboard.php' : 'admin_dashboard.php'));
                exit;
            }
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['full_name'] = $user['FullName'];
            set_login_session_cookie(isset($_POST['remember']) && $_POST['remember'] === '1');
            header('Location: ' . ($user['Role'] === 'Dentist' ? 'dentist_dashboard.php' : 'admin_dashboard.php'));
            exit;
        }
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $registrationEmail = trim($_POST['email'] ?? '');
        $icNumber = trim($_POST['ic_number'] ?? '');
        $normalizedIcNumber = str_replace('-', '', $icNumber);
        $phone = trim($_POST['phone'] ?? '');
        $validIcFormat = preg_match('/^(?:[0-9]{12}|[0-9]{6}-[0-9]{2}-[0-9]{4})$/D', $icNumber) === 1;
        $dateOfBirth = $validIcFormat ? (derive_malaysian_ic_date_of_birth($normalizedIcNumber) ?? '') : '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullName === '') {
            $registrationError = 'Enter your full name.';
        } elseif (!filter_var($registrationEmail, FILTER_VALIDATE_EMAIL)) {
            $registrationError = 'Enter a valid email address.';
        } elseif (!$validIcFormat) {
            $registrationError = 'Enter a valid 12-digit IC number, with or without hyphens.';
        } elseif ($dateOfBirth === '') {
            $registrationError = 'The IC number must contain a valid date of birth that is not in the future.';
        } elseif ($phone !== '' && preg_match('/^(\+?60|0)1[0-9]-?[0-9]{7,8}$/D', $phone) !== 1) {
            $registrationError = 'Please enter a valid Malaysian phone number (e.g., 012-9876543 or +6012-9876543).';
        } elseif (strlen($password) < 8) {
            $registrationError = 'Password must contain at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $registrationError = 'The passwords do not match.';
        } else {
            try {
                $pdo->beginTransaction();
                $userId = generate_role_user_id($pdo, 'Patient');
                $userStatement = $pdo->prepare(
                    'INSERT INTO user (UserID, FullName, Email, PasswordHash, Phone, ic_number, Role)
                     VALUES (:user_id, :full_name, :email, :password_hash, :phone, :ic_number, \'Patient\')'
                );
                $userStatement->execute([
                    'user_id' => $userId,
                    'full_name' => $fullName,
                    'email' => $registrationEmail,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'phone' => $phone !== '' ? $phone : null,
                    'ic_number' => $normalizedIcNumber,
                ]);
                $patientStatement = $pdo->prepare(
                    'INSERT INTO patient (PatientID, UserID, DateOfBirth, Approval)
                     VALUES (:patient_id, :user_id, :date_of_birth, \'No\')'
                );
                $patientStatement->execute([
                    'patient_id' => $userId,
                    'user_id' => $userId,
                    'date_of_birth' => $dateOfBirth !== '' ? $dateOfBirth : null,
                ]);
                $pdo->commit();
                release_role_user_id_lock($pdo);

                set_flash('success', 'Registration submitted successfully! Your account is currently awaiting admin approval. Please wait until your registration is approved before logging in.');
                header('Location: authentication.php');
                exit;
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                release_role_user_id_lock($pdo);
                $registrationError = (string) $exception->getCode() === '23000'
                    ? 'An account with this email address or IC number already exists.'
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
        <link rel="stylesheet" href="../Styling/authentication.css?v=2">
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
                        </div>

                        <!-- Login Form -->
                        <?php if ($flash || $loginError): ?><div class="security-notice <?= ($loginError !== '' || ($flash['type'] ?? '') !== 'success') ? 'destructive' : 'success' ?>" role="<?= ($loginError !== '' || ($flash['type'] ?? '') !== 'success') ? 'alert' : 'status' ?>"><p><?= escape_html($loginError ?: $flash['message']) ?></p></div><?php endif; ?>
                        <form class="login-form" action="authentication.php" method="post">
                            <input type="hidden" name="form_action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
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

                                    <button type="button" class="forgot-password-button" id="forgot-password-button" aria-expanded="false" aria-controls="forgot-password-notice">
                                        Forgot password?
                                    </button>
                                </div>
                                <div class="forgot-password-notice" id="forgot-password-notice" role="status" hidden>
                                    <p>To reset your password, please contact the administrator at:</p>
                                    <p><strong>Email:</strong> <a href="mailto:hello@brightsmile.com">hello@brightsmile.com</a></p>
                                    <p><strong>Phone:</strong> <a href="tel:+6041234567">+60 4-123 4567</a></p>
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
                            <?php if ($registrationError): ?><div class="security-notice destructive" role="alert"><p><?= escape_html($registrationError) ?></p></div><?php endif; ?>
                            <form class="login-form" action="authentication.php" method="post">
                                <input type="hidden" name="form_action" value="register">
                                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                                <div class="form-group"><label for="full_name">Full Name (as per IC)</label><input id="full_name" name="full_name" value="<?= escape_html($fullName) ?>" placeholder="Enter your full name (e.g. Joshua Lee Jia Sheng)" autocomplete="name" required></div>
                                <div class="form-group"><label for="register_email">Email</label><input id="register_email" name="email" type="email" value="<?= escape_html($registrationEmail) ?>" placeholder="Enter your email (e.g. joshua@example.com)" autocomplete="email" required></div>
                                <div class="form-group"><label for="ic_number">IC Number</label><input id="ic_number" name="ic_number" type="text" value="<?= escape_html($icNumber) ?>" placeholder="Enter your IC number (e.g. 980102105123 or 980102-10-5123)" pattern="(?:[0-9]{12}|[0-9]{6}-[0-9]{2}-[0-9]{4})" maxlength="14" autocomplete="off" required></div>
                                <div class="form-group"><label for="phone">Phone Number</label><input id="phone" name="phone" type="tel" value="<?= escape_html($phone) ?>" placeholder="Enter your phone number (e.g., 012-9876543 or +6012-9876543)" inputmode="tel" pattern="^(\+?60|0)1[0-9]-?[0-9]{7,8}$" title="Please enter a valid Malaysian phone number (e.g., 012-9876543 or +6012-9876543)." autocomplete="tel"></div>
                                <div class="form-group"><label for="date_of_birth">Date of Birth</label><input id="date_of_birth" name="date_of_birth" type="text" value="<?= escape_html($dateOfBirth) ?>" placeholder="Auto-generated from IC number" readonly required></div>
                                <div class="form-group"><label for="register_password">Password</label><input id="register_password" name="password" type="password" minlength="8" autocomplete="new-password" required placeholder="Enter your password"></div>
                                <div class="form-group"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required placeholder="Enter your password again"></div>
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
                            <i class="fa-solid fa-lock"></i>
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
        <?php if (($flash['type'] ?? '') === 'success'): ?>
            alert(<?= json_encode($flash['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
        <?php endif; ?>
        <?php if ($loginError === 'Your registration is still pending approval by an administrator.'): ?>
            alert(<?= json_encode($loginError, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
        <?php endif; ?>

        const icNumberInput = document.getElementById('ic_number');
        const dateOfBirthInput = document.getElementById('date_of_birth');

        function updateDateOfBirth() {
            const prefix = icNumberInput.value.replace(/\D/g, '').slice(0, 6);
            if (prefix.length !== 6) {
                dateOfBirthInput.value = '';
                return;
            }

            const yearDigits = Number(prefix.slice(0, 2));
            const year = yearDigits <= Number(new Date().getFullYear().toString().slice(-2))
                ? 2000 + yearDigits
                : 1900 + yearDigits;
            const month = Number(prefix.slice(2, 4));
            const day = Number(prefix.slice(4, 6));
            const parsedDate = new Date(Date.UTC(year, month - 1, day));
            const dateValue = `${year.toString().padStart(4, '0')}-${prefix.slice(2, 4)}-${prefix.slice(4, 6)}`;
            const today = new Date();
            const todayValue = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            if (parsedDate.getUTCFullYear() !== year || parsedDate.getUTCMonth() !== month - 1
                || parsedDate.getUTCDate() !== day || dateValue > todayValue) {
                dateOfBirthInput.value = '';
                return;
            }

            dateOfBirthInput.value = dateValue;
        }

        icNumberInput.addEventListener('input', updateDateOfBirth);
        updateDateOfBirth();

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

        const forgotPasswordButton = document.getElementById('forgot-password-button');
        const forgotPasswordNotice = document.getElementById('forgot-password-notice');
        forgotPasswordButton.addEventListener('click', () => {
            const isExpanded = forgotPasswordButton.getAttribute('aria-expanded') === 'true';
            forgotPasswordButton.setAttribute('aria-expanded', String(!isExpanded));
            forgotPasswordNotice.hidden = isExpanded;
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