<?php
    require_once __DIR__ . '/../includes/patient_bootstrap.php';

    $search = trim($_GET['search'] ?? '');

    if (strlen($search) < 2) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT ServiceID, ServiceName
            FROM service
            WHERE ServiceName LIKE ?
            ORDER BY ServiceName ASC
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $searchTerm = '%' . $search . '%';
    $stmt->execute([
        $searchTerm
    ]);

    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($services);

    ?>