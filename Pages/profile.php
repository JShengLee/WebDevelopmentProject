<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';
$flash = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!verify_csrf_token() || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Enter a valid name and email address, then try again.');
    } else {
        try {
            $pdo->beginTransaction();
            $userUpdate = $pdo->prepare('UPDATE user SET FullName = :full_name, Email = :email, Phone = :phone WHERE UserID = :user_id');
            $userUpdate->execute([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'user_id' => $currentPatient['UserID'],
            ]);
            $patientUpdate = $pdo->prepare('UPDATE patient SET DateOfBirth = :date_of_birth, Gender = :gender, Address = :address WHERE PatientID = :patient_id');
            $patientUpdate->execute([
                'date_of_birth' => $dateOfBirth !== '' ? $dateOfBirth : null,
                'gender' => $gender !== '' ? $gender : null,
                'address' => $address !== '' ? $address : null,
                'patient_id' => $currentPatient['PatientID'],
            ]);
            $pdo->commit();
            $_SESSION['full_name'] = $fullName;
            set_flash('success', 'Your profile was updated.');
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            set_flash('error', $exception->getCode() === '23000'
                ? 'That email address is already in use.'
                : 'Your profile could not be updated. Please try again.');
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
    <link rel="stylesheet" href="../Styling/patient-pages.css">
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
            <section class="page-content patient-page">
                <div class="page-heading">
                    <h1>My Profile</h1>
                    <p class="page-description">Keep your contact and personal details up to date.</p>
                </div>

                <div class="profile-layout">
                    <section class="surface-panel">
                        <div class="profile-heading">
                            <div class="profile-avatar" aria-hidden="true"><?= escape_html(strtoupper(substr($currentPatient['FullName'], 0, 1))) ?></div>
                            <div><h2><?= escape_html($currentPatient['FullName']) ?></h2><p>Patient account</p></div>
                        </div>
                        <?php if ($flash): ?><div class="profile-notice" role="status"><?= escape_html($flash['message']) ?></div><?php endif; ?>
                        <div class="profile-notice">Your profile details help the clinic contact you about appointments and care updates.</div>
                        <dl class="detail-list">
                            <div><dt>Patient ID</dt><dd><?= (int) $currentPatient['PatientID'] ?></dd></div>
                            <div><dt>Preferred clinic</dt><dd>BrightSmile Clinic</dd></div>
                            <div><dt>Preferred contact</dt><dd>Email</dd></div>
                        </dl>
                    </section>

                    <section class="surface-panel">
                        <h2>Personal information</h2>
                        <p class="panel-intro">Contact details used for your care</p>
                        <form class="profile-form" id="profile-form" action="profile.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                            <label>Full name<input name="full_name" value="<?= escape_html($currentPatient['FullName']) ?>" autocomplete="name" required></label>
                            <label>Email address<input name="email" type="email" value="<?= escape_html($currentPatient['Email']) ?>" autocomplete="email" required></label>
                            <label>Phone number<input name="phone" type="tel" value="<?= escape_html($currentPatient['Phone']) ?>" autocomplete="tel"></label>
                            <label>Date of birth<input name="date_of_birth" type="date" value="<?= escape_html($currentPatient['DateOfBirth']) ?>" autocomplete="bday"></label>
                            <label>Gender<input name="gender" value="<?= escape_html($currentPatient['Gender']) ?>"></label>
                            <label>Address<input name="address" value="<?= escape_html($currentPatient['Address']) ?>" autocomplete="street-address"></label>
                            <div class="form-actions">
                                <button class="secondary-button" type="reset">Discard changes</button>
                                <button class="primary-button" type="submit">Save profile</button>
                            </div>
                        </form>
                    </section>
                </div>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>