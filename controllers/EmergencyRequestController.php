<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/EmergencyRequest.php';
require_once __DIR__ . '/../models/Hospital.php';

class EmergencyRequestController {
    public function index(): array {
        requireUserLogin();
        $user = currentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->process($user);
        }

        return [
            'user' => $user,
            'hospitals' => Hospital::activeForSelection(),
            'subscription' => getUserActiveSubscription($user['id']),
        ];
    }

    private function process(array $user): void {
        $requestId = EmergencyRequest::create([
            'user_id' => (int)$user['id'],
            'pickup_address' => clean($_POST['pickup_address'] ?? ''),
            'pickup_latitude' => $_POST['lat'] ?? null,
            'pickup_longitude' => $_POST['lng'] ?? null,
            'destination_address' => clean($_POST['destination'] ?? ''),
            'patient_name' => clean($_POST['patient_name'] ?? ''),
            'patient_age' => (int)($_POST['patient_age'] ?? 0),
            'condition_description' => clean($_POST['condition'] ?? ''),
            'hospital_id' => ($_POST['hospital_id'] ?? '') ?: null,
            'priority' => $_POST['priority'] ?? 'medium',
        ]);

        header('Location: track-request.php?id=' . $requestId);
        exit();
    }
}
