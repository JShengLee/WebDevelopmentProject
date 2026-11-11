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

    $cancelledcountSql = "SELECT COUNT(*) FROM appointment WHERE AppointmentStatus = 'Cancelled'";
    $cancelledcountStmt = $pdo->prepare($cancelledcountSql);
    $cancelledcountStmt->execute();
    $cancelledcount = $cancelledcountStmt->fetchColumn();

    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "updateStatus"){
        $status = $_POST["status"] ?? "";
        $appointmentID = filter_var($_POST["appointmentID"] ?? null, FILTER_VALIDATE_INT);
        $allowedStatuses = ["Confirmed", "Completed", "Cancelled"];

        if (!in_array($status, $allowedStatuses, true) || !$appointmentID) {
            echo "<script>alert('Unable to update appointment status.');</script>";
        } else try{
            $pdo->beginTransaction();
            $updateStatusSql = "UPDATE appointment SET AppointmentStatus = ? WHERE AppointmentID = ?";
            $updateStatusStmt = $pdo->prepare($updateStatusSql);
            $updateStatusStmt->execute([
                $status,
                $appointmentID
            ]);

            if ($status === "Completed") {
                sync_completed_appointment_record($pdo, (int) $appointmentID);
            }
            $pdo->commit();

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
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to update appointment status.');
                </script>";
        }
    }


    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "addAppointment"){
        $patientid = $_POST["patientid"];
        $dentistid = $_POST["dentistid"];
        $serviceid = $_POST["serviceid"];
        $date = $_POST["date"];
        $time = $_POST["time"];
        $notes = trim($_POST["notes"]);

        try{
            $sqlAppointment = "INSERT INTO appointment (PatientID, DentistID, ServiceID, AppointmentDate, AppointmentTime, AppointmentStatus, AppointmentNotes)
                        VALUES (?, ?, ?, ?, ?, 'Confirmed', ?)";
            
            $stmtAppointment = $pdo->prepare($sqlAppointment);

            $stmtAppointment->execute(
                [$patientid,
                $dentistid,
                $serviceid,
                $date,
                $time,
                $notes]
            );

            echo"<script>
                    alert('Appointment added successfully!');
                    window.location.href = window.location.pathname;    
                </script>";

            exit;
        } catch (Throwable $e){
            if ($pdo->inTransaction()){
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            echo"<script>
                    alert('Unable to add appointment. Please check the details and try again.');
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
    <link rel="stylesheet" href="../Styling/admin_appointment_list.css?v=10">
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
                            <div class="summary-icon cancelled-label">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div>
                                <p class="summary-caption">CANCELLED</p>
                                <strong><?=$cancelledcount?></strong>
                                <p>Cancelled visits</p>
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

                            <a class="addAppointment" href="#" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                                + Add Appointment
                            </a>
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
                                        AND a.AppointmentStatus = ?
                                        ORDER BY a.AppointmentID DESC";
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
                                        OR s.ServiceName LIKE ?
                                        ORDER BY a.AppointmentID DESC";
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
                                        WHERE a.AppointmentStatus = ?
                                        ORDER BY a.AppointmentID DESC";
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
                                            ON a.ServiceID = s.ServiceID
                                        ORDER BY a.AppointmentID DESC";
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
                                        <?php if($appointment["AppointmentStatus"] === "Confirmed"): ?>
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

        <div class="modal fade" id="addAppointmentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Appointment</h5>

                        <a class="primary-button cancel-btn" data-bs-dismiss="modal">
                            <span>
                                <i class="fas fa-times"></i>
                            </span>
                        </a>
                    </div>
                    
                    <div class="modal-body">
                        <form method="POST" id="addAppointmentForm">
                            <input type="hidden" name="action" value="addAppointment"/>

                            <div class="mb-3 patient-search-container">
                                <label for="patientid" class="form-label">Patient Name</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="patientSearch"
                                    placeholder="Search patient name..."
                                    autocomplete="off"
                                    required
                                />

                                <input type="hidden" id="patientid" name="patientid">

                                <div id="patientResult" class="patient-result"></div>
                            </div>

                            

                            <div class="mb-3 patient-search-container">
                                <label for="dentistid" class="form-label">Dentist Name</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="dentistSearch"
                                    placeholder="Search dentist name..."
                                    autocomplete="off"
                                    required
                                />

                                <input type="hidden" id="dentistid" name="dentistid">

                                <div id="dentistResult" class="patient-result"></div>
                            </div>



                            <div class="mb-3 patient-search-container">
                                <label for="serviceid" class="form-label">Service Name</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="serviceSearch"
                                    placeholder="Search service name..."
                                    autocomplete="off"
                                    required
                                />

                                <input type="hidden" id="serviceid" name="serviceid">

                                <div id="serviceResult" class="patient-result"></div>
                            </div>

                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>

                            <div class="mb-3">
                                <label for="time" class="form-label">Time</label>
                                <select class="form-select" id="time" name="time" required>
                                    <option value="">Select Time</option>
                                        <option value="9:00:00"
                                            <?= ($_GET["time"] ?? "") === "9:00:00" ? "selected" : "" ?>>
                                            9:00 AM
                                        </option>
                                        <option value="10:00:00"
                                            <?= ($_GET["time"] ?? "") === "10:00:00" ? "selected" : "" ?>>
                                            10:00 AM
                                        </option>
                                        <option value="11:00:00"
                                            <?= ($_GET["time"] ?? "") === "11:00:00" ? "selected" : "" ?>>
                                            11:00 AM
                                        </option>
                                        <option value="14:00:00"
                                            <?= ($_GET["time"] ?? "") === "14:00:00" ? "selected" : "" ?>>
                                            2:00 PM
                                        </option>
                                        <option value="15:00:00"
                                            <?= ($_GET["time"] ?? "") === "15:00:00" ? "selected" : "" ?>>
                                            3:00 PM
                                        </option>
                                        <option value="16:00:00"
                                            <?= ($_GET["time"] ?? "") === "16:00:00" ? "selected" : "" ?>>
                                            4:00 PM
                                        </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea
                                    class="form-control"
                                    id="notes"
                                    name="notes"
                                    rows="4"
                                    placeholder="Enter appointment notes..."
                                ></textarea>
                            </div>

                            <div class="modal-footer">
                                <a class="primary-button"
                                    onclick="document.getElementById('addAppointmentForm').requestSubmit(); return false;">
                                        Add Appointment
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
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





            const patientSearch = document.getElementById('patientSearch');
            const patientResults = document.getElementById('patientResult');
            const patientId = document.getElementById('patientid');
            const appointmentForm = document.getElementById('addAppointmentForm');

            let searchController;

            patientSearch.addEventListener('input', async function () {
                const search = this.value.trim();

                // A new search means the previous selection is no longer valid.
                patientId.value = '';
                patientResults.replaceChildren();

                if (search.length < 2) {
                    patientResults.style.display = 'none';
                    return;
                }

                // Cancel an earlier request if the user types again.
                if (searchController) {
                    searchController.abort();
                }

                searchController = new AbortController();

                try {
                    const response = await fetch(
                        '../includes/search_patients.php?search=' + encodeURIComponent(search),
                        { signal: searchController.signal }
                    );

                    if (!response.ok) {
                        throw new Error('Patient search failed.');
                    }

                    const patients = await response.json();

                    // Ignore stale results if the input has changed.
                    if (patientSearch.value.trim() !== search) {
                        return;
                    }

                    patientResults.replaceChildren();

                    if (patients.length === 0) {
                        const message = document.createElement('div');
                        message.className = 'patient-no-results';
                        message.textContent = 'No matching patients found.';
                        patientResults.appendChild(message);
                    } else {
                        patients.forEach(function (patient) {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className = 'patient-result';

                            const name = document.createElement('strong');
                            name.textContent = patient.FullName;

                            const id = document.createElement('small');
                            id.textContent = 'Patient ID: ' + patient.PatientID;

                            option.append(name, id);

                            option.addEventListener('click', function () {
                                patientSearch.value = patient.FullName;
                                patientId.value = patient.PatientID;

                                patientResults.style.display = 'none';
                            });

                            patientResults.appendChild(option);
                        });
                    }

                    patientResults.style.display = 'block';

                } catch (error) {
                    if (error.name !== 'AbortError') {
                        patientResults.replaceChildren();

                        const message = document.createElement('div');
                        message.className = 'patient-no-results';
                        message.textContent = 'Unable to search patients. Please try again.';

                        patientResults.appendChild(message);
                        patientResults.style.display = 'block';

                        console.error(error);
                    }
                }
            });

            // Hide the dropdown when clicking outside the search field.
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.patient-search-container')) {
                    patientResults.style.display = 'none';
                }
            });

            // Prevent submitting the appointment without selecting a real result.
            appointmentForm.addEventListener('submit', function (event) {
                if (!patientId.value) {
                    event.preventDefault();
                    alert('Please select a patient from the search results.');
                    patientSearch.focus();
                }
            });


            const dentistSearch = document.getElementById('dentistSearch');
            const dentistResults = document.getElementById('dentistResult');
            const dentistId = document.getElementById('dentistid');

            dentistSearch.addEventListener('input', async function () {
                const search = this.value.trim();

                // A new search means the previous selection is no longer valid.
                dentistId.value = '';
                dentistResults.replaceChildren();

                if (search.length < 2) {
                    dentistResults.style.display = 'none';
                    return;
                }

                // Cancel an earlier request if the user types again.
                if (searchController) {
                    searchController.abort();
                }

                searchController = new AbortController();

                try {
                    const response = await fetch(
                        '../includes/search_dentist.php?search=' + encodeURIComponent(search),
                        { signal: searchController.signal }
                    );

                    if (!response.ok) {
                        throw new Error('Dentist search failed.');
                    }

                    const dentists = await response.json();

                    // Ignore stale results if the input has changed.
                    if (dentistSearch.value.trim() !== search) {
                        return;
                    }

                    dentistResults.replaceChildren();

                    if (dentists.length === 0) {
                        const message = document.createElement('div');
                        message.className = 'patient-no-results';
                        message.textContent = 'No matching dentists found.';
                        dentistResults.appendChild(message);
                    } else {
                        dentists.forEach(function (dentist) {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className = 'patient-result';

                            const name = document.createElement('strong');
                            name.textContent = dentist.FullName;

                            const id = document.createElement('small');
                            id.textContent = 'Dentist ID: ' + dentist.DentistID;

                            option.append(name, id);

                            option.addEventListener('click', function () {
                                dentistSearch.value = dentist.FullName;
                                dentistId.value = dentist.DentistID;

                                dentistResults.style.display = 'none';
                            });

                            dentistResults.appendChild(option);
                        });
                    }

                    dentistResults.style.display = 'block';

                } catch (error) {
                    if (error.name !== 'AbortError') {
                        dentistResults.replaceChildren();

                        const message = document.createElement('div');
                        message.className = 'patient-no-results';
                        message.textContent = 'Unable to search dentists. Please try again.';

                        dentistResults.appendChild(message);
                        dentistResults.style.display = 'block';

                        console.error(error);
                    }
                }
            });

            // Hide the dropdown when clicking outside the search field.
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.patient-search-container')) {
                    dentistResults.style.display = 'none';
                }
            });

            // Prevent submitting the appointment without selecting a real result.
            appointmentForm.addEventListener('submit', function (event) {
                if (!dentistId.value) {
                    event.preventDefault();
                    alert('Please select a dentist from the search results.');
                    dentistSearch.focus();
                }
            });


            const serviceSearch = document.getElementById('serviceSearch');
            const serviceResults = document.getElementById('serviceResult');
            const serviceId = document.getElementById('serviceid');

            serviceSearch.addEventListener('input', async function () {
                const search = this.value.trim();

                // A new search means the previous selection is no longer valid.
                serviceId.value = '';
                serviceResults.replaceChildren();

                if (search.length < 2) {
                    serviceResults.style.display = 'none';
                    return;
                }

                // Cancel an earlier request if the user types again.
                if (searchController) {
                    searchController.abort();
                }

                searchController = new AbortController();

                try {
                    const response = await fetch(
                        '../includes/search_service.php?search=' + encodeURIComponent(search),
                        { signal: searchController.signal }
                    );

                    if (!response.ok) {
                        throw new Error('Dentist search failed.');
                    }

                    const services = await response.json();

                    // Ignore stale results if the input has changed.
                    if (serviceSearch.value.trim() !== search) {
                        return;
                    }

                    serviceResults.replaceChildren();

                    if (services.length === 0) {
                        const message = document.createElement('div');
                        message.className = 'patient-no-results';
                        message.textContent = 'No matching service found.';
                        serviceResults.appendChild(message);
                    } else {
                        services.forEach(function (service) {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className = 'patient-result';

                            const name = document.createElement('strong');
                            name.textContent = service.ServiceName;

                            const id = document.createElement('small');
                            id.textContent = 'Service ID: ' + service.ServiceID;

                            option.append(name, id);

                            option.addEventListener('click', function () {
                                serviceSearch.value = service.ServiceName;
                                serviceId.value = service.ServiceID;

                                serviceResults.style.display = 'none';
                            });

                            serviceResults.appendChild(option);
                        });
                    }

                    serviceResults.style.display = 'block';

                } catch (error) {
                    if (error.name !== 'AbortError') {
                        serviceResults.replaceChildren();

                        const message = document.createElement('div');
                        message.className = 'patient-no-results';
                        message.textContent = 'Unable to search services. Please try again.';

                        serviceResults.appendChild(message);
                        serviceResults.style.display = 'block';

                        console.error(error);
                    }
                }
            });

            // Hide the dropdown when clicking outside the search field.
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.patient-search-container')) {
                    serviceResults.style.display = 'none';
                }
            });

            // Prevent submitting the appointment without selecting a real result.
            appointmentForm.addEventListener('submit', function (event) {
                if (!serviceId.value) {
                    event.preventDefault();
                    alert('Please select a dentist from the search results.');
                    serviceSearch.focus();
                }
            });

            const dateInput = document.getElementById('date');

            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');

dateInput.min = `${year}-${month}-${day}`;
        </script>
</body>
</html>