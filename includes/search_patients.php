<?php
    require_once __DIR__ . '/../includes/patient_bootstrap.php';

    $search = trim($_GET['search'] ?? '');

    if (strlen($search) < 2) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT p.PatientID, u.FullName
            FROM patient p
            INNER JOIN user u ON p.UserID = u.UserID
            WHERE u.FullName LIKE ?
            AND p.Approval = 'Yes'
            ORDER BY u.FullName ASC
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $searchTerm = '%' . $search . '%';
    $stmt->execute([
        $searchTerm
    ]);

    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($patients);

    ?>