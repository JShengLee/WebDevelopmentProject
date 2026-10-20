<?php
function generate_role_user_id(PDO $pdo, string $role): int
{
    $roleSettings = [
        'Patient' => ['starting_id' => 1000, 'table' => 'patient', 'id_column' => 'PatientID'],
        'Admin' => ['starting_id' => 2000, 'table' => 'admin', 'id_column' => 'AdminID'],
        'Dentist' => ['starting_id' => 3000, 'table' => 'dentist', 'id_column' => 'DentistID'],
    ];

    if (!isset($roleSettings[$role])) {
        throw new InvalidArgumentException('Unsupported user role.');
    }

    if (!$pdo->inTransaction()) {
        throw new LogicException('Role IDs must be generated inside a transaction.');
    }

    $lockName = 'brightsmile_user_id_creation';
    $lockStatement = $pdo->prepare('SELECT GET_LOCK(:lock_name, 10)');
    $lockStatement->execute(['lock_name' => $lockName]);

    if ((int) $lockStatement->fetchColumn() !== 1) {
        throw new RuntimeException('Unable to reserve a user ID. Please try again.');
    }

    try {
        $settings = $roleSettings[$role];
        $userMaxStatement = $pdo->prepare(
            'SELECT MAX(UserID) FROM user WHERE Role = :role'
        );
        $userMaxStatement->execute(['role' => $role]);
        $maxUserId = $userMaxStatement->fetchColumn();

        $profileMaxStatement = $pdo->query(
            "SELECT MAX({$settings['id_column']}) FROM {$settings['table']}"
        );
        $maxProfileId = $profileMaxStatement->fetchColumn();

        $nextID = max(
            $settings['starting_id'] + 1,
            $maxUserId === false || $maxUserId === null ? 0 : (int) $maxUserId + 1,
            $maxProfileId === false || $maxProfileId === null ? 0 : (int) $maxProfileId + 1
        );

        $userIDStatement = $pdo->prepare('SELECT 1 FROM user WHERE UserID = :user_id');
        do {
            $userIDStatement->execute(['user_id' => $nextID]);
            $idExists = $userIDStatement->fetchColumn() !== false;
            if ($idExists) {
                $nextID++;
            }
        } while ($idExists);

        return $nextID;
    } catch (Throwable $exception) {
        release_role_user_id_lock($pdo);
        throw $exception;
    }
}

function release_role_user_id_lock(PDO $pdo): void
{
    try {
        $statement = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $statement->execute(['lock_name' => 'brightsmile_user_id_creation']);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
    }
}