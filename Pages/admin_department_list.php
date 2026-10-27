<?php 
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "addDept"){
        $deptName = trim($_POST["deptName"]);

        try{
            $addDept = "INSERT INTO department (DepartmentName)
                        VALUES (?)";

            $stmtAddDept = $pdo->prepare($addDept);

            $stmtAddDept->execute(
                [$deptName]
            );

            echo"<script>
                    alert('Department added successfully!');
                    window.location.href = window.location.pathname;    
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to add department. Please check the details and try again.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "editDept"){
        $deptID = (int) trim($_POST["editDeptID"]);
        $deptName = trim($_POST["editDeptName"]);

        try{
            $editDept = "UPDATE department SET DepartmentName = ? WHERE DeptID = ?";

            $stmtEditDept = $pdo->prepare($editDept);

            $stmtEditDept->execute(
                [$deptName,
                $deptID]
            );

            echo"<script>
                    alert('Changes have been saved.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update department. Please check the details and try again.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "deleteDept"){
        $deptID = (int) trim($_POST["deleteDeptID"]);

        try{

            $deleteDept = "DELETE FROM department WHERE DeptID = ?";

            $stmtDeleteDept = $pdo->prepare($deleteDept);

            $stmtDeleteDept->execute(
                [$deptID]
            );

            echo"<script>
                    alert('The department has been deleted.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to delete department.');
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
        <title>Admin Department List | BrightSmile Dental Clinic</title>

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
                    <span class="page-label"> DEPARTMENT </span>
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
                                <a class="add-doctor-btn" href="#" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                                    + Add Dept
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="modal fade" id="addDeptModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Department</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="addDeptForm">
                                        <input type="hidden" name="action" value="addDept">

                                        <div class="mb-3">
                                            <label for="deptName" class="form-label">Department Name</label>
                                            <input type="text" class="form-control" id="deptName" name="deptName" required>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('addDeptForm').requestSubmit(); return false;">
                                                    Add Department
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
                            FROM department WHERE 
                            DeptID = ?
                            OR DepartmentName LIKE ?";

                            $searchTerm = "%" . $search . "%";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$search, $searchTerm]);
                        } else { 
                            $sql = "SELECT * FROM department ORDER BY DeptID ASC";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                        }
                    ?>

                    <div class="modal fade" id="editDeptModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Department</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="editDeptForm">
                                        <input type="hidden" name="action" value="editDept">

                                        <div class="mb-3">
                                            <label for="editDeptID" class="form-label">Department ID</label>
                                            <input type="text" class="form-control" id="editDeptID" name="editDeptID" readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editDeptName" class="form-label">Department Name</label>
                                            <input type="text" class="form-control" id="editDeptName" name="editDeptName" required>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('editDeptForm').requestSubmit(); return false;">
                                                    Edit Service
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteDeptModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Are you sure you want to delete it?</h5>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="deleteDeptForm">
                                        <input type="hidden" name="action" value="deleteDept">
                                        
                                        <input type="hidden" id="deleteDeptID" name="deleteDeptID">

                                        <div class="delete-form-btn-container">
                                            <a class="primary-delete"
                                                onclick="document.getElementById('deleteDeptForm').submit();">
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
                                    <th>Department ID</th>
                                    <th>Department Name</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($dept = $stmt->fetch()): ?> 
                                    <tr>
                                        <td><?= $dept["DeptID"]?></td>
                                        <td><?= $dept["DepartmentName"]?></td>

                                        <td class="btn-cell">
                                            <div class="row-container">
                                                <a class="row-edit" href="#" data-bs-toggle="modal" data-bs-target="#editDeptModal"
                                                data-deptid="<?=htmlspecialchars($dept["DeptID"])?>"
                                                data-deptname="<?=htmlspecialchars($dept["DepartmentName"], ENT_QUOTES)?>"
                                                >
                                                    Edit
                                                </a>

                                                <a class="row-delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteDeptModal"
                                                data-deptid="<?=htmlspecialchars($dept["DeptID"])?>"
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
                    const dept = this.dataset;

                    document.getElementById("editDeptID").value = dept.deptid;
                    document.getElementById("editDeptName").value = dept.deptname;
                });
            });

            document.querySelectorAll(".row-delete").forEach(button =>{
                button.addEventListener("click", function (){
                    const dept = this.dataset;

                    document.getElementById("deleteDeptID").value = dept.deptid;
                });
            });
        </script>
    </body>
</html>