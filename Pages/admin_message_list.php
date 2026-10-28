<?php
    require_once __DIR__ . '/../includes/patient_bootstrap.php';
    require_once __DIR__ . '/../email_helper.php';
    // include "../dbconnection.php";
?>

<?php
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "markAsRead"
) {
    $messageID = filter_input(INPUT_POST, "messageID", FILTER_VALIDATE_INT);

    if ($messageID !== false && $messageID !== null && $messageID > 0) {
        $updateSql = "UPDATE message SET ReadStatus = 'Read'
                      WHERE MessageID = ? AND ReadStatus = 'Unread'";

        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$messageID]);
    }

    $redirectParams = [
        "status" => $_GET["status"] ?? "All",
        "search" => $_GET["search"] ?? "",
        "page" => $_GET["page"] ?? 1,
        "messageID" => $messageID
    ];

    header("Location: ?" . http_build_query($redirectParams));
    exit;
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
    <link rel="stylesheet" href="../Styling/admin_message_list.css?v=4">
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
                <span class="page-label"> MESSAGE </span>
                
                <?php
                $status = $_GET["status"] ?? "All";
                $search = trim($_GET["search"] ?? "");

                $messagesPerPage = 5;
                $currentPage = max(1, (int)($_GET["page"] ?? 1));

                $whereSql = " FROM message WHERE 1 = 1";
                $params = [];

                if ($status === "Unread" || $status === "Read") {
                    $whereSql .= " AND ReadStatus = ?";
                    $params[] = $status;
                }

                if ($search !== "") {
                    $whereSql .= " AND (FullName LIKE ? OR Subject LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }

                // Count matching messages
                $countStmt = $pdo->prepare("SELECT COUNT(*)" . $whereSql);
                $countStmt->execute($params);
                $totalMessages = (int)$countStmt->fetchColumn();

                $totalPages = max(1, (int)ceil($totalMessages / $messagesPerPage));

                // Keep the current page within valid bounds
                $currentPage = min($currentPage, $totalPages);
                $offset = ($currentPage - 1) * $messagesPerPage;

                // Fetch only messages for this page
                $messageSql = "SELECT *" . $whereSql
                    . " ORDER BY MessageTimeStamp DESC LIMIT $messagesPerPage OFFSET $offset";

                $messageStmt = $pdo->prepare($messageSql);
                $messageStmt->execute($params);

                // Preserve filters and search when navigating pages
                $queryParams = [
                    "status" => $status,
                    "search" => $search
                ];
                ?>

                <div class="email-content">
                    <div class="left-content">
                        <div class="left-header">
                            <div class="selection">
                                <form method="GET">
                                    <a href="?status=All" class="all <?=$status === 'All' ? 'active' : '' ?>">All</a>
                                    <a href="?status=Unread" class="unread <?=$status === 'Unread' ? 'active' : '' ?>">Unread</a>
                                    <a href="?status=Read" class="read <?=$status === 'Read' ? 'active' : '' ?>">Read</a>
                                </form>
                            </div>

                            <div>
                                <form class="searchbar" method="GET">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="Search by name or subject..." class="search" id="search" name="search"/>
                                    <i class="far fa-times-circle" onclick="clearSearch()"></i>
                                </form>
                            </div>
                        </div>

                        <div class="message-list">
                            <?php while ($message = $messageStmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <a
                                    class="message-link"
                                    href="?status=<?= urlencode($status) ?>&amp;search=<?= urlencode($search) ?>&amp;messageID=<?= (int)$message['MessageID'] ?>"
                                >
                                    <div class="message <?= (isset($messageID) && $messageID == $message['MessageID']) ? 'selected' : '' ?>">
                                        <div class="message-preview">
                                            <p class="message-preview-name"><?= htmlspecialchars($message["FullName"] ?? "", ENT_QUOTES, 'UTF-8') ?></p>
                                            <p><?= htmlspecialchars($message["Subject"] ?? "", ENT_QUOTES, 'UTF-8') ?></p>
                                            <p class="message-preview-text"><?= htmlspecialchars($message["MessageContent"] ?? "", ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>

                                        <div class="message-meta">
                                            <p><?= htmlspecialchars($message["MessageTimeStamp"] ?? "", ENT_QUOTES, 'UTF-8') ?></p>

                                            <?php if (($message["ReadStatus"] ?? "") === "Unread"): ?>
                                                <i class="fas fa-circle unread-dot"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                        <div class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <?php
                                $queryParams["page"] = $currentPage - 1;
                                ?>
                                <a href="?<?= htmlspecialchars(http_build_query($queryParams), ENT_QUOTES, 'UTF-8') ?>"
                                class="page-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php else: ?>
                                <span class="page-btn disabled">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </span>
                            <?php endif; ?>

                            <span class="page-info">
                                Page <?= $currentPage ?> of <?= $totalPages ?>
                            </span>

                            <?php if ($currentPage < $totalPages): ?>
                                <?php
                                $queryParams["page"] = $currentPage + 1;
                                ?>
                                <a href="?<?= htmlspecialchars(http_build_query($queryParams), ENT_QUOTES, 'UTF-8') ?>"
                                class="page-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-btn disabled">
                                    Next <i class="fas fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    $selectedMessage = null;
                    $messageID = filter_input(INPUT_GET, 'messageID', FILTER_VALIDATE_INT);

                    if ($messageID !== false && $messageID !== null && $messageID > 0) {
                        $detailSql = "SELECT * FROM message WHERE MessageID = ?";
                        $detailStmt = $pdo->prepare($detailSql);
                        $detailStmt->execute([$messageID]);
                        $selectedMessage = $detailStmt->fetch(PDO::FETCH_ASSOC);
                    }
                    ?>

                    <div class="right-content">
    <?php if ($selectedMessage): ?>

        <div class="message-detail">
            <div class="detail-header">
                <h2>
                    <?= htmlspecialchars($selectedMessage["Subject"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <?php if (($selectedMessage["ReadStatus"] ?? "") === "Unread"): ?>
                    <form method="POST" class="mark-read-form">
                        <input type="hidden" name="action" value="markAsRead">
                        <input type="hidden" name="messageID"
                               value="<?= (int)$selectedMessage["MessageID"] ?>">

                        <button type="submit" class="mark-read-btn">
                            <i class="fas fa-envelope-open"></i>
                            Mark as Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="sender-details">
                <div class="sender-info">
                    <p>
                        <strong>From:</strong>
                        <?= htmlspecialchars($selectedMessage["FullName"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>
                        <strong>Email:</strong>
                        <?= htmlspecialchars($selectedMessage["Email"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>

                <div class="message-date">
                    <p>
                        <?= htmlspecialchars($selectedMessage["MessageTimeStamp"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <?php if (($selectedMessage["ReadStatus"] ?? "") === "Unread"): ?>
                        <span class="unread-badge">Unread</span>
                    <?php else: ?>
                        <span class="read-badge">Read</span>
                    <?php endif; ?>
                </div>
            </div>

            <hr>

            <div class="full-message">
                <?= nl2br(htmlspecialchars($selectedMessage["MessageContent"] ?? "", ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>

    <?php else: ?>

        <div class="no-message-selected">
            <i class="far fa-envelope"></i>
            <p>Select a message to read its details.</p>
        </div>

    <?php endif; ?>
</div>
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
        </script>
</body>
</html>