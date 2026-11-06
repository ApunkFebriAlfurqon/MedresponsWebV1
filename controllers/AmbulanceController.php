<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Ambulance.php';
require_once __DIR__ . '/../models/Driver.php';

class AmbulanceController {
    public function handle(): array {
        requireAdminLogin();

        $action = $_GET['action'] ?? 'list';
        $id = (int)($_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'vehicle_number' => clean($_POST['vehicle_number']),
                'vehicle_type'   => $_POST['vehicle_type'],
                'make'           => clean($_POST['make']),
                'model'          => clean($_POST['model']),
                'year'           => (int)$_POST['year'],
                'color'          => clean($_POST['color']),
                'driver_id'      => $_POST['driver_id'] ?: null,
                'status'         => $_POST['status'],
                'equipment'      => clean($_POST['equipment'] ?? ''),
            ];

            if ($action === 'add') {
                $ambulance = new Ambulance($data);
                $ambulance->save();
                setFlash('success', 'Ambulance added successfully!');
            } elseif ($action === 'edit' && $id) {
                $ambulance = new Ambulance(array_merge($data, ['id' => $id]));
                $ambulance->save();
                setFlash('success', 'Ambulance updated successfully!');
            }

            header('Location: ambulances.php');
            exit();
        }

        if ($action === 'delete' && $id) {
            $ambulance = Ambulance::findById($id);
            if ($ambulance) {
                $ambulance->deactivate();
            }
            setFlash('success', 'Ambulance removed.');
            header('Location: ambulances.php');
            exit();
        }

        $statusCounts = [];
        foreach (['available', 'on_duty', 'maintenance', 'inactive'] as $status) {
            $statusCounts[$status] = Ambulance::countByStatus($status);
        }

        return [
            'ambulances' => Ambulance::allWithDrivers(),
            'drivers' => Driver::activeForAssignment(),
            'editItem' => $id ? Ambulance::findById($id) : null,
            'flash' => getFlash(),
            'statusCounts' => $statusCounts,
        ];
    }
}
