<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

// Verify authentication & fetch current logged-in dentist
$currentDentist = require_patient($pdo);

// Retrieve ALL patients with global Last Visit & Latest Treatment history using PDO
$sql = "SELECT
            p.PatientID,
            u.FullName,
            u.Phone,
            p.DateOfBirth,
            p.Gender,

            /* Global Last Visit across all dentists */
            (
                SELECT MAX(dr.RecordDate)
                FROM dentalrecord dr
                WHERE dr.PatientID = p.PatientID
            ) AS LastVisit,

            /* Global Latest Treatment across all dentists */
            COALESCE(
                (
                    SELECT t.TreatmentName
                    FROM treatment t
                    JOIN dentalrecord dr ON t.RecordID = dr.RecordID
                    WHERE dr.PatientID = p.PatientID
                    ORDER BY t.TreatmentDate DESC, t.TreatmentID DESC
                    LIMIT 1
                ),
                (
                    SELECT ortho.TreatmentType
                    FROM orthodonticrecord ortho
                    WHERE ortho.PatientID = p.PatientID
                    ORDER BY ortho.StartDate DESC
                    LIMIT 1
                ),
                (
                    SELECT s.ServiceName
                    FROM appointment a
                    JOIN service s ON a.ServiceID = s.ServiceID
                    WHERE a.PatientID = p.PatientID
                    ORDER BY a.AppointmentDate DESC, a.AppointmentTime DESC
                    LIMIT 1
                ),
                'No treatment recorded'
            ) AS LatestTreatment

        FROM patient p
        JOIN user u ON p.UserID = u.UserID
        ORDER BY p.PatientID ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();

$patients = [];

foreach ($rows as $row) {
    // Calculate patient age
    $age = '-';

    if (!empty($row['DateOfBirth'])) {
        $birthDate = new DateTime($row['DateOfBirth']);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;
    }

    // Generate initials from patient name
    $nameParts = preg_split('/\s+/', trim($row['FullName']));
    $initials = '';

    foreach ($nameParts as $part) {
        if ($part !== '') {
            $initials .= mb_substr($part, 0, 1);
        }

        if (mb_strlen($initials) >= 2) {
            break;
        }
    }

    $patients[] = [
        'id' => (int) $row['PatientID'],
        'name' => $row['FullName'],
        'phone' => $row['Phone'] ?: '-',
        'gender' => $row['Gender'] ?: '-',
        'age' => $age,
        'initials' => mb_strtoupper($initials),
        'lastVisit' => !empty($row['LastVisit'])
            ? date('d M Y', strtotime($row['LastVisit']))
            : 'No visits yet',
        'treatment' => $row['LatestTreatment']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Patients | BrightSmile Dental Clinic</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/dentist_patients.css">
    </head>

    <body>
        <div class="app-container">
            <!-- Sidebar -->
            <?php
                include '../Components/Sidebar.php';
            ?>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Header -->
                <?php
                    include '../Components/Header.php';
                ?>


                <!-- Page Content -->
                <div class="page-content">
                    <!-- Page Heading -->
                    <section class="page-heading">
                        <div>
                            <h1>
                                Patients & Dental Records
                            </h1>

                            <p>
                                Select a patient to review dental histories or create new examination notes, diagnoses, and prescriptions.
                            </p>
                        </div>
                    </section>

                    <!-- Patient Card -->
                    <section class="patient-card">
                        <div class="patient-card-header">
                            <div>
                                <h2>
                                    Patient List
                                </h2>

                                <p>
                                    Search or select a patient below to open their clinical file.
                                </p>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="search-box">
                            <span>
                                ⌕
                            </span>

                            <input type="text" id="patientSearch" placeholder="Search patient name or patient ID...">
                        </div>

                        <!-- Patient Table -->
                        <div class="patient-table-container">
                            <table class="patient-table">
                                <thead>
                                    <tr>
                                        <th>
                                            Patient ID
                                        </th>

                                        <th>
                                            Patient
                                        </th>

                                        <th>
                                            Contact
                                        </th>

                                        <th>
                                            Last Visit
                                        </th>

                                        <th>
                                            Treatment
                                        </th>

                                        <th>
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                <tbody id="patientTableBody">

                                    <?php if (count($patients) > 0): ?>

                                        <?php foreach ($patients as $patient): ?>

                                            <tr>
                                                <!-- Patient ID -->
                                                <td>
                                                    <strong>
                                                        <?= escape_html($patient['id']) ?>
                                                    </strong>
                                                </td>

                                                <!-- Patient information -->
                                                <td>
                                                    <div class="patient-info">

                                                        <div class="patient-avatar">
                                                            <?= escape_html($patient['initials']) ?>
                                                        </div>

                                                        <div>
                                                            <strong>
                                                                <?= escape_html($patient['name']) ?>
                                                            </strong>

                                                            <span>
                                                                <?= escape_html($patient['gender']) ?>
                                                                ·
                                                                <?= escape_html($patient['age']) ?>
                                                                <?= is_numeric($patient['age']) ? 'years old' : '' ?>
                                                            </span>
                                                        </div>

                                                    </div>
                                                </td>

                                                <!-- Contact -->
                                                <td>
                                                    <?= escape_html($patient['phone']) ?>
                                                </td>

                                                <!-- Last visit -->
                                                <td>
                                                    <?= escape_html($patient['lastVisit']) ?>
                                                </td>

                                                <!-- Latest treatment -->
                                                <td>
                                                    <span class="treatment-badge">
                                                        <?= escape_html($patient['treatment']) ?>
                                                    </span>
                                                </td>

                                                <!-- View patient record -->
                                                <td>
                                                    <a
                                                        href="dentist_patient_record.php?id=<?= (int) $patient['id'] ?>"
                                                        class="view-button"
                                                    >
                                                        View
                                                    </a>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No patients found for this dentist.
                                            </td>
                                        </tr>

                                    <?php endif; ?>

                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('patientSearch');
            const tableBody = document.getElementById('patientTableBody');

            if (!searchInput || !tableBody) return;

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase().trim();
                const rows = tableBody.querySelectorAll('tr');

                let visibleCount = 0;

                rows.forEach(row => {
                    // Skip the single-cell placeholder row if it exists
                    if (row.cells.length === 1) return;

                    // Cell 0: Patient ID (e.g., P001)
                    // Cell 1: Patient Name section
                    const patientIdCell = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
                    
                    // Extract only the strong tag text (Patient Full Name) from the second column
                    const nameElement = row.cells[1] ? row.cells[1].querySelector('strong') : null;
                    const patientName = nameElement ? nameElement.textContent.toLowerCase() : '';

                    // Check if search query exists in Patient ID OR Patient Name
                    if (patientIdCell.includes(query) || patientName.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide custom "No matching patients found" message
                let noResultsRow = document.getElementById('noSearchResultsRow');

                if (visibleCount === 0 && query !== '') {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.id = 'noSearchResultsRow';
                        noResultsRow.innerHTML = `
                            <td colspan="6" style="text-align: center; padding: 25px 10px; color: #9aa5aa; font-size: 13px;">
                                No matching patients found.
                            </td>
                        `;
                        tableBody.appendChild(noResultsRow);
                    }
                    noResultsRow.style.display = '';
                } else if (noResultsRow) {
                    noResultsRow.style.display = 'none';
                }
            });
        });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
    </body>
</html>