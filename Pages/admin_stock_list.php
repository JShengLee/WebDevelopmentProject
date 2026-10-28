<?php 
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "addItem"){
        $itemName = trim($_POST["itemName"]);
        $category = trim($_POST["category"]);
        $quantity = (float) trim($_POST["quantity"]);
        $reorderLevel = (int) trim($_POST["reorderLevel"]);

        try{
            $addItem = "INSERT INTO inventory (ItemName, Category, QuantityInStock, ReorderLevel)
                        VALUES (?, ?, ?, ?)";

            $stmtAddItem = $pdo->prepare($addItem);

            $stmtAddItem->execute(
                [$itemName,
                $category,
                $quantity,
                $reorderLevel]
            );

            echo"<script>
                    alert('Item added successfully!');
                    window.location.href = window.location.pathname;    
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to add item. Please check the details and try again.');
                </script>";

            echo($e->getMessage());
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "editItem"){
        $itemID = (int) trim($_POST["editItemID"]);
        $itemName = trim($_POST["editItemName"]);
        $itemCategory = trim($_POST["editItemCategory"]);

        try{
            $editItem = "UPDATE inventory SET ItemName = ?, Category = ? WHERE ItemID = ?";

            $stmtEditItem = $pdo->prepare($editItem);

            $stmtEditItem->execute(
                [$itemName,
                $itemCategory,
                $itemID]
            );

            echo"<script>
                    alert('Changes have been saved.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update item. Please check the details and try again.');
                </script>";

            echo($e->getMessage());
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "deleteItem"){
        $itemID = (int) trim($_POST["deleteItemID"]);

        try{

            $deleteItem = "DELETE FROM inventory WHERE ItemID = ?";

            $stmtDeleteItem = $pdo->prepare($deleteItem);

            $stmtDeleteItem->execute(
                [$itemID]
            );

            echo"<script>
                    alert('The item has been deleted.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to delete item.');
                </script>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "adjustItem"){
        $itemID = (int) trim($_POST["adjustItemID"]);
        $itemQuantity = trim($_POST["adjustQuantity"]);
        $reason = trim($_POST["reason"]);

        try{
            $pdo->beginTransaction();

            $currentsql = "SELECT QuantityInStock FROM inventory WHERE ItemID = ?";

            $stmtCurrent = $pdo->prepare($currentsql);
            $stmtCurrent->execute([$itemID]);

            $currentQuantity = $stmtCurrent->fetchColumn();

            $difference = (int) $currentQuantity - $itemQuantity;


            $adjustItem = "UPDATE inventory SET QuantityInStock = ? WHERE ItemID = ?";

            $stmtAdjustItem = $pdo->prepare($adjustItem);

            $stmtAdjustItem->execute(
                [$itemQuantity,
                $itemID]
            );

            $transaction = "INSERT INTO stocktransaction (ItemID, AdminID, TransactionType, Quantity, TransactionDate)
                            VALUES (?, ?, ?, ?, NOW())";
            
            $stmtTransaction = $pdo->prepare($transaction);

            $stmtTransaction->execute(
                [$itemID,
                $_SESSION["user_id"],
                $reason,
                $difference]
            );

            $pdo->commit();

        } catch (PDOException $e){

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update item. Please check the details and try again.');
                </script>";

            echo($e->getMessage());
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Admin Stock List | BrightSmile Dental Clinic</title>

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
                    <span class="page-label"> INVENTORY </span>
                    <div class="summary-container">
                        <div class="legend-card">
                            <p>Total Items</p>
                            <?php
                                $totalItemsSql = "SELECT COUNT(*) AS total FROM inventory";
                                $totalItemsStmt = $pdo->query($totalItemsSql);
                                $totalItemsStmt->execute();
                                $totalItems = $totalItemsStmt -> fetch()["total"];
                            ?>
                            <h3><?= $totalItems ?></h3>
                        </div>
                        <div class="legend-card">
                            <p>Low Stock</p>
                            <?php
                                $lowStockSql = "SELECT COUNT(*) AS low_stock FROM inventory WHERE QuantityInStock < 10";
                                $lowStockStmt = $pdo->query($lowStockSql);
                                $lowStockStmt->execute();
                                $lowStock = $lowStockStmt -> fetch()["low_stock"];
                            ?>
                            <h3><?= $lowStock ?></h3>
                        </div>
                    </div>
                    <form method="GET" id="searchForm">
                        <div class="top-container">
                            <div class="form-container">
                                <span class="nav-icon"><i class="fas fa-filter"></i></span>
                                <select class="filter" name="stock" id="stock" onchange="this.form.submit()">
                                    <option value="">All Items</option>
                                    <option value="low"
                                        <?= ($_GET["stock"] ?? "") === "low" ? "selected" : "" ?>>Low Stock</option>
                                    <option value="moderate"
                                        <?= ($_GET["stock"] ?? "") === "moderate" ? "selected" : "" ?>>Moderate Stock</option>
                                    <option value="sufficient"
                                        <?= ($_GET["stock"] ?? "") === "sufficient" ? "selected" : "" ?>>Sufficient Stock</option>
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
                                <a class="add-doctor-btn" href="#" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                    + Add Item
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="modal fade" id="addItemModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Item</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="addItemForm">
                                        <input type="hidden" name="action" value="addItem">

                                        <div class="mb-3">
                                            <label for="itemName" class="form-label">Item Name</label>
                                            <input type="text" class="form-control" id="itemName" name="itemName" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" name="category" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" step="any" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="reorderLevel" class="form-label">Priority Level</label>
                                             <select class="form-select" id="reorderLevel" name="reorderLevel" required>
                                                <option value="">Select Level</option>
                                                <option value="1">High Priority</option>
                                                <option value="2">Medium Priority</option>
                                                <option value="3">Low Priority</option>
                                            </select>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('addItemForm').requestSubmit(); return false;">
                                                    Add Item
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        $stock = $_GET["stock"] ?? "";
                        $search = $_GET["search"] ?? "";

                        if ($stock === "low"){
                            $stockAmount = 5;
                        } elseif ($stock === "moderate"){
                            $stockAmount = 10;
                        } elseif ($stock === "sufficient"){
                            $stockAmount = 10;
                        }

                        if ($stock === "low" || $stock === "moderate"){
                            if ($search !== ""){
                                $sql = "SELECT * 
                                FROM inventory
                                WHERE (ItemID = ?
                                OR ItemName LIKE ?
                                OR Category LIKE ?)
                                AND QuantityInStock <= ?";

                                $searchTerm = "%" . $search . "%";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$search, $searchTerm, $searchTerm, $stockAmount]);
                            } else {
                                $sql = "SELECT * FROM inventory WHERE QuantityInStock <= ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$stockAmount]);
                            }
                        } elseif ($stock === "sufficient"){
                            if ($search !== ""){
                                $sql = "SELECT * 
                                FROM inventory
                                WHERE (ItemID = ?
                                OR ItemName LIKE ?
                                OR Category LIKE ?)
                                AND QuantityInStock > ?";

                                $searchTerm = "%" . $search . "%";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$search, $searchTerm, $searchTerm, $stockAmount]);
                            } else {
                                $sql = "SELECT * FROM inventory WHERE QuantityInStock > ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$stockAmount]);
                            }
                        } else { 
                            if ($search !== ""){
                                $sql = "SELECT * 
                                FROM inventory
                                WHERE ItemID = ?
                                OR ItemName LIKE ?
                                OR Category LIKE ?";

                                $searchTerm = "%" . $search . "%";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$search, $searchTerm, $searchTerm]);
                            } else {
                                $sql = "SELECT * FROM inventory";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                            }
                        }
                    ?>

                    <div class="modal fade" id="editItemModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Items</h5>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="editItemForm">
                                        <input type="hidden" name="action" value="editItem">

                                        <div class="mb-3">
                                            <label for="editItemID" class="form-label">Item ID</label>
                                            <input type="text" class="form-control" id="editItemID" name="editItemID" readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editItemName" class="form-label">Item Name</label>
                                            <input type="text" class="form-control" id="editItemName" name="editItemName" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editItemCategory" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="editItemCategory" name="editItemCategory" required>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('editItemForm').requestSubmit(); return false;">
                                                    Update Item
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteItemModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Are you sure you want to delete it?</h5>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="deleteItemForm">
                                        <input type="hidden" name="action" value="deleteItem">
                                        
                                        <input type="hidden" id="deleteItemID" name="deleteItemID">

                                        <div class="delete-form-btn-container">
                                            <a class="primary-delete"
                                                onclick="document.getElementById('deleteItemForm').submit();">
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
                    
                    <div class="modal fade" id="adjustItemModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title">Adjust Inventory Quantity</h5>
                                        <span id="adjustItemName" class="text-muted"></span>
                                    </div>

                                    <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                                        <span>
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" id="adjustItemForm">
                                        <input type="hidden" name="action" value="adjustItem">
                                        <input type="hidden" id="adjustItemID" name="adjustItemID">

                                        <div class="mb-3">
                                            <label for="adjustQuantity" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="adjustQuantity" name="adjustQuantity" step="any" min="0" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="reason" class="form-label">Reason</label>
                                             <select class="form-select" id="reason" name="reason" required>
                                                <option value="">Select Reason</option>
                                                <option value="Restock">Restock</option>
                                                <option value="Usage">Usage</option>
                                                <option value="Adjustment">Adjustment</option>
                                            </select>
                                        </div>

                                        <div class="modal-footer">
                                            <a class="primary-button"
                                                onclick="document.getElementById('adjustItemForm').requestSubmit(); return false;">
                                                    Confirm Adjustment
                                            </a>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="inventory-container">
                        <table class="dentist-table">
                            <!-- <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th style="text-align:center;">Status</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead> -->
                            <tbody>
                                <?php 
                                    while ($stock = $stmt->fetch()):
                                ?> 
                                    <tr class="item-row" tabindex="0">
                                        <td colspan="6">
                                            <div class="inventory-item">
                                                <div>
                                                    <h5><?= htmlspecialchars($stock["ItemName"]) ?></h5>
                                                    <div class="item-details">
                                                        <span>ID: <?= htmlspecialchars($stock["ItemID"]) ?> : <?= htmlspecialchars($stock["Category"]) ?></span>
                                                    </div>
                                                </div>
                                                <div class="quantity">
                                                    <?php if ($stock["QuantityInStock"] <= 5): ?>
                                                        <span class="badge bg-danger">Low Stock</span>
                                                    <?php elseif ($stock["QuantityInStock"] <= 10): ?>
                                                        <span class="badge bg-warning">Moderate Stock</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">In Stock</span>
                                                    <?php endif; ?>
                                                    <h3><?= htmlspecialchars($stock["QuantityInStock"]) ?></h3>
                                                    <span class="arrow">&#9662;</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="action-row" hidden>
                                        <td colspan="6">
                                            <div class="action-content">
                                                <a class="row-edit" href="#" data-bs-toggle="modal" data-bs-target="#editItemModal"
                                                data-itemid="<?= htmlspecialchars($stock["ItemID"]) ?>"
                                                data-itemname="<?= htmlspecialchars($stock["ItemName"]) ?>"
                                                data-itemcategory="<?= htmlspecialchars($stock["Category"]) ?>"
                                                >
                                                    Update
                                                </a>

                                                <a class="row-adjust" href="#" data-bs-toggle="modal" data-bs-target="#adjustItemModal"
                                                data-itemid="<?= htmlspecialchars($stock["ItemID"]) ?>"
                                                data-itemname="<?= htmlspecialchars($stock["ItemName"]) ?>"
                                                data-itemquantity="<?= htmlspecialchars($stock["QuantityInStock"]) ?>"
                                                >
                                                    Adjust Quantity
                                                </a>

                                                <a class="row-delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteItemModal"
                                                data-itemid="<?=htmlspecialchars($stock["ItemID"])?>"
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
            document.querySelectorAll(".dentist-table tbody .item-row").forEach(row => {
                function toggleActionRow() {
                    const actionRow = row.nextElementSibling;

                    // Get the current open row
                    const isAlreadyOpen = row.classList.contains("selected");

                    // Close every currently open action row
                    document.querySelectorAll(
                        ".dentist-table tbody .item-row.selected"
                    ).forEach(openRow => {
                        openRow.classList.remove("selected");
                        openRow.setAttribute("aria-expanded", "false");
                        openRow.nextElementSibling.hidden = true;
                    });

                    // Open the clicked row unless it was already open
                    if (!isAlreadyOpen) {
                        row.classList.add("selected");
                        row.setAttribute("aria-expanded", "true");
                        actionRow.hidden = false;
                    }
                }

                // Mouse click
                row.addEventListener("click", toggleActionRow);

                // Keyboard support: Enter or Space
                row.addEventListener("keydown", event => {
                    if (
                        event.key === "Enter" ||
                        event.key === " "
                    ) {
                        event.preventDefault();
                        toggleActionRow();
                    }
                });
            });


            document.querySelectorAll(".row-edit").forEach(button =>{
                button.addEventListener("click", function (){
                    const item = this.dataset;

                    document.getElementById("editItemID").value = item.itemid;
                    document.getElementById("editItemName").value = item.itemname;
                    document.getElementById("editItemCategory").value = item.itemcategory;
                });
            });

            document.querySelectorAll(".row-delete").forEach(button =>{
                button.addEventListener("click", function (){
                    const item = this.dataset;

                    document.getElementById("deleteItemID").value = item.itemid;
                });
            });   

            document.querySelectorAll(".row-adjust").forEach(button => {
                button.addEventListener("click", function () {
                    const item = this.dataset;

                    document.getElementById("adjustItemID").value = item.itemid;
                    document.getElementById("adjustItemName").textContent = item.itemname;
                    document.getElementById("adjustQuantity").value = item.itemquantity;
                });
            }); 
        </script>
    </body>
</html>