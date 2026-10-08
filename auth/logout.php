<?php
require_once '../includes/helpers.php';
startSession();
$isAdmin = isset($_GET['admin']);
session_destroy();
if ($isAdmin) {
    header('Location: ../auth/admin-login.php');
} else {
    header('Location: ../index.php');
}
exit();
