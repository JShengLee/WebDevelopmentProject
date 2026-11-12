<?php 
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "addService"){
        $serviceName = trim($_POST["serviceName"]);
        $description = trim($_POST["description"]);
        $price = (float) trim($_POST["price"]);
        $duration = (int) trim($_POST["duration"]);
        $category = trim($_POST["category"]);

        try{
            $addService = "INSERT INTO service (ServiceName, Description, Price, Duration, Category)
                        VALUES (?, ?, ?, ?, ?)";

            $stmtAddService = $pdo->prepare($addService);

            $stmtAddService->execute(
                [$serviceName,
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

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "editService"){
        $serviceID = (int) trim($_POST["editServiceID"]);
        $serviceName = trim($_POST["editServiceName"]);
        $description = trim($_POST["editDescription"]);
        $price = (float) trim($_POST["editPrice"]);
        $duration = (int) trim($_POST["editDuration"]);
        $category = trim($_POST["editCategory"]);

        try{
            $editService = "UPDATE service SET ServiceName = ?, Description = ?, Price = ?, Duration = ?, Category = ? WHERE ServiceID = ?";

            $stmtEditService = $pdo->prepare($editService);

            $stmtEditService->execute(
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

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "deleteService"){
        $serviceID = (int) trim($_POST["deleteServiceID"]);

        try{

            $deleteService = "DELETE FROM service WHERE serviceID = ?";

            $stmtDeleteService = $pdo->prepare($deleteService);

            $stmtDeleteService->execute(
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
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Admin Service List | BrightSmile Dental Clinic</title>

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
                    <span class="page-label"> SERVICES </span>
                    <form method="GET" id="searchForm">
                        <div class="top-container">
                            <div class="form-container">
                                <span class="nav-icon"><i class="fas fa-filter"></i></span>
                                <select class="filter" name="category" id="category" onchange="this.form.submit()">
                                    <option value="">All Category</option>

                                    <?php
                                        $catesql = "SELECT DISTINCT Category FROM service ORDER BY Category ASC";
                                        $cateresult = $pdo->query($catesql);
                                        
                                        $cateList = [];
                                        while ($cate = $cateresult->fetch()){
                                            $cateList[] = $cate;
                                        }

                                        foreach ($cateList as $cate):
                                    ?>
                                        <option value="<?= $cate["Category"] ?>"
                                            <?= (isset($_GET["category"]) && $_GET["category"] == $cate["Category"]) ? "selected" : "" ?>>
                                            <?= $cate["Category"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

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
                                <a class="add-doctor-btn" href="#" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                    + Add Service
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="modal fade" id="addServiceModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Service</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="addServiceForm">
                                        <input type="hidden" name="action" value="addService">

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
                                            <label for="duration" class="form-label">Duration (minutes)</label>
                                            <input type="number" class="form-control" id="duration" name="duration" min="1" step="1" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" name="category" list="categoryOptions" required>

                                            <datalist id="categoryOptions">
                                                <?php
                                                    $addcatesql = "SELECT DISTINCT Category FROM service ORDER BY Category ASC";
                                                    $addcateresult = $pdo->query($addcatesql);
                                                    
                                                    $cateList = [];
                                                    while ($cate = $addcateresult->fetch()){
                                                        $cateList[] = $cate;
                                                    }

                                                    foreach ($cateList as $cate):
                                                ?>
                                                <option value="<?=$cate["Category"]?>">
                                                <?php endforeach;?>
                                            </datalist>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('addServiceForm').requestSubmit(); return false;">
                                                    Add Service
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        $category = $_GET["category"] ?? "";
                        $search = $_GET["search"] ?? "";

                        if ($category !== ""){
                            if ($search !== ""){
                                $sql = "SELECT * FROM service
                                WHERE Category = ?
                                AND(
                                ServiceID = ?
                                OR ServiceName = ?
                                OR Description LIKE ?
                                OR Price = ?
                                OR Duration = ?
                                OR Category = ?
                                )";

                                $searchTerm = "%" . $search . "%";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$category, $search, $search, $searchTerm, $search, $search, $search]);
                            } else {
                                $sql = "SELECT * FROM service
                                WHERE Category = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$category]);
                            }
                        } else {
                            if ($search !== ""){
                                $sql = "SELECT * FROM service
                                WHERE ServiceID = ?
                                OR ServiceName = ?
                                OR Description LIKE ?
                                OR Price = ?
                                OR Duration = ?
                                OR Category = ?";

                                $searchTerm = "%" . $search . "%";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$search, $search, $searchTerm, $search, $search, $search]);
                            } else { 
                                $sql = "SELECT * FROM service";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                            }
                        }
                    ?>

                    <div class="modal fade" id="editServiceModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Service</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="editServiceForm">
                                        <input type="hidden" name="action" value="editService">

                                        <div class="mb-3">
                                            <label for="editServiceID" class="form-label">Service ID</label>
                                            <input type="text" class="form-control" id="editServiceID" name="editServiceID" readonly>
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
                                            <label for="editDuration" class="form-label">Duration (minutes)</label>
                                            <input type="number" class="form-control" id="editDuration" name="editDuration" min="1" step="1" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editCategory" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="editCategory" name="editCategory" required>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('editServiceForm').requestSubmit(); return false;">
                                                    Edit Service
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteServiceModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Are you sure you want to delete it?</h5>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="deleteServiceForm">
                                        <input type="hidden" name="action" value="deleteService">
                                        
                                        <input type="hidden" id="deleteServiceID" name="deleteServiceID">

                                        <div class="delete-form-btn-container">
                                            <a class="primary-delete"
                                                onclick="document.getElementById('deleteServiceForm').submit();">
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
                                    <th>Service ID</th>
                                    <th>Service Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Duration (minutes)</th>
                                    <th>Category</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($service = $stmt->fetch()): ?> 
                                    <tr>
                                        <td><?= $service["ServiceID"]?></td>
                                        <td><?= $service["ServiceName"]?></td>
                                        <td class="description-cell"><?= $service["Description"]?></td>
                                        <td><?= $service["Price"]?></td>
                                        <td><?= $service["Duration"]?></td>
                                        <td><?= $service["Category"]?></td>

                                        <td class="btn-cell">
                                            <div class="row-container">
                                                <a class="row-edit" href="#" data-bs-toggle="modal" data-bs-target="#editServiceModal"
                                                data-serviceid="<?=htmlspecialchars($service["ServiceID"])?>"
                                                data-servicename="<?=htmlspecialchars($service['ServiceName'], ENT_QUOTES)?>"
                                                data-description="<?=htmlspecialchars($service['Description'], ENT_QUOTES)?>"
                                                data-price="<?=htmlspecialchars($service['Price'])?>"
                                                data-duration="<?=htmlspecialchars($service['Duration'])?>"
                                                data-category="<?=htmlspecialchars($service['Category'], ENT_QUOTES)?>"
                                                >
                                                    Edit
                                                </a>

                                                <a class="row-delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteServiceModal"
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