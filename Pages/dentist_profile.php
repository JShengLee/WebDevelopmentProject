<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

// Verify authentication & get current logged-in dentist account details
$currentDentist = require_patient($pdo);
$dentistID = (int)$currentDentist['DentistID'];

// Retrieve flash message stored in session (if any)
$flash = take_flash();
$statusMsg =$flash['message'] ?? '';
$statusType =$flash['type'] ?? '';

// Handle Profile and Password Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action =$_POST['action'] ?? '';

    // --- Action 1: Update Editable Profile Info ---
    if ($action === 'update_profile') {
        $email     = trim($_POST['Email'] ?? '');
        $phone     = trim($_POST['Phone'] ?? '');
        $specialty = trim($_POST['Specialty'] ?? '');

        if (!empty($email)) {
            $pdo->beginTransaction();
            try {
                // Get UserID associated with this dentist
                $getUserStmt = $pdo->prepare("SELECT UserID FROM dentist WHERE DentistID = :dentist_id");
                $getUserStmt->execute(['dentist_id' => $dentistID]);
                $userResult = $getUserStmt->fetch();

                if ($userResult) {
                    $userID = $userResult['UserID'];

                    // Update `user` table (Email, Phone)
                    $stmtUser = $pdo->prepare("UPDATE `user` SET Email = :email, Phone = :phone WHERE UserID = :user_id");
                    $stmtUser->execute([
                        'email'   => $email,
                        'phone'   => $phone,
                        'user_id' => $userID
                    ]);

                    // Update `dentist` table (Specialty)
                    $stmtDentist = $pdo->prepare("UPDATE dentist SET Specialty = :specialty WHERE DentistID = :dentist_id");
                    $stmtDentist->execute([
                        'specialty'  => $specialty,
                        'dentist_id' => $dentistID
                    ]);

                    $pdo->commit();
                    set_flash('success', 'Profile updated successfully!');
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                set_flash('danger', 'Failed to update profile: ' . $e->getMessage());
            }
        } else {
            set_flash('danger', 'Email is required.');
        }

        header("Location: dentist_profile.php");
        exit();
    }

    // --- Action 2: Update Password ---
    if ($action === 'update_password') {
        $currentPassword =$_POST['currentPassword'] ?? '';
        $newPassword     =$_POST['newPassword'] ?? '';
        $confirmPassword =$_POST['confirmPassword'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) { set_flash('danger', 'Please fill in all password fields.'); } elseif (strlen($newPassword) < 8) {
            set_flash('danger', 'New password must be at least 8 characters long.');
        } elseif ($newPassword !==$confirmPassword) {
            set_flash('danger', 'New password and confirmation do not match.');
        } else {
            // Fetch stored password hash using PasswordHash column
            $pwdSql = "SELECT u.UserID, u.PasswordHash 
                       FROM `user` u 
                       JOIN dentist d ON u.UserID = d.UserID 
                       WHERE d.DentistID = :dentist_id";
            $pwdStmt = $pdo->prepare($pwdSql);
            $pwdStmt->execute(['dentist_id' =>$dentistID]);
            $pwdResult =$pwdStmt->fetch();

            if ($pwdResult && password_verify($currentPassword,$pwdResult['PasswordHash'])) {
                // Securely hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updatePwdStmt =$pdo->prepare("UPDATE `user` SET PasswordHash = :password_hash WHERE UserID = :user_id");
                $updated =$updatePwdStmt->execute([
                    'password_hash' => $hashedPassword,
                    'user_id'       => $pwdResult['UserID']
                ]);

                if ($updated) {
                    set_flash('success', 'Password changed successfully!');
                } else {
                    set_flash('danger', 'Failed to update password.');
                }
            } else {
                set_flash('danger', 'Incorrect current password.');
            }
        }

        header("Location: dentist_profile.php");
        exit();
    }
}

// Retrieve updated dentist account data using PDO
$sql = "SELECT
            u.UserID, u.FullName, u.Email, u.Phone, u.ic_number,
            d.DentistID, d.DeptID, d.Specialty, dep.DepartmentName
        FROM dentist d
        JOIN `user` u ON d.UserID = u.UserID
        LEFT JOIN department dep ON d.DeptID = dep.DeptID
        WHERE d.DentistID = :dentist_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['dentist_id' =>$dentistID]);
$dentist =$stmt->fetch();

// Retrieve available departments
$deptStmt =$pdo->query("SELECT DeptID, DepartmentName FROM department ORDER BY DepartmentName ASC");
$departments =$deptStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Dentist Profile | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../Styling/dentist_profile.css">
</head>

<body>

    <div class="app-container">

        <!-- Shared sidebar -->
        <?php include '../Components/Sidebar.php'; ?>

        <main class="main-content">

            <!-- Shared header -->
            <?php include '../Components/Header.php'; ?>

            <div class="page-content profile-page">
                <?php if (!$dentist): ?>
                    <section class="profile-card">
                        <h2>Profile Not Found</h2>
                        <p>The dentist account could not be found in the database.</p>
                    </section>

                <?php else: ?>

                    <!-- Page heading -->
                    <div class="page-heading">
                        <div>
                            <h1>My Profile</h1>
                            <p>Manage your account and professional information.</p>
                        </div>

                        <div class="profile-role">
                            <i class="fa-solid fa-user-doctor"></i>
                            Dentist Account
                        </div>
                    </div>

                    <!-- Dentist summary -->
                    <section class="profile-overview">

                        <div class="profile-avatar">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>

                        <div class="profile-overview-info">
                            <h2 id="displayName">
                                <?= escape_html($dentist["FullName"]) ?>
                            </h2>

                            <p id="displaySpecialty">
                                <?= escape_html($dentist["Specialty"]) ?>
                            </p>

                            <span class="account-status">
                                <span class="status-dot"></span>
                                Active Profile
                            </span>
                        </div>

                        <button type="button" id="editButton" class="edit-button">
                            <i class="fa-solid fa-pen"></i>
                            Edit Profile
                        </button>

                    </section>

                    <form id="profileForm" method="POST" action="dentist_profile.php">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Account information -->
                        <section class="profile-card">

                            <div class="profile-section-heading">
                                <div class="profile-section-icon">
                                    <i class="fa-regular fa-user"></i>
                                </div>

                                <div class="profile-section-text">
                                    <h3>Account Information</h3>
                                    <p>Your registered account details.</p>
                                </div>
                            </div>

                            <div class="profile-grid">
                                <div class="form-group">
                                    <label for="fullName">Full Name</label>
                                    <input
                                        type="text"
                                        id="fullName"
                                        value="<?= escape_html($dentist["FullName"]) ?>"
                                        disabled
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="icNumber">IC Number</label>
                                    <input
                                        type="text"
                                        id="icNumber"
                                        value="<?= escape_html($dentist["ic_number"] ?? '') ?>"
                                        disabled
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="Email"
                                        value="<?= escape_html($dentist["Email"]) ?>"
                                        disabled
                                        required
                                        maxlength="150"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="Phone"
                                        value="<?= escape_html($dentist["Phone"]) ?>"
                                        disabled
                                        maxlength="20"
                                    >
                                </div>

                            </div>
                        </section>

                        <!-- Professional information -->
                        <section class="profile-card">

                            <div class="profile-section-heading">
                                <div class="profile-section-icon">
                                    <i class="fa-solid fa-tooth"></i>
                                </div>

                                <div class="profile-section-text">
                                    <h3>Professional Information</h3>
                                    <p>Your dentist identification and department.</p>
                                </div>
                            </div>

                            <div class="profile-grid">

                                <div class="form-group">
                                    <label for="dentistId">Dentist ID</label>
                                    <input
                                        type="text"
                                        id="dentistId"
                                        value="<?= escape_html((string)$dentist["DentistID"]) ?>"
                                        disabled
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="deptId">Department ID</label>
                                    <input
                                        type="text"
                                        id="deptId"
                                        value="<?= escape_html((string)$dentist["DeptID"]) ?>"
                                        disabled
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="specialty">Specialty</label>
                                    <input
                                        type="text"
                                        id="specialty"
                                        name="Specialty"
                                        value="<?= escape_html($dentist["Specialty"]) ?>"
                                        disabled
                                        maxlength="100"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="departmentName">Department</label>
                                
                                    <select id="departmentName" disabled>
                                        <?php foreach ($departments as$department): ?>
                                            <option
                                                value="<?= (int) $department['DeptID'] ?>"
                                                <?= (int) $department['DeptID'] === (int)$dentist['DeptID']
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                <?= escape_html($department['DepartmentName']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <small>Department assigned to your dentist account.</small>
                                </div>

                            </div>
                        </section>

                        <!-- Edit actions -->
                        <div class="form-actions" id="formActions" hidden>
                            <button
                                type="button"
                                class="cancel-button"
                                id="cancelButton"
                            >
                                Cancel
                            </button>

                            <button type="submit" class="save-button">
                                <i class="fa-solid fa-check"></i>
                                Save Changes
                            </button>
                        </div>

                    </form>

                    <!-- Security section -->
                    <section class="profile-card">

                        <div class="profile-section-heading">
                            <div class="profile-section-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>

                            <div class="profile-section-text">
                                <h3>Account Security</h3>
                                <p>Manage your account password.</p>
                            </div>
                        </div>

                        <div class="security-row">
                            <div class="security-icon">
                                <i class="fa-solid fa-lock"></i>
                            </div>

                            <div class="security-info">
                                <strong>Password</strong>
                                <p>
                                    Update your password to help protect your account.
                                </p>
                            </div>

                            <button
                                type="button"
                                id="passwordButton"
                                class="password-button"
                            >
                                Change Password
                            </button>
                        </div>

                        <div id="passwordPanel" class="password-panel" hidden>
                            <form id="passwordForm" method="POST" action="dentist_profile.php">
                                <input type="hidden" name="action" value="update_password">

                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="currentPassword" name="currentPassword" autocomplete="current-password" required>
                                        <button type="button" class="show-password" data-toggle-password="currentPassword" aria-label="Show password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="newPassword" name="newPassword" autocomplete="new-password" required minlength="8">
                                        <button type="button" class="show-password" data-toggle-password="newPassword" aria-label="Show password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="new-password" required minlength="8">
                                        <button type="button" class="show-password" data-toggle-password="confirmPassword" aria-label="Show password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="password-actions">
                                    <button
                                        type="button"
                                        id="cancelPasswordButton"
                                        class="cancel-button"
                                    >
                                        Cancel
                                    </button>

                                    <button
                                        type="submit"
                                        id="savePasswordButton"
                                        class="save-button"
                                    >
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>

                    </section>

                    <?php if (!empty($statusMsg)): ?>
                        <div class="alert alert-<?= escape_html($statusType) ?> mt-3" role="alert">
                            <?= escape_html($statusMsg) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../dentist_profile.js"></script>
</body>
</html>