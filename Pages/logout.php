<?php
require_once __DIR__ . '/../includes/patient_helpers.php';
$_SESSION = [];
session_destroy();
header('Location: authentication.php');
exit;