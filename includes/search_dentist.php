<?php
    require_once __DIR__ . '/../includes/patient_bootstrap.php';

    $search = trim($_GET['search'] ?? '');

    if (strlen($search) < 2) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT d.DentistID, u.FullName
            FROM dentist d
            INNER JOIN user u ON d.UserID = u.UserID
            WHERE u.FullName LIKE ?
            ORDER BY d.DentistID ASC, u.FullName ASC
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $searchTerm = '%' . $search . '%';
    $stmt->execute([
        $searchTerm
    ]);

    $dentists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($dentists);

    ?>