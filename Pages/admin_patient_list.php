<?php 
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "addPatient"){
        $serviceID = (int) trim($_POST["serviceID"]);
        $serviceName = trim($_POST["serviceName"]);
        $description = trim($_POST["description"]);
        $price = (float) trim($_POST["price"]);
        $duration = (int) trim($_POST["duration"]);
        $category = trim($_POST["category"]);

        try{
            $addPatient = "INSERT INTO service (ServiceID, ServiceName, Description, Price, Duration, Category)
                        VALUES (?, ?, ?, ?, ?, ?)";

            $stmtAddPatient = $pdo->prepare($addPatient);

            $stmtAddPatient->execute(
                [$serviceID,
                $serviceName,
                $description,
                $price,
                $duration,
                $category]
            );

            echo"<script>
                    alert('Service added successfully!');
                    window.location.href = window.location.pathname;    
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to add service. Please check the details and try again.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "editPatient"){
        $serviceID = (int) trim($_POST["editServiceID"]);
        $serviceName = trim($_POST["editServiceName"]);
        $description = trim($_POST["editDescription"]);
        $price = (float) trim($_POST["editPrice"]);
        $duration = (int) trim($_POST["editDuration"]);
        $category = trim($_POST["editCategory"]);

        try{
            $editPatient = "UPDATE service SET ServiceName = ?, Description = ?, Price = ?, Duration = ?, Category = ? WHERE ServiceID = ?";

            $stmtEditPatient = $pdo->prepare($editPatient);

            $stmtEditPatient->execute(
                [$serviceName,
                $description,
                $price,
                $duration,
                $category,
                $serviceID]
            );

            echo"<script>
                    alert('Changes have been saved.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update service. Please check the details and try again.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "deletePatient"){
        $serviceID = (int) trim($_POST["deleteServiceID"]);

        try{

            $deletePatient = "DELETE FROM service WHERE serviceID = ?";

            $stmtDeletePatient = $pdo->prepare($deletePatient);

            $stmtDeletePatient->execute(
                [$serviceID]
            );

            echo"<script>
                    alert('The service has been deleted.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){

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
                    alert('The service has been deleted.');
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
        <title>Admin Service List | BrightSmile Dental Clinic</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/admin_dentist_list.css?v=9">
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
                        </div>
                    </form>

                    <div class="modal fade" id="addPatientModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Patient</h5>

                                    <a class="primary-button add-doctor" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="addPatientForm">
                                        <input type="hidden" name="action" value="addPatient">

                                        <div class="mb-3">
                                            <label for="serviceID" class="form-label">ServiceID</label>
                                            <input type="text" class="form-control" id="serviceID" name="serviceID" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="serviceName" class="form-label">Service Name</label>
                                            <input type="text" class="form-control" id="serviceName" name="serviceName" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="description" name="description" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price (RM)</label>
                                            <input type="number" class="form-control" id="price" name="price" step="any" min="0" required value="0.00">
                                        </div>

                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Duration</label>
                                            <input type="text" class="form-control" id="duration" name="duration" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" name="category" required>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('addPatientForm').requestSubmit(); return false;">
                                                    Add Service
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- This is for the showing the list of doctors -->
                    <?php
                        $search = $_GET["search"] ?? "";

                        if ($search !== ""){
                            $sql = "SELECT * 
                            FROM user JOIN patient ON user.UserID = patient.PatientID
                            WHERE user.UserID = ?
                            OR user.FullName LIKE ?
                            OR patient.DateOfBirth = ?
                            OR patient.Address LIKE ?
                            WHERE patient.Approval = 'No'";

                            $searchTerm = "%" . $search . "%";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$search, $searchTerm, $search, $searchTerm]);
                        } else { 
                            $sql = "SELECT * FROM user JOIN patient ON user.UserID = patient.PatientID WHERE patient.Approval = 'No'";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                        }
                    ?>

                    <div class="modal fade" id="editPatientModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Patient</h5>

                                    <a class="primary-button add-doctor" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="editPatientForm">
                                        <input type="hidden" name="action" value="editService">

                                        <div class="mb-3">
                                            <label for="editServiceID" class="form-label">ServiceID</label>
                                            <input type="text" class="form-control" id="editServiceID" name="editServiceID" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editServiceName" class="form-label">Service Name</label>
                                            <input type="text" class="form-control" id="editServiceName" name="editServiceName" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editDescription" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="editDescription" name="editDescription" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editPrice" class="form-label">Price (RM)</label>
                                            <input type="number" class="form-control" id="editPrice" name="editPrice" step="any" min="0" required value="0.00">
                                        </div>

                                        <div class="mb-3">
                                            <label for="editDuration" class="form-label">Duration</label>
                                            <input type="text" class="form-control" id="editDuration" name="editDuration" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editCategory" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="editCategory" name="editCategory" required>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('editPatientForm').requestSubmit(); return false;">
                                                    Edit Service
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
                                        
                                        <input type="hidden" id="deleteServiceID" name="deleteServiceID">

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
                                    <th>Date Of Birth</th>
                                    <th>Address</th>
                                    <th>Approval</th>
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
                                        <td><?= $patient["DateOfBirth"]?></td>
                                        <td class="description-cell"><?= $patient["Address"]?></td>
                                        <td><?= $patient["Approval"]?></td>

                                        <td>
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
                                        <td colspan="6" style="text-align:center;">
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
                            WHERE user.UserID = ?
                            OR user.FullName LIKE ?
                            OR patient.DateOfBirth = ?
                            OR patient.Address LIKE ?
                            WHERE patient.Approval = 'Yes'";

                            $searchTerm = "%" . $search . "%";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$search, $searchTerm, $search, $searchTerm]);
                        } else { 
                            $sql = "SELECT * FROM user JOIN patient ON user.UserID = patient.PatientID WHERE patient.Approval = 'Yes'";
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
                                    <th>Date Of Birth</th>
                                    <th>Address</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($patient = $stmt->fetch()): ?> 
                                    <tr>
                                        <td><?= $patient["UserID"]?></td>
                                        <td><?= $patient["FullName"]?></td>
                                        <td><?= $patient["DateOfBirth"]?></td>
                                        <td class="description-cell"><?= $patient["Address"]?></td>

                                        <td>
                                            <div class="row-container">
                                                <a class="row-edit" href="#" data-bs-toggle="modal" data-bs-target="#editPatientModal"
                                                data-serviceid="<?=htmlspecialchars($service["ServiceID"])?>"
                                                data-servicename="<?=htmlspecialchars($service['ServiceName'], ENT_QUOTES)?>"
                                                data-description="<?=htmlspecialchars($service['Description'], ENT_QUOTES)?>"
                                                data-price="<?=htmlspecialchars($service['Price'])?>"
                                                data-duration="<?=htmlspecialchars($service['Duration'])?>"
                                                data-category="<?=htmlspecialchars($service['Category'], ENT_QUOTES)?>"
                                                >
                                                    Edit
                                                </a>

                                                <a class="row-delete" href="#" data-bs-toggle="modal" data-bs-target="#deletePatientModal"
                                                data-serviceid="<?=htmlspecialchars($service["ServiceID"])?>"
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
                    const service = this.dataset;

                    document.getElementById("editServiceID").value = service.serviceid;
                    document.getElementById("editServiceName").value = service.servicename;
                    document.getElementById("editDescription").value = service.description;
                    document.getElementById("editPrice").value = parseFloat(service.price || 0).toFixed(2);
                    document.getElementById("editDuration").value = service.duration;
                    document.getElementById("editCategory").value = service.category;
                });
            });

            document.querySelectorAll(".row-delete").forEach(button =>{
                button.addEventListener("click", function (){
                    const service = this.dataset;

                    document.getElementById("deleteServiceID").value = service.serviceid;
                });
            });   
            
            const priceInput = document.getElementById("price");
            priceInput.addEventListener("keydown", function(event){
                if (event.key === "ArrowUp" || event.key === "ArrowDown"){
                    event.preventDefault();

                    const current = parseFloat(this.value) || 0;
                    const change = event.key === "ArrowUp" ? 1 : -1;

                    this.value = Math.max(0, current + change).toFixed(2);
                    this.dispatchEvent(new Event("input", {bubbles: true}));
                }
            });
        </script>
    </body>
</html>