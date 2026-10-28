<?php 
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
    require_once __DIR__ . '/../includes/role_id_helpers.php';
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "addPatient"){
        $fullName = trim($_POST["fullName"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $phone = trim($_POST["phone"]);
        $icnum = trim($_POST["icnum"]);
        $gender = trim($_POST["gender"]);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try{
            $pdo->beginTransaction();
            $userID = generate_role_user_id($pdo, 'Patient');

            $addUser = "INSERT INTO user (UserID, FullName, Email, ic_number, PasswordHash, Phone, Role)
                        VALUES (?, ?, ?, ?, ?, ?, 'Patient')";

            $stmtAddUser = $pdo->prepare($addUser);

            $stmtAddUser->execute(
                [$userID,
                $fullName,
                $email,
                preg_replace('/[^0-9]/', '', $icnum),
                $passwordHash,
                $phone]
            );

            $addPatient = "INSERT INTO patient (PatientID, UserID, Gender,Approval)
                        VALUES (?, ?, ?, ?, 'Yes')";
    
            $stmtAddPatient = $pdo->prepare($addPatient);

            $stmtAddPatient->execute(
                [$userID,
                $userID,
                $gender]
            );

            $pdo->commit();

            echo"<script>
                    alert('Patient added successfully!');
                    window.location.href = window.location.pathname;    
                </script>";

            exit;
        } catch (Throwable $e){
            if ($pdo->inTransaction()){
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to add patient. Please check the details and try again.');
                </script>";

            echo($e->getMessage());
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "editPatient"){
        $userID = (int) trim($_POST["editUserID"]);
        $fullName = trim($_POST["editFullName"]);
        $icnum = trim($_POST["editICNumber"]);
        $gender = trim($_POST["editGender"]);
        $email = trim($_POST["editEmail"]);
        $phone = trim($_POST["editPhone"]);

        try{
            $pdo->beginTransaction();

            $editUser = "UPDATE user SET FullName = ?, ic_number = ?, Email = ?, Phone = ? WHERE UserID = ?";

            $stmtEditUser = $pdo->prepare($editUser);

            $stmtEditUser->execute(
                [$fullName,
                preg_replace('/[^0-9]/', '', $icnum),
                $email,
                $phone,
                $userID]
            );

            $editPatient = "UPDATE patient SET Gender = ? WHERE UserID = ?";

            $stmtEditPatient = $pdo->prepare($editPatient);

            $stmtEditPatient->execute(
                [$gender,
                $userID]
            );

            $pdo->commit();

            echo"<script>
                    alert('Changes have been saved.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){
            if ($pdo->inTransaction()){
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update patient. Please check the details and try again.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "deletePatient"){
        $userID = (int) trim($_POST["deleteUserID"]);

        try{
            $pdo->beginTransaction();

            $deletePatient = "DELETE FROM patient WHERE UserID = ?";

            $stmtDeletePatient = $pdo->prepare($deletePatient);

            $stmtDeletePatient->execute(
                [$userID]
            );

            $deleteUser = "DELETE FROM user WHERE UserID = ?";

            $stmtDeleteUser = $pdo->prepare($deleteUser);

            $stmtDeleteUser->execute(
                [$userID]
            );

            $pdo->commit();

            echo"<script>
                    alert('The user has been deleted.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){
            if ($pdo->inTransaction()){
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to delete service.');
                </script>";
        }
    }

    if (($_GET["action"] ?? "") === "approvePatient" && isset($_GET["userID"])){
        $userID = $_GET["userID"];
        try{
            $approvePatient = "UPDATE patient SET Approval = 'Yes' WHERE UserID = ?";

            $stmtApprovePatient = $pdo->prepare($approvePatient);

            $stmtApprovePatient->execute([$userID]);

            echo"<script>
                    alert('The patient has been approved.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update approval.');
                </script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Admin Patient List | BrightSmile Dental Clinic</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/admin_dentist_list.css?v=19">
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
                <?php
                    include '../Components/Header.php';
                ?>
                <!-- Page content -->
                <div class="page-content">
                    <span class="page-label"> PENDING APPROVALS </span>
                    <form method="GET" id="searchForm">
                        <div class="top-container">
                            <div class="form-container">           
                                <a onclick="document.getElementById('searchForm').submit();">
                                    <span class="nav-icon search">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </a>
                                <input 
                                    type="text"
                                    name="search"
                                    id="search"
                                    class="searchBar"
                                    placeholder="Enter search details..."
                                    value="<?= htmlspecialchars($_GET["search"] ?? "") ?>">
                                <a onclick=
                                    "document.getElementById('search').value = ''; 
                                    document.getElementById('searchForm').submit();">
                                    <span class="cancel-search">
                                        <i class="far fa-times-circle"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="modal fade" id="addPatientModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Patient</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="addPatientForm">
                                        <input type="hidden" name="action" value="addPatient">

                                        <div class="mb-3">
                                            <label for="fullName" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="fullName" name="fullName" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="icnum" class="form-label">IC Number</label>
                                            <input type="text" class="form-control" id="icnum" name="icnum" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone No.</label>
                                            <input type="text" class="form-control" id="phone" name="phone">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">E-mail</label>
                                            <input type="text" class="form-control" id="email" name="email" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="text" class="form-control" id="password" name="password" readonly
                                                value="TemporaryPassword123">
                                        </div>

                                        <div class="mb-3">
                                            <label for="gender" class="form-label">Gender</label>
                                            <!-- <input type="text" class="form-control" id="specialty" name="specialty" required> -->
                                             <select class="form-select" id="gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('addPatientForm').requestSubmit(); return false;">
                                                    Add Patient
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        $search = $_GET["search"] ?? "";

                        if ($search !== ""){
                            $sql = "SELECT * 
                            FROM user JOIN patient ON user.UserID = patient.UserID
                            WHERE 
                            (user.UserID = ?
                            OR user.FullName LIKE ?
                            OR user.ICNumber = ?
                            OR patient.DateOfBirth = ?
                            OR patient.PhoneNumber = ?
                            OR patient.Email = ?)
                            AND patient.Approval = 'No'";

                            $searchTerm = "%" . $search . "%";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$search, $searchTerm, $search, $search, $search, $search]);
                        } else { 
                            $sql = "SELECT * FROM user JOIN patient ON user.UserID = patient.UserID WHERE patient.Approval = 'No'";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                        }
                    ?>

                    <div class="modal fade" id="editPatientModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Patient</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="editPatientForm">
                                        <input type="hidden" name="action" value="editPatient">

                                        <div class="mb-3">
                                            <label for="editUserID" class="form-label">User ID</label>
                                            <input type="text" class="form-control" id="editUserID" name="editUserID" readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editFullName" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="editFullName" name="editFullName" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editICNumber" class="form-label">IC Number</label>
                                            <input type="text" class="form-control" id="editICNumber" name="editICNumber" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editGender" class="form-label">Gender</label>
                                            <select class="form-control" id="editGender" name="editGender" required>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editPhone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="editPhone" name="editPhone" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="editEmail" name="editEmail" required>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('editPatientForm').requestSubmit(); return false;">
                                                    Edit Patient
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deletePatientModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Are you sure you want to delete it?</h5>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="deletePatientForm">
                                        <input type="hidden" name="action" value="deletePatient">
                                        
                                        <input type="hidden" id="deleteUserID" name="deleteUserID">

                                        <div class="delete-form-btn-container">
                                            <a class="primary-delete"
                                                onclick="document.getElementById('deletePatientForm').submit();">
                                                    Delete
                                            </a>
                                            
                                            <a class="primary-button" data-bs-dismiss="modal">
                                                    Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- This is for the unapprove patients -->
                    <div class="patient-table-container">
                        <table class="dentist-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Full Name</th>
                                    <th>IC Number</th>
                                    <th>Date Of Birth</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $pending = false;
                                    while ($patient = $stmt->fetch()): 
                                        $pending = true;
                                ?> 
                                    <tr>
                                        <td><?= $patient["UserID"]?></td>
                                        <td><?= $patient["FullName"]?></td>
                                        <td><?= $patient["ic_number"]?></td>
                                        <td><?= $patient["DateOfBirth"]?></td>
                                        <td><?= $patient["Phone"]?></td>
                                        <td><?= $patient["Email"]?></td>
                                        <td>Pending</td>

                                        <td class="btn-cell">
                                            <div class="row-container">
                                                <a class="row-edit" href="?action=approvePatient&userID=<?=$patient['UserID']?>">
                                                    Approve
                                                </a>

                                                <a class="row-delete" href="#" data-bs-toggle="modal" data-bs-target="#deletePatientModal"
                                                data-userid="<?=htmlspecialchars($patient["UserID"])?>"
                                                >
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>

                                <?php if (!$pending): ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center;">
                                            No pending approvals...
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>


                    <br>


                    <!-- This is for the normal patients viewing -->
                    <span class="page-label"> PATIENTS </span>
                    <form method="GET" id="searchForm">
                        <div class="top-container">
                            <div class="form-container">           
                                <a onclick="document.getElementById('searchForm').submit();">
                                    <span class="nav-icon search">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </a>
                                <input 
                                    type="text"
                                    name="search"
                                    id="search"
                                    class="searchBar"
                                    placeholder="Enter search details..."
                                    value="<?= htmlspecialchars($_GET["search"] ?? "") ?>">
                                <a onclick=
                                    "document.getElementById('search').value = ''; 
                                    document.getElementById('searchForm').submit();">
                                    <span class="cancel-search">
                                        <i class="far fa-times-circle"></i>
                                    </span>
                                </a>
                            </div>
                            <div class="add-doctor">
                                <a class="add-doctor-btn" href="#" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                                    + Add Patient
                                </a>
                            </div>
                        </div>
                    </form>

                    <?php
                        $search = $_GET["search"] ?? "";

                        if ($search !== ""){
                            $sql = "SELECT * 
                            FROM user JOIN patient ON user.UserID = patient.PatientID
                            WHERE 
                            (user.UserID = ?
                            OR user.FullName LIKE ?
                            OR patient.DateOfBirth = ?
                            OR user.ICNumber = ?
                            OR patient.PhoneNumber = ?
                            OR patient.Email LIKE ?
                            )
                            AND patient.Approval = 'Yes'";

                            $searchTerm = "%" . $search . "%";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$search, $searchTerm, $search, $search, $search, $searchTerm]);
                        } else { 
                            $sql = "SELECT * FROM user JOIN patient ON user.UserID = patient.UserID WHERE patient.Approval = 'Yes'";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                        }
                    ?>

                    <div class="patient-table-container">
                        <table class="dentist-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Full Name</th>
                                    <th>IC Number</th>
                                    <th>Date Of Birth</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $pending = false;
                                    while ($patient = $stmt->fetch()): 
                                        $pending = true;
                                ?> 
                                    <tr>
                                        <td><?= $patient["UserID"]?></td>
                                        <td><?= $patient["FullName"]?></td>
                                        <td><?= $patient["ic_number"]?></td>
                                        <td><?= $patient["DateOfBirth"]?></td>
                                        <td><?= $patient["Phone"]?></td>
                                        <td><?= $patient["Email"]?></td>

                                        <td class="btn-cell">
                                            <div class="row-container">
                                                <a class="row-edit" href="#" data-bs-toggle="modal" data-bs-target="#editPatientModal"
                                                data-patientid="<?=htmlspecialchars($patient["UserID"])?>"
                                                data-fullname="<?=htmlspecialchars($patient['FullName'], ENT_QUOTES)?>"
                                                data-icnumber="<?=htmlspecialchars($patient['ic_number'], ENT_QUOTES)?>"
                                                data-dateofbirth="<?=htmlspecialchars($patient['DateOfBirth'])?>"
                                                data-phone="<?=htmlspecialchars($patient['Phone'])?>"
                                                data-email="<?=htmlspecialchars($patient['Email'], ENT_QUOTES)?>"
                                                data-gender="<?=htmlspecialchars($patient['Gender'], ENT_QUOTES)?>"
                                                >
                                                    Edit
                                                </a>

                                                <a class="row-delete" href="#" data-bs-toggle="modal" data-bs-target="#deletePatientModal"
                                                data-patientid="<?=htmlspecialchars($patient["UserID"])?>"
                                                >
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
        <script>
            document.querySelectorAll(".row-edit").forEach(button =>{
                button.addEventListener("click", function (){
                    const patient = this.dataset;

                    document.getElementById("editUserID").value = patient.patientid;
                    document.getElementById("editFullName").value = patient.fullname;
                    document.getElementById("editICNumber").value = patient.icnumber;
                    document.getElementById("editPhone").value = patient.phone;
                    document.getElementById("editEmail").value = patient.email;
                    document.getElementById("editGender").value = patient.gender;
                });
            });

            document.querySelectorAll(".row-delete").forEach(button =>{
                button.addEventListener("click", function (){
                    const patient = this.dataset;

                    document.getElementById("deleteUserID").value = patient.patientid;
                });
            });   
        </script>
    </body>
</html>