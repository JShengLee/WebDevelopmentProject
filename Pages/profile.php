<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';
$flash = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $gender = trim($_POST['gender'] ?? '');

        if (!verify_csrf_token()) {
            set_flash('error', 'Your session expired. Please try again.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Enter a valid email.');
        } elseif ($phone !== '' && preg_match('/^[0-9]{10,11}$/D', $phone) !== 1) {
            set_flash('error', 'Enter a phone number containing 10 or 11 digits only.');
        } elseif (!in_array($gender, ['', 'Male', 'Female'], true)) {
            set_flash('error', 'Select a valid gender.');
        } else {
            try {
                $pdo->beginTransaction();
                $userUpdate = $pdo->prepare('UPDATE user SET Email = :email, Phone = :phone WHERE UserID = :user_id');
                $userUpdate->execute([
                    'email' => $email,
                    'phone' => $phone !== '' ? $phone : null,
                    'user_id' => $currentPatient['UserID'],
                ]);
                $patientUpdate = $pdo->prepare('UPDATE patient SET Gender = :gender WHERE PatientID = :patient_id');
                $patientUpdate->execute([
                    'gender' => $gender !== '' ? $gender : null,
                    'patient_id' => $currentPatient['PatientID'],
                ]);
                $pdo->commit();
                set_flash('success', 'Your profile was updated.');
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                set_flash('error', $exception->getCode() === '23000'
                    ? 'That email is already in use.'
                    : 'Your profile could not be updated. Please try again.');
            }
        }
    } elseif ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!verify_csrf_token()) {
            set_flash('error', 'Your session expired. Please try again.');
        } elseif ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            set_flash('error', 'Complete all password fields.');
        } elseif (strlen($newPassword) < 8) {
            set_flash('error', 'New password must be at least 8 characters long.');
        } elseif ($newPassword !== $confirmPassword) {
            set_flash('error', 'New password and confirmation do not match.');
        } else {
            $passwordStatement = $pdo->prepare('SELECT PasswordHash FROM user WHERE UserID = :user_id');
            $passwordStatement->execute(['user_id' => $currentPatient['UserID']]);
            $passwordHash = $passwordStatement->fetchColumn();

            if ($passwordHash === false || !password_verify($currentPassword, $passwordHash)) {
                set_flash('error', 'Your current password is incorrect.');
            } else {
                $passwordUpdate = $pdo->prepare('UPDATE user SET PasswordHash = :password_hash WHERE UserID = :user_id');
                $passwordUpdate->execute([
                    'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'user_id' => $currentPatient['UserID'],
                ]);
                set_flash('success', 'Your password was changed.');
            }
        }
    }

    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>My Profile | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../Styling/dentist_profile.css">
</head>
<body>
    <!-- Main application -->
    <div class="app-container">

        <!-- Sidebar -->
        <?php
            include '../Components/Sidebar.php';
        ?>

        <!-- Main content -->
        <main class="main-content">

            <!-- Header -->
            <?php
                include '../Components/Header.php';
            ?>

            <!-- Page content -->
            <div class="page-content profile-page">
                <div class="page-heading">
                    <h1>My Profile</h1>
                    <p>Manage your account and personal information.</p>
                </div>

                <section class="profile-overview">
                    <div class="profile-avatar" aria-hidden="true">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="profile-overview-info">
                        <h2 id="displayName"><?= escape_html($currentPatient['FullName']) ?></h2>
                        <span class="account-status"><span class="status-dot"></span>Active Profile</span>
                    </div>
                    <button type="button" id="editButton" class="edit-button">
                        <i class="fa-solid fa-pen"></i> Edit Profile
                    </button>
                </section>

                <form id="profileForm" method="POST" action="profile.php">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                    <section class="profile-card">
                        <div class="profile-section-heading">
                            <div class="profile-section-icon"><i class="fa-regular fa-user"></i></div>
                            <div class="profile-section-text">
                                <h3>Account Information</h3>
                                <p>Your registered account and contact details.</p>
                            </div>
                        </div>

                        <div class="profile-grid">
                            <div class="form-group">
                                <label for="fullName">Full Name</label>
                                <input type="text" id="fullName" value="<?= escape_html($currentPatient['FullName']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="icNumber">IC Number</label>
                                <input type="text" id="icNumber" value="<?= escape_html($currentPatient['ic_number'] ?? '') ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= escape_html($currentPatient['Email']) ?>" autocomplete="email" maxlength="150" disabled required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?= escape_html($currentPatient['Phone'] ?? '') ?>" autocomplete="tel" inputmode="numeric" pattern="[0-9]{10,11}" maxlength="11" title="Enter 10 or 11 digits" disabled>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" disabled>
                                    <option value="" <?= empty($currentPatient['Gender']) ? 'selected' : '' ?>>Select Gender</option>
                                    <option value="Male" <?= ($currentPatient['Gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($currentPatient['Gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dateOfBirth">Date of Birth</label>
                                <input type="date" id="dateOfBirth" value="<?= escape_html($currentPatient['DateOfBirth'] ?? '') ?>" autocomplete="bday" disabled>
                            </div>
                        </div>
                    </section>

                    <div class="form-actions" id="formActions" hidden>
                        <button type="button" class="cancel-button" id="cancelButton">Cancel</button>
                        <button type="submit" class="save-button"><i class="fa-solid fa-check"></i> Save Changes</button>
                    </div>
                </form>

                <section class="profile-card">
                    <div class="profile-section-heading">
                        <div class="profile-section-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="profile-section-text">
                            <h3>Account Security</h3>
                            <p>Manage your account password.</p>
                        </div>
                    </div>

                    <div class="security-row">
                        <div class="security-icon"><i class="fa-solid fa-lock"></i></div>
                        <div class="security-info">
                            <strong>Password</strong>
                            <p>Update your password to help protect your account.</p>
                        </div>
                        <button type="button" id="passwordButton" class="password-button">Change Password</button>
                    </div>

                    <div id="passwordPanel" class="password-panel" hidden>
                        <form method="POST" action="profile.php">
                            <input type="hidden" name="action" value="update_password">
                            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                            <div class="profile-grid">
                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="currentPassword" name="current_password" autocomplete="current-password" required>
                                        <button type="button" class="show-password" data-toggle-password="currentPassword" aria-label="Show password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="newPassword" name="new_password" autocomplete="new-password" minlength="8" required>
                                        <button type="button" class="show-password" data-toggle-password="newPassword" aria-label="Show password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="confirmPassword" name="confirm_password" autocomplete="new-password" minlength="8" required>
                                        <button type="button" class="show-password" data-toggle-password="confirmPassword" aria-label="Show password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="password-actions">
                                <button type="button" id="cancelPasswordButton" class="cancel-button">Cancel</button>
                                <button type="submit" class="save-button">Update Password</button>
                            </div>
                        </form>
                    </div>
                </section>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> mt-3" role="status">
                        <?= escape_html($flash['message']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../patient_profile.js"></script>
</body>
</html>