<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $req = db()->fetchOne("SELECT * FROM emergency_requests WHERE id=? AND user_id=?", [$id, $user['id']]);
    if ($req && in_array($req['status'], ['pending','accepted'])) {
        db()->update('emergency_requests', ['status'=>'cancelled'], 'id=?', [$id]);
        if ($req['ambulance_id']) db()->update('ambulances', ['status'=>'available'], 'id=?', [$req['ambulance_id']]);
        createNotification($user['id'], null, 'Request Cancelled', 'Your emergency request '.$req['request_code'].' has been cancelled.', 'warning');
    }
}
header('Location: ../user/emergency-history.php');
exit();
