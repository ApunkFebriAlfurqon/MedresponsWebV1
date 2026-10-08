<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
db()->update('notifications', ['is_read'=>1], 'user_id=?', [$user['id']]);
header('Content-Type: application/json');
echo json_encode(['success'=>true]);
