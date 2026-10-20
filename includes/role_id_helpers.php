<?php
function generate_role_user_id(PDO $pdo, string $role): int
{
    $startingIDs = [
        'Patient' => 1000,
        'Admin' => 2000,
        'Dentist' => 3000,
    ];

    if (!isset($startingIDs[$role])) {
        throw new InvalidArgumentException('Unsupported user role.');
    }

    if (!$pdo->inTransaction()) {
        throw new LogicException('Role IDs must be generated inside a transaction.');
    }

    $sequenceRows = $pdo->query(
        'SELECT Role, LastID FROM role_id_sequence ORDER BY Role FOR UPDATE'
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    if (count($sequenceRows) !== count($startingIDs) || !isset($sequenceRows[$role])) {
        throw new RuntimeException('Role ID sequences are not initialized.');
    }

    $nextID = max($startingIDs[$role] + 1, (int) $sequenceRows[$role] + 1);
    $userIDStatement = $pdo->prepare('SELECT 1 FROM user WHERE UserID = :user_id');
    do {
        $userIDStatement->execute(['user_id' => $nextID]);
        $idExists = $userIDStatement->fetchColumn() !== false;
        if ($idExists) {
            $nextID++;
        }
    } while ($idExists);

    $updateStatement = $pdo->prepare(
        'UPDATE role_id_sequence SET LastID = :last_id WHERE Role = :role'
    );
    $updateStatement->execute(['last_id' => $nextID, 'role' => $role]);

    return $nextID;
}