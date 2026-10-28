<?php
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
    require_once __DIR__ . '/../email_helper.php';
    // include "../dbconnection.php";

    $confirmedcountSql = "SELECT COUNT(*) FROM appointment WHERE AppointmentStatus = 'Confirmed'";
    $confirmedcountStmt = $pdo->prepare($confirmedcountSql);
    $confirmedcountStmt->execute();
    $confirmedcount = $confirmedcountStmt->fetchColumn();

    $completedcountSql = "SELECT COUNT(*) FROM appointment WHERE AppointmentStatus = 'Completed'";
    $completedcountStmt = $pdo->prepare($completedcountSql);
    $completedcountStmt->execute();
    $completedcount = $completedcountStmt->fetchColumn();

    $pendingcountSql = "SELECT COUNT(*) FROM appointment WHERE AppointmentStatus = 'Pending'";
    $pendingcountStmt = $pdo->prepare($pendingcountSql);
    $pendingcountStmt->execute();
    $pendingcount = $pendingcountStmt->fetchColumn();

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "updateStatus"){
        $status = $_POST["status"];
        $appointmentID = $_POST["appointmentID"];

        try{
            $updateStatusSql = "UPDATE appointment SET AppointmentStatus = ? WHERE AppointmentID = ?";
            $updateStatusStmt = $pdo->prepare($updateStatusSql);
            $updateStatusStmt->execute([
                $status,
                $appointmentID
            ]);

            $emailSql = "SELECT u.Email, u.FullName, s.ServiceName, a.AppointmentDate, a.AppointmentTime
                        FROM appointment a
                        INNER JOIN patient p ON a.PatientID = p.PatientID
                        INNER JOIN user u ON p.UserID = u.UserID
                        INNER JOIN service s ON a.ServiceID = s.ServiceID
                        WHERE a.AppointmentID = ?";

            $emailStmt = $pdo->prepare($emailSql);
            $emailStmt->execute([$appointmentID]);

            $patient = $emailStmt->fetch(PDO::FETCH_ASSOC);

            if ($patient){
                $subject = "Appointment Status";
                $body = "
                    <h2>Appointment Status</h2>
                    <p>Hello ".htmlspecialchars($patient["FullName"], ENT_QUOTES, "UTF-8") . ",</p>
                    <p>Your dental appointment for ".htmlspecialchars($patient["ServiceName"], ENT_QUOTES, "UTF-8")." has been updated to:
                        <strong>".htmlspecialchars($status, ENT_QUOTES, "UTF-8")."</strong>    
                    </p>
                    <p>Appointment ID: #".(int)$appointmentID."</p>
                    <p>Thank you for choosing BrightSmile Dental.</p>
                ";

                $emailSent = sendEmail(
                    $patient["Email"],
                    $subject,
                    $body
                );

                if (!$emailSent){
                    error_log("Failed to send appointment status email for appointment ID: ".$appointmentID);
                }
            }

            echo"<script>
                    alert('Changes have been saved.');
                    window.location.href = window.location.pathname;
                </script>";

            exit;
        } catch (PDOException $e){
            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update appointment status.');
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
    <title>Admin Appointment List | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Styling/admin_appointment_list.css?v=7">
</head>
<body>
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
                    <span class="page-label"> APPOINTMENT </span>
                    <!-- Top summary cards -->
                    <div class="top-container">
                        <div class="summary-card">
                            <div class="summary-icon upcoming-label">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                <p class="summary-caption">UPCOMING</p>
                                <strong><?=$confirmedcount?></strong>
                                <p>Confirmed visits</p>
                            </div>
                        </div>

                        <div class="summary-card">
                            <div class="summary-icon completed-label">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <p class="summary-caption">COMPLETED</p>
                                <strong><?=$completedcount?></strong>
                                <p>Visits completed</p>
                            </div>
                        </div>

                        <div class="summary-card">
                            <div class="summary-icon pending-label">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <p class="summary-caption">PENDING REQUESTS</p>
                                <strong><?=$pendingcount?></strong>
                                <p>Awaiting approval</p>
                            </div>
                        </div>
                    </div>

                    <!-- Search bar -->
                    <form method="GET" id="searchForm">
                        <div class="search-container">
                            <div class="searchbar">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" id="search" class="search" placeholder="Search appointments..." 
                                value="<?= htmlspecialchars($_GET["search"] ?? "") ?>"/>
                                <i class="far fa-times-circle" onclick="clearSearch()"></i>
                            </div>

                            <!-- Filter bar -->
                            <div class="filterbar">
                                <select name="status" id="status" class="filter" onchange="this.form.submit()">
                                    <option value="" <?= ($_GET['status'] ?? '') === '' ? 'selected' : '' ?>>All Status</option>
                                    <option value="Pending" <?= ($_GET['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>
                                        Pending
                                    </option>
                                    <option value="Confirmed" <?= ($_GET['status'] ?? '') === 'Confirmed' ? 'selected' : '' ?>>
                                        Confirmed
                                    </option>
                                    <option value="Completed" <?= ($_GET['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>
                                        Completed
                                    </option>
                                    <option value="Cancelled" <?= ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>
                                        Cancelled
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <?php
                        $search = $_GET["search"] ?? "";
                        $status = $_GET["status"] ?? "";

                        if ($search !== ""){
                            if ($status !== ""){
                                $sql = "SELECT a.AppointmentID, 
                                        a.AppointmentDate, 
                                        a.AppointmentTime, 
                                        a.AppointmentStatus,
                                        a.AppointmentNotes,
                                        u1.FullName AS PatientName,
                                        u2.FullName AS DentistName,
                                        s.ServiceName
                                        FROM appointment a 
                                        INNER JOIN patient p
                                            ON a.PatientID = p.PatientID
                                        INNER JOIN user u1
                                            ON p.UserID = u1.UserID
                                        INNER JOIN dentist d
                                            ON a.DentistID = d.DentistID
                                        INNER JOIN user u2
                                            ON d.UserID = u2.UserID
                                        INNER JOIN service s
                                            ON a.ServiceID = s.ServiceID
                                        WHERE (u1.FullName LIKE ?
                                        OR u2.FullName LIKE ?
                                        OR s.ServiceName LIKE ?)
                                        AND a.AppointmentStatus = ?";
                                $searchTerm = "%" . $search . "%";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([
                                    $searchTerm,
                                    $searchTerm,
                                    $searchTerm,
                                    $status
                                ]);
                            } else {
                                $sql = "SELECT a.AppointmentID, 
                                        a.AppointmentDate, 
                                        a.AppointmentTime, 
                                        a.AppointmentStatus,
                                        a.AppointmentNotes,
                                        u1.FullName AS PatientName,
                                        u2.FullName AS DentistName,
                                        s.ServiceName
                                        FROM appointment a 
                                        INNER JOIN patient p
                                            ON a.PatientID = p.PatientID
                                        INNER JOIN user u1
                                            ON p.UserID = u1.UserID
                                        INNER JOIN dentist d
                                            ON a.DentistID = d.DentistID
                                        INNER JOIN user u2
                                            ON d.UserID = u2.UserID
                                        INNER JOIN service s
                                            ON a.ServiceID = s.ServiceID
                                        WHERE u1.FullName LIKE ?
                                        OR u2.FullName LIKE ?
                                        OR s.ServiceName LIKE ?";
                                $searchTerm = "%" . $search . "%";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([
                                    $searchTerm,
                                    $searchTerm,
                                    $searchTerm
                                ]);
                            }
                        } else {
                            if ($status !== ""){
                                $sql = "SELECT a.AppointmentID, 
                                        a.AppointmentDate, 
                                        a.AppointmentTime, 
                                        a.AppointmentStatus,
                                        a.AppointmentNotes,
                                        u1.FullName AS PatientName,
                                        u2.FullName AS DentistName,
                                        s.ServiceName
                                        FROM appointment a 
                                        INNER JOIN patient p
                                            ON a.PatientID = p.PatientID
                                        INNER JOIN user u1
                                            ON p.UserID = u1.UserID
                                        INNER JOIN dentist d
                                            ON a.DentistID = d.DentistID
                                        INNER JOIN user u2
                                            ON d.UserID = u2.UserID
                                        INNER JOIN service s
                                            ON a.ServiceID = s.ServiceID
                                        WHERE a.AppointmentStatus = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$status]);
                            } else {
                                $sql = "SELECT a.AppointmentID, 
                                        a.AppointmentDate, 
                                        a.AppointmentTime, 
                                        a.AppointmentStatus,
                                        a.AppointmentNotes,
                                        u1.FullName AS PatientName,
                                        u2.FullName AS DentistName,
                                        s.ServiceName
                                        FROM appointment a 
                                        INNER JOIN patient p
                                            ON a.PatientID = p.PatientID
                                        INNER JOIN user u1
                                            ON p.UserID = u1.UserID
                                        INNER JOIN dentist d
                                            ON a.DentistID = d.DentistID
                                        INNER JOIN user u2
                                            ON d.UserID = u2.UserID
                                        INNER JOIN service s
                                            ON a.ServiceID = s.ServiceID";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                            }
                        }
                    ?>

                    <!-- Appointment Table -->
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-header">
                                <tr>
                                    <th>Appointment</th>
                                    <th>Patient</th>
                                    <th>Dentist</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($appointment = $stmt->fetch()):?>
                                    <tr onclick="toggleNotes(<?=(int)$appointment['AppointmentID']?>)">
                                        <td>
                                            <div>
                                                <p class="service-name"><?= $appointment["ServiceName"] ?></p>
                                                <p class="appointment-id"># <?= $appointment["AppointmentID"]?></p>
                                            </div>
                                        </td>
                                        <td><?= $appointment["PatientName"]?></td>
                                        <td><?= $appointment["DentistName"]?></td>
                                        <td>
                                            <div>
                                                <p class="appointment-date"><?=$appointment["AppointmentDate"]?></p>
                                                <p class="appointment-time"><?=$appointment["AppointmentTime"]?></p>
                                            </div>
                                        </td>
                                        <?php if($appointment["AppointmentStatus"] === "Pending"): ?>
                                            <td>
                                                <p class="pending">Pending</p>
                                            </td>
                                        <?php elseif($appointment["AppointmentStatus"] === "Confirmed"): ?>
                                            <td>
                                                <p class="confirmed">Confirmed</p>
                                            </td>
                                        <?php elseif($appointment["AppointmentStatus"] === "Completed"): ?>
                                            <td>
                                                <p class="completed">Completed</p>
                                            </td>
                                        <?php elseif($appointment["AppointmentStatus"] === "Cancelled"): ?>
                                            <td>
                                                <p class="cancelled">Cancelled</p>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <form method="POST" id="statusUpdate">
                                                <input type="hidden" name="action" value="updateStatus" />
                                                <input type="hidden" name="appointmentID" value="<?=$appointment["AppointmentID"]?>"/>
                                                <div class="action-row">
                                                    <select name="status">
                                                        <option value="Pending"
                                                            <?=$appointment["AppointmentStatus"] === "Pending" ? "selected" : "" ?>
                                                            disabled>
                                                            Pending
                                                        </option>
                                                        <option value="Confirmed"
                                                            <?=$appointment["AppointmentStatus"] === "Confirmed" ? "selected" : "" ?>>
                                                            Confirmed
                                                        </option>
                                                        <option value="Completed"
                                                            <?=$appointment["AppointmentStatus"] === "Completed" ? "selected" : "" ?>>
                                                            Completed
                                                        </option>
                                                        <option value="Cancelled"
                                                            <?=$appointment["AppointmentStatus"] === "Cancelled" ? "selected" : "" ?>>
                                                            Cancelled
                                                        </option>
                                                    </select>
                                                    <a href="#" class="update-btn" onclick="event.preventDefault(); this.closest('form').requestSubmit();">Update</a>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr id="notes-<?=(int)$appointment["AppointmentID"]?>" style="display: none;">
                                        <td colspan="6">
                                            <div>
                                                <strong>Notes</strong>
                                                <p>
                                                    <?=nl2br(htmlspecialchars($appointment["AppointmentNotes"] ?? "No notes provided.", ENT_QUOTES, "UTF-8"))?>
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr>
                                    <td colspan="6" class="table-footer"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
        <script>
            function clearSearch(){
                const search = document.getElementById("search");
                search.value = "";
                search.form.submit();
            }

            function toggleNotes(appointmentID){
                const notesRow = document.getElementById(`notes-${appointmentID}`);

                if (!notesRow) return;

                const isHidden = getComputedStyle(notesRow).display === "none";
                notesRow.style.display = isHidden ? "table-row" : "none";
            }
        </script>
</body>
</html>