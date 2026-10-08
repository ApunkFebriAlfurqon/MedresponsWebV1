<?php
require_once '../includes/helpers.php';
requireAdminLogin();
$id = (int)($_POST['id'] ?? 0);
$status = (int)($_POST['status'] ?? 0);
if ($id) db()->update('users', ['is_active'=>$status], 'id=?', [$id]);
setFlash('success', 'User status updated.');
header('Location: ../admin/users.php');
exit();
