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
    <div class="app-container">
        <?php include '../Components/Sidebar.php'; ?>
        <main class="main-content">
            <?php include '../Components/Header.php'; ?>
            <section class="page-content patient-page">
                <div class="page-heading">
                    <p class="page-label">ACCOUNT</p>
                    <h1>My Profile</h1>
                    <p class="page-description">Keep your contact and personal details up to date.</p>
                </div>

                <div class="profile-layout">
                    <section class="surface-panel">
                        <div class="profile-heading">
                            <div class="profile-avatar" aria-hidden="true">JL</div>
                            <div><h2>Joshua Lee</h2><p>Patient account · Member since 2025</p></div>
                        </div>
                        <div class="profile-notice">Your profile details help the clinic contact you about appointments and care updates.</div>
                        <dl class="detail-list">
                            <div><dt>Patient ID</dt><dd>BS-2025-0148</dd></div>
                            <div><dt>Preferred clinic</dt><dd>BrightSmile Clinic</dd></div>
                            <div><dt>Preferred contact</dt><dd>Email</dd></div>
                        </dl>
                    </section>

                    <section class="surface-panel">
                        <h2>Personal information</h2>
                        <p class="panel-intro">Contact details used for your care</p>
                        <form class="profile-form" id="profile-form">
                            <label>Full name<input name="full-name" value="Joshua Lee" autocomplete="name" required></label>
                            <label>Email address<input name="email" type="email" value="joshua.lee@example.com" autocomplete="email" required></label>
                            <label>Phone number<input name="phone" type="tel" value="+60 12-345 6789" autocomplete="tel" required></label>
                            <label>Date of birth<input name="date-of-birth" type="date" value="1998-04-16" autocomplete="bday"></label>
                            <label>Preferred contact method<select name="contact-method"><option>Email</option><option>Phone</option><option>SMS</option></select></label>
                            <label>Emergency contact<input name="emergency-contact" value="Mei Lee · +60 12-987 6543"></label>
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
    <script>
        document.getElementById('profile-form').addEventListener('submit', (event) => {
            event.preventDefault();
            alert('Profile updates are ready to be connected to the patient account service.');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>