<?php
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
    // include "../dbconnection.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Admin Dashboard | BrightSmile Dental Clinic</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/admin_homepage.css?v=24">
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

                    <!-- Welcome section -->
                    <section class="welcome-section">
                        <div>
                            <span class="page-label">
                                ADMIN DASHBOARD
                            </span>
                            <?php
                                $sql = "SELECT * FROM user WHERE UserID = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$currentPatient['UserID']]);
                                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <h1>
                                Good day, <?= htmlspecialchars($user['FullName'] ) ?> 👋
                            </h1>
                        </div>
                    </section>

                    <section class="summary-grid">
                        <!-- Dentist Summary -->
                        <div class="summary-card">
                            <div class="section-heading">
                                <h2>
                                    Dentists
                                </h2>
                                <a href="admin_dentist_list.php">View all</a>
                            </div>
                            <!-- <h2>
                                18 Sep
                            </h2>

                            <p class="card-description">
                                10:00 AM · General Check-up
                            </p> -->
                            <div class="card-content">
                                <div class="doctor-summary">
                                    <div class="doctor-gauge">
                                        <svg class="gauge-circle" viewBox="0 0 200 200">
                                            <circle
                                                class="gauge-background"
                                                cx="100"
                                                cy="100"
                                                r="80">
                                            </circle>

                                            <g id="dentistGaugeSegments"></g>

                                        </svg>
                                        <!-- The gauge texts are edited in the gaugefunction.js file bcz it will be retrieving the info from the database -->
                                        <div class="gauge-text">
                                            <span id="dentistTotalValue"></span>
                                        </div>
                                    </div>
                                </div>
                                <div id="dentistRoleAmount">
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Service Summary -->
                        <div class="summary-card">
                            <div class="section-heading">
                                <h2>
                                    Services
                                </h2>
                                <a href="admin_service_list.php">View all</a>
                            </div>
                            <!-- <h2>
                                18 Sep
                            </h2>

                            <p class="card-description">
                                10:00 AM · General Check-up
                            </p> -->
                            <div class="card-content">
                                <div class="doctor-summary">
                                    <div class="doctor-gauge">
                                        <svg class="gauge-circle" viewBox="0 0 200 200">
                                            <circle
                                                class="gauge-background"
                                                cx="100"
                                                cy="100"
                                                r="80">
                                            </circle>

                                            <g id="serviceGaugeSegments"></g>

                                        </svg>
                                        <!-- The gauge texts are edited in the gaugefunction.js file bcz it will be retrieving the info from the database -->
                                        <div class="gauge-text">
                                            <span id="serviceTotalValue"></span>
                                        </div>
                                    </div>
                                </div>
                                <div id="serviceRoleAmount">
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Patient Summary -->
                        <div class="summary-card">
                            <div class="section-heading">
                                <h2>
                                    Patients
                                </h2>
                                <a href="admin_patient_list.php">View all</a>
                            </div>
                            <!-- <h2>
                                18 Sep
                            </h2>

                            <p class="card-description">
                                10:00 AM · General Check-up
                            </p> -->
                            <div class="card-content">
                                <div class="doctor-summary">
                                    <div class="doctor-gauge">
                                        <svg class="gauge-circle" viewBox="0 0 200 200">
                                            <circle
                                                class="gauge-background"
                                                cx="100"
                                                cy="100"
                                                r="80">
                                            </circle>

                                            <g id="patientGaugeSegments"></g>

                                        </svg>
                                        <!-- The gauge texts are edited in the gaugefunction.js file bcz it will be retrieving the info from the database -->
                                        <div class="gauge-text">
                                            <span id="patientTotalValue"></span>
                                        </div>
                                    </div>
                                </div>
                                <div id="patientRoleAmount">
                                    
                                </div>
                            </div>
                    </section>
                    <?php
                        $appointmentsql = "SELECT  u.FullName, s.ServiceName, a.AppointmentDate, a.AppointmentTime, a.AppointmentStatus
                                        FROM appointment a
                                        INNER JOIN patient p  ON a.PatientID = p.PatientID
                                        INNER JOIN user u ON p.UserID = u.UserID
                                        INNER JOIN `service` s on a.ServiceID = s.ServiceID
                                        ORDER BY AppointmentID DESC
                                        LIMIT 5";
                        $appointmentstmt = $pdo->prepare($appointmentsql);
                        $appointmentstmt->execute();

                        $stocksql = "SELECT * FROM inventory WHERE QuantityInStock < 10 ORDER BY ReorderLevel ASC LIMIT 5";
                        $stockstmt = $pdo->prepare($stocksql);
                        $stockstmt->execute();
                    ?>
                    <div>
                    <section>
                        <div class="appointment-stock-container">
                            <div class="appointment">
                                <div class="section-heading">
                                    <div>
                                        <i></i>
                                        <h2>Recent Appointments</h2>
                                    </div>

                                    <a href="admin_appointment_list.php">View all</a>
                                </div>
                                <div>
                                    <table class="table appointment-table">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Service</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($appointment = $appointmentstmt->fetch()): ?>

                                                <tr>
                                                    <td><?=htmlspecialchars($appointment["FullName"])?></td>
                                                    <td><?=htmlspecialchars($appointment["ServiceName"])?></td>
                                                    <td><?=htmlspecialchars($appointment["AppointmentDate"])?> <?=htmlspecialchars($appointment["AppointmentTime"])?></td>
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
                                                </tr>

                                            <?php endwhile;?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="stock">
                                <div class="section-heading">
                                    <div>
                                        <i></i>
                                        <h2>Low Stock Items</h2>
                                    </div>

                                    <a href="admin_stock_list.php">View all</a>
                                </div>
                                <div>
                                    <table class="table appointment-table">
                                        <thead>
                                            <tr>
                                                <th>Item ID</th>
                                                <th>Item Name</th>
                                                <th>Current Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($stock = $stockstmt->fetch()): ?>

                                                <tr>
                                                    <td><?=htmlspecialchars($stock["ItemID"])?></td>
                                                    <td><?=htmlspecialchars($stock["ItemName"])?></td>
                                                    <td><?=htmlspecialchars($stock["QuantityInStock"])?></td>
                                                </tr>

                                            <?php endwhile;?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
        <script>

            <?php
                //This is for the dentist'
                $sql = "SELECT COUNT(*) AS Total FROM dentist;";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                $result = $stmt->fetch();

                $rows = $result["Total"];

                $dentistCount = [];

                $sqlCount = "SELECT dept.DepartmentName, COUNT(*) AS Amount 
                            FROM dentist d INNER JOIN department dept ON d.DeptID = dept.DeptID
                            GROUP BY d.DeptID";

                $count = $pdo->query($sqlCount);

                while ($row = $count->fetch()){
                    $dentistCount[$row["DepartmentName"]] = $row["Amount"];
                }

                //This is for the service
                $servicesql = "SELECT COUNT(*) AS Total FROM service;";

                $servicestmt = $pdo->prepare($servicesql);

                $servicestmt->execute();

                $serviceresult = $servicestmt->fetch();

                $servicerows = $serviceresult["Total"];

                $serviceCount = [];

                $servicesqlcount = "SELECT Category, COUNT(*) AS Amount FROM service GROUP BY Category";

                $servicecountstmt = $pdo->query($servicesqlcount);

                while ($row = $servicecountstmt->fetch()){
                    $serviceCount[$row["Category"]] = $row["Amount"];
                }

                //This is for the patient
                $patientsql = "SELECT COUNT(*) AS Total FROM patient;";

                $patientstmt = $pdo->prepare($patientsql);

                $patientstmt->execute();

                $patientresult = $patientstmt->fetch();

                $patientrows = $patientresult["Total"];

                $patientCount = [];

                $patientsqlcount = "SELECT Approval, COUNT(*) AS Amount FROM patient GROUP BY Approval";

                $patientcountstmt = $pdo->query($patientsqlcount);

                while ($row = $patientcountstmt->fetch()){
                    $patientCount[$row["Approval"]] = $row["Amount"];
                }


            ?>

            const dentistCount = <?= json_encode($dentistCount) ?>;
            const maximumValue = <?= $rows ?>;

            const serviceCount = <?=json_encode($serviceCount)?>;
            const serviceMax = <?=$servicerows?>;

            const patientCount = <?=json_encode($patientCount)?>;
            const patientMax = <?=$patientrows?>;

            const gaugeSegments = document.getElementById("dentistGaugeSegments");
            const valueText = document.getElementById("dentistTotalValue");

            const serviceGaugeSegments = document.getElementById("serviceGaugeSegments");
            const serviceValueText  =document.getElementById("serviceTotalValue");

            const patientGaugeSegments = document.getElementById("patientGaugeSegments");
            const patientValueText  =document.getElementById("patientTotalValue");

            const radius = 80;
            const circumference = 2 * Math.PI * radius;

            const colors = [
                "#42B1CC",
                "#F4A261",
                "#7B61FF",
                "#4CAF50",
                "#E76F51",
                "#E9C46A"
            ];

            let currentOffset = 0;
            let colorIndex = 0;

            valueText.textContent = maximumValue;
            serviceValueText.textContent = serviceMax;
            patientValueText.textContent = patientMax;

            const dentistRoleAmount = document.getElementById("dentistRoleAmount");
            const serviceRoleAmount = document.getElementById("serviceRoleAmount");
            const patientRoleAmount = document.getElementById("patientRoleAmount");

            for (const specialty in dentistCount){
                const amount = dentistCount[specialty];
                const percentage = maximumValue > 0 ? amount / maximumValue : 0;
                const segmentLength = circumference * percentage;
                const circle = document.createElementNS(
                    "http://www.w3.org/2000/svg",
                    "circle"
                );

                circle.setAttribute("cx", "100");
                circle.setAttribute("cy", "100");
                circle.setAttribute("r", "80");

                circle.setAttribute("fill", "none");
                circle.setAttribute("stroke", colors[colorIndex]);
                circle.setAttribute("stroke-width", "15");
                circle.setAttribute("stroke-linecap", "butt");

                circle.setAttribute(
                    "stroke-dasharray",
                    `${segmentLength} ${circumference - segmentLength}`
                );

                circle.setAttribute(
                    "stroke-dashoffset",
                    -currentOffset
                );

                gaugeSegments.appendChild(circle);

                currentOffset += segmentLength;


                const role = document.createElement("div");
                role.classList.add("role-item");
                role.innerHTML = `
                    <span class="role-color" style="background-color: ${colors[colorIndex]}"></span>
                    <span class="role-name">${specialty}</span>
                    <span class="role-count">${amount}</span>
                `;

                dentistRoleAmount.appendChild(role);

                colorIndex++;
            }

            let serviceCurrentOffset = 0;
            let serviceColorIndex = 0;

            for (const category in serviceCount){
                const amount = serviceCount[category];
                const percentage = serviceMax > 0 ? amount / serviceMax : 0;
                const segmentLength = circumference * percentage;
                const circle = document.createElementNS(
                    "http://www.w3.org/2000/svg",
                    "circle"
                );

                circle.setAttribute("cx", "100");
                circle.setAttribute("cy", "100");
                circle.setAttribute("r", "80");

                circle.setAttribute("fill", "none");
                circle.setAttribute("stroke", colors[serviceColorIndex]);
                circle.setAttribute("stroke-width", "15");
                circle.setAttribute("stroke-linecap", "butt");

                circle.setAttribute(
                    "stroke-dasharray",
                    `${segmentLength} ${circumference - segmentLength}`
                );

                circle.setAttribute(
                    "stroke-dashoffset",
                    -serviceCurrentOffset
                );

                serviceGaugeSegments.appendChild(circle);

                serviceCurrentOffset += segmentLength;


                const role = document.createElement("div");
                role.classList.add("role-item");
                role.innerHTML = `
                    <span class="role-color" style="background-color: ${colors[serviceColorIndex]}"></span>
                    <span class="role-name">${category}</span>
                    <span class="role-count">${amount}</span>
                `;

                serviceRoleAmount.appendChild(role);

                serviceColorIndex++;
            } 

            let patientCurrentOffset = 0;
            let patientColorIndex = 0;

            for (const approval in patientCount){
                const amount = patientCount[approval];
                const value = approval === "Yes" ? "Approved" : "Pending";

                const patientcolors = approval === "Yes" ? "#4CAF50" : "#E76F51";

                const percentage = patientMax > 0 ? amount / patientMax : 0;
                const segmentLength = circumference * percentage;
                const circle = document.createElementNS(
                    "http://www.w3.org/2000/svg",
                    "circle"
                );

                circle.setAttribute("cx", "100");
                circle.setAttribute("cy", "100");
                circle.setAttribute("r", "80");

                circle.setAttribute("fill", "none");
                circle.setAttribute("stroke", patientcolors);
                circle.setAttribute("stroke-width", "15");
                circle.setAttribute("stroke-linecap", "butt");

                circle.setAttribute(
                    "stroke-dasharray",
                    `${segmentLength} ${circumference - segmentLength}`
                );

                circle.setAttribute(
                    "stroke-dashoffset",
                    -patientCurrentOffset
                );

                patientGaugeSegments.appendChild(circle);

                patientCurrentOffset += segmentLength;


                const role = document.createElement("div");
                role.classList.add("role-item");
                role.innerHTML = `
                    <span class="role-color" style="background-color: ${patientcolors}"></span>
                    <span class="role-name">${value}</span>
                    <span class="role-count">${amount}</span>
                `;

                patientRoleAmount.appendChild(role);
            } 

        </script>
    </body>
</html>