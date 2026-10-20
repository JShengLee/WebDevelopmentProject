<?php 
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
    require_once __DIR__ . '/../includes/role_id_helpers.php';
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "addDoctor"){
        $fullName = trim($_POST["fullName"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $phone = trim($_POST["phone"]);
        $specialty = trim($_POST["specialty"]);
        $deptID = (int) $_POST["deptID"];

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try{
            $pdo->beginTransaction();
            $userID = generate_role_user_id($pdo, 'Dentist');

            $sqlUser = "INSERT INTO user (UserID, FullName, Email, PasswordHash, Phone, Role)
                        VALUES (?, ?, ?, ?, ?, 'Dentist')";
            
            $stmtUser = $pdo->prepare($sqlUser);

            $stmtUser->execute(
                [$userID,
                $fullName,
                $email,
                $passwordHash,
                $phone]
            );

            $sqlDentist = "INSERT INTO dentist (DentistID, UserID, Specialty, DeptID)
                            VALUES (?, ?, ?, ?)";
            
            $stmtDentist = $pdo->prepare($sqlDentist);

            $stmtDentist->execute(
               [ $userID,
                $userID,
                $specialty,
                $deptID]
            );

            $pdo->commit();
            release_role_user_id_lock($pdo);

            echo"<script>
                    alert('Doctor added successfully!');
                    window.location.href = window.location.pathname;    
                </script>";

            exit;
        } catch (Throwable $e){
            if ($pdo->inTransaction()){
                $pdo->rollBack();
            }
            release_role_user_id_lock($pdo);

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to add doctor. Please check the details and try again.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "editDoctor"){
        $userID = (int) trim($_POST["editUserID"]);
        $fullName = trim($_POST["editFullName"]);
        $email = trim($_POST["editEmail"]);
        $phone = trim($_POST["editPhone"]);
        $specialty = trim($_POST["editSpecialty"]);
        $deptID = (int) $_POST["editDeptID"];

        try{
            $pdo->beginTransaction();

            $editUser = "UPDATE user SET FullName = ?, Email = ?, Phone = ? WHERE UserID = ?";

            $stmtEditUser = $pdo->prepare($editUser);

            $stmtEditUser->execute(
                [$fullName,
                $email,
                $phone,
                $userID]
            );

            $editDentist = "UPDATE dentist SET Specialty = ?, DeptID = ? WHERE UserID = ?";

            $stmtEditDentist = $pdo->prepare($editDentist);

            $stmtEditDentist->execute(
                [$specialty,
                $deptID,
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
                    alert('Unable to update doctor. Please check the details and try again.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "deleteDoctor"){
        $userID = (int) trim($_POST["deleteUserID"]);

        try{
            $pdo->beginTransaction();

            $deleteDentist = "DELETE FROM dentist WHERE UserID = ?";

            $stmtDeleteDentist = $pdo->prepare($deleteDentist);

            $stmtDeleteDentist->execute(
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
                    alert('Unable to delete doctor.');
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
        <title>Admin Dentist List | BrightSmile Dental Clinic</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/admin_dentist_list.css?v=6">
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
                    <span class="page-label"> DOCTORS </span>
                    <form method="GET" id="searchForm">
                        <div class="top-container">
                            <div class="form-container">
                                <span class="nav-icon"><i class="fas fa-filter"></i></span>
                                <select name="department" id="department" onchange="this.form.submit()">
                                    <option value="">All Departments</option>

                                    <?php
                                        $deptsql = "SELECT DeptID, DepartmentName FROM department";
                                        $deptresult = $pdo->query($deptsql);
                                        
                                        $departmentList = [];
                                        while ($dept = $deptresult->fetch()){
                                            $departmentList[] = $dept;
                                        }

                                        foreach ($departmentList as $dept):
                                    ?>
                                        <option value="<?= $dept["DeptID"] ?>"
                                            <?= (isset($_GET["department"]) && $_GET["department"] == $dept["DeptID"]) ? "selected" : "" ?>>
                                            <?= $dept["DepartmentName"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-container">           
                                <a onclick="document.getElementById('searchForm').submit();">
                                    <span class="nav-icon">
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
                                <a class="add-doctor-btn" href="#" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                                    + Add Doctor
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="modal fade" id="addDoctorModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Doctor</h5>

                                    <a class="primary-button add-doctor" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>
                                
                                <div class="modal-body">
                                    <form method="POST" id="addDoctorForm">
                                        <input type="hidden" name="action" value="addDoctor">

                                        <!-- <div class="mb-3">
                                            <label for="userID" class="form-label">UserID</label>
                                            <input type="text" class="form-control" id="userID" name="userID" value="" readonly>
                                        </div> -->

                                        <div class="mb-3">
                                            <label for="fullName" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="fullName" name="fullName" required>
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
                                            <label for="phone" class="form-label">Phone No.</label>
                                            <input type="text" class="form-control" id="phone" name="phone">
                                        </div>

                                        <div class="mb-3">
                                            <label for="specialty" class="form-label">Specialty</label>
                                            <input type="text" class="form-control" id="specialty" name="specialty" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="deptID" class="form-label">Department</label>
                                            <select class="form-select" id="deptID" name="deptID" required>
                                                <option value="">Select Department</option>

                                                <?php
                                                    foreach($departmentList as $dept):
                                                ?>
                                                    <option value="<?= $dept["DeptID"] ?>"
                                                        <?= (isset($_GET["department"]) && $_GET["department"] == $dept["DeptID"]) ? "selected" : "" ?>>
                                                        <?= $dept["DepartmentName"] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('addDoctorForm').requestSubmit(); return false;">
                                                    Add Doctor
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- This is for the showing the list of doctors -->
                    <?php
                        $department = $_GET["department"] ?? "";
                        $search = $_GET["search"] ?? "";

                        if ($department !== ""){
                            if ($search !== ""){
                                $sql = "SELECT user.FullName, user.UserID, user.Email, user.Phone, dentist.Specialty, dentist.DeptID, department.DepartmentName 
                                FROM user JOIN dentist ON user.UserID = dentist.UserID 
                                JOIN department ON dentist.DeptID = department.DeptID
                                WHERE department.DeptID = ?
                                AND(
                                user.FullName LIKE ?
                                OR user.UserID = ?
                                OR dentist.Specialty = ?
                                OR dentist.DeptID = ?
                                OR department.DepartmentName = ?
                                )";

                                $searchTerm = "%" . $search . "%";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$department, $searchTerm, $search, $search, $search, $search]);
                            } else {
                                $sql = "SELECT user.FullName, user.UserID, user.Email, user.Phone, dentist.Specialty, dentist.DeptID, department.DepartmentName 
                                FROM user JOIN dentist ON user.UserID = dentist.UserID 
                                JOIN department ON dentist.DeptID = department.DeptID
                                WHERE department.DeptID = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$department]);
                            }
                        } else {
                            if ($search !== ""){
                                $sql = "SELECT user.FullName, user.UserID, user.Email, user.Phone, dentist.Specialty, dentist.DeptID, department.DepartmentName 
                                FROM user JOIN dentist ON user.UserID = dentist.UserID 
                                JOIN department ON dentist.DeptID = department.DeptID
                                WHERE user.FullName LIKE ?
                                OR user.UserID = ?
                                OR dentist.Specialty = ?
                                OR dentist.DeptID = ?
                                OR department.DepartmentName = ?";

                                $searchTerm = "%" . $search . "%";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$searchTerm, $search, $search, $search, $search]);
                            } else { 
                                $sql = "SELECT user.FullName, user.UserID, user.Email, user.Phone, dentist.Specialty, dentist.DeptID, department.DepartmentName 
                                FROM user JOIN dentist ON user.UserID = dentist.UserID 
                                JOIN department ON dentist.DeptID = department.DeptID";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                            }
                        }

                        // $stmt->execute();
                        // $result = $stmt->fetch();
                    ?>

                    <div class="modal fade" id="editDoctorModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Doctor</h5>

                                    <a class="primary-button add-doctor" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="editDoctorForm">
                                        <input type="hidden" name="action" value="editDoctor">

                                        <div class="mb-3">
                                            <label for="editUserID" class="form-label">UserID</label>
                                            <input type="text" class="form-control" id="editUserID" name="editUserID" readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editFullName" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="editFullName" name="editFullName" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editEmail" class="form-label">E-mail</label>
                                            <input type="text" class="form-control" id="editEmail" name="editEmail" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editPhone" class="form-label">Phone No.</label>
                                            <input type="text" class="form-control" id="editPhone" name="editPhone">
                                        </div>

                                        <div class="mb-3">
                                            <label for="editSpecialty" class="form-label">Specialty</label>
                                            <input type="text" class="form-control" id="editSpecialty" name="editSpecialty" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editDeptID" class="form-label">Department</label>
                                            <select class="form-select" id="editDeptID" name="editDeptID" required>
                                                <option value="">Select Department</option>

                                                <?php
                                                    foreach($departmentList as $dept):
                                                ?>
                                                    <option value="<?= $dept["DeptID"] ?>"
                                                        <?= (isset($_GET["department"]) && $_GET["department"] == $dept["DeptID"]) ? "selected" : "" ?>>
                                                        <?= $dept["DepartmentName"] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('editDoctorForm').requestSubmit(); return false;">
                                                    Save Changes
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteDoctorModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Are you sure you want to delete it?</h5>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="deleteDoctorForm">
                                        <input type="hidden" name="action" value="deleteDoctor">
                                        
                                        <input type="hidden" id="deleteUserID" name="deleteUserID">

                                        <div class="delete-form-btn-container">
                                            <a class="primary-delete"
                                                onclick="document.getElementById('deleteDoctorForm').submit();">
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

                    <div class="table-container">
                        <table class="dentist-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Full Name</th>
                                    <th>Specialty</th>
                                    <th>Department ID</th>
                                    <th>Department</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($dentist = $stmt->fetch()): ?>
                                    <tr>
                                        <td><?= $dentist["UserID"]?></td>
                                        <td><?= $dentist["FullName"]?></td>
                                        <td><?= $dentist["Specialty"]?></td>
                                        <td><?= $dentist["DeptID"]?></td>
                                        <td><?= $dentist["DepartmentName"]?></td>

                                        <td>
                                            <div class="row-container">
                                                <a class="row-edit" href="#" data-bs-toggle="modal" data-bs-target="#editDoctorModal"
                                                data-userid="<?=htmlspecialchars($dentist['UserID'])?>"
                                                data-fullname="<?=htmlspecialchars($dentist['FullName'], ENT_QUOTES)?>"
                                                data-specialty="<?=htmlspecialchars($dentist['Specialty'], ENT_QUOTES)?>"
                                                data-deptid="<?=htmlspecialchars($dentist['DeptID'])?>"
                                                data-email="<?=htmlspecialchars($dentist['Email'], ENT_QUOTES)?>"
                                                data-phone="<?=htmlspecialchars($dentist['Phone'] ?? '', ENT_QUOTES)?>"
                                                >
                                                    Edit
                                                </a>

                                                <a class="row-delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteDoctorModal"
                                                data-userid="<?=htmlspecialchars($dentist['UserID'])?>"
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
                    const doctor = this.dataset;

                    document.getElementById("editUserID").value = doctor.userid;
                    document.getElementById("editFullName").value = doctor.fullname;
                    document.getElementById("editEmail").value = doctor.email;
                    document.getElementById("editPhone").value = doctor.phone;
                    document.getElementById("editSpecialty").value = doctor.specialty;
                    document.getElementById("editDeptID").value = doctor.deptid;
                });
            });

            document.querySelectorAll(".row-delete").forEach(button =>{
                button.addEventListener("click", function (){
                    const doctor = this.dataset;

                    document.getElementById("deleteUserID").value = doctor.userid;
                });
            });                       
        </script>
    </body>
</html>