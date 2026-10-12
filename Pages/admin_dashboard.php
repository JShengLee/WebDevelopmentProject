<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Admin Dashboard | BrightSmile Dental Clinic</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/admin_homepage.css?v=1">
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
                            <h1>
                                Good day, Joshua 👋
                            </h1>
                        </div>
                    </section>

                    <section class="summary-grid">
                        <!-- Available Doctors -->
                        <div class="summary-card">
                            <div class="section-heading">
                                <h2>
                                    Doctors
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

                                            <g id="gaugeSegments"></g>

                                        </svg>
                                        <!-- The gauge texts are edited in the gaugefunction.js file bcz it will be retrieving the info from the database -->
                                        <div class="gauge-text">
                                            <span id="totalValue"></span>
                                        </div>
                                    </div>
                                </div>
                                <div id="roleAmount">
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Treatment Progress -->
                        <div class="summary-card">
                            <div class="card-top">
                                <div class="card-icon treatment-icon">
                                    %
                                </div>

                                <span class="card-status">
                                    Ongoing
                                </span>
                            </div>

                            <p class="card-label">
                                TREATMENT PROGRESS
                            </p>

                            <h2>
                                65%
                            </h2>

                            <div class="progress-bar">
                                <div class="progress-value"></div>
                            </div>

                            <p class="card-description">
                                Orthodontic treatment
                            </p>
                        </div>

                        <!-- Dental Records -->
                        <div class="summary-card">
                            <div class="card-top">
                                <div class="card-icon records-icon">
                                    +
                                </div>

                                <span class="card-status">
                                    Updated
                                </span>
                            </div>

                            <p class="card-label">
                                DENTAL RECORDS
                            </p>

                            <h2>
                                4
                            </h2>

                            <p class="card-description">
                                Recorded dental visits
                            </p>
                        </div>
                    </section>

                    <!-- Dentist Carousel -->
                    <div>
                    <section class="dashboard-card activity-card">
                        <div>
                            Something
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
        <script>

            <?php
                include "../dbconnection.php";

                $sql = "SELECT * FROM dentist;";
                $stmt = $conn->prepare($sql);
                $stmt->execute();

                $result = $stmt->get_result();

                $rows = $result->num_rows;

                $dentistCount = [];

                $sqlCount = "SELECT Specialty, COUNT(*) AS Amount FROM dentist GROUP BY Specialty";

                $count = $conn->query($sqlCount);

                while ($row = $count->fetch_assoc()){
                    $dentistCount[$row["Specialty"]] = $row["Amount"];
                }
            ?>

            const dentistCount = <?= json_encode($dentistCount) ?>;
            let maximumValue = <?= $rows ?>;

            const gaugeSegments = document.getElementById("gaugeSegments");
            const valueText = document.getElementById("totalValue");
            const totalText = document.getElementById("maximumValue");

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

            const roleAmount = document.getElementById("roleAmount");

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

                roleAmount.appendChild(role);

                colorIndex++;
            }

        </script>
    </body>
</html>