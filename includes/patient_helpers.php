<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/view_helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// function require_patient(PDO $pdo): array
// {
//     if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Patient') {
//         header('Location: authentication.php');
//         exit;
//     }

//     $statement = $pdo->prepare(
//         'SELECT u.UserID, u.FullName, u.Email, u.Phone, p.PatientID, p.DateOfBirth, p.Gender
//          FROM user u
//          INNER JOIN patient p ON p.UserID = u.UserID
//          WHERE u.UserID = :user_id AND u.Role = \'Patient\''
//     );
//     $statement->execute(['user_id' => $_SESSION['user_id']]);
//     $patient = $statement->fetch();

//     if (!$patient) {
//         $_SESSION = [];
//         session_destroy();
//         header('Location: authentication.php');
//         exit;
//     }

//     return $patient;
// }

function require_patient(PDO $pdo): array
{
    if (!empty($_SESSION["user_id"])){
        if (($_SESSION["role"] ?? "") === "Patient"){
            $statement = $pdo->prepare(
                'SELECT u.UserID, u.FullName, u.Email, u.Phone, u.ic_number, p.PatientID, p.DateOfBirth, p.Gender
                FROM user u
                INNER JOIN patient p ON p.UserID = u.UserID
                WHERE u.UserID = :user_id AND u.Role = \'Patient\''
            );
            $statement->execute(['user_id' => $_SESSION['user_id']]);
            $patient = $statement->fetch();

            if (!$patient) {
                $_SESSION = [];
                session_destroy();
                header('Location: authentication.php');
                exit;
            }

            return $patient;
        } else if (($_SESSION["role"] ?? "") === "Dentist"){
            $stmt = $pdo->prepare(
                'SELECT u.UserID, u.FullName, u.Email, u.Phone, d.DentistID, d.DeptID, d.Specialty
                FROM user u
                INNER JOIN dentist d ON d.UserID = u.UserID
                WHERE u.UserID = :user_id AND u.Role = \'Dentist\''
            );
            $stmt->execute(["user_id" => $_SESSION["user_id"]]);
            $dentist = $stmt->fetch();

            if (!$dentist){
                $_SESSION = [];
                session_destroy();
                header("Location: authentication.php");
                exit;
            }

            return $dentist;
        } else if (($_SESSION["role"] ?? "") === "Admin"){
            $stmt = $pdo->prepare(
                'SELECT u.UserID, u.FullName, u.Email, u.Phone, a.AdminID
                FROM user u
                INNER JOIN `admin` a ON a.UserID = u.UserID
                WHERE u.UserID = :user_id AND u.Role = \'Admin\''
            );
            $stmt->execute(["user_id" => $_SESSION["user_id"]]);
            $admin = $stmt->fetch();

            if (!$admin){
                $_SESSION = [];
                session_destroy();
                header("Location: authentication.php");
                exit;
            }

            return $admin;
        } else {
            $_SESSION = [];
            session_destroy();
            header('Location: authentication.php');
            exit;
        }
    } else {
        $_SESSION = [];
        session_destroy();
        header('Location: authentication.php');
        exit;
    }
}