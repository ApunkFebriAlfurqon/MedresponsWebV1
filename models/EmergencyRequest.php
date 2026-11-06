<?php

class EmergencyRequest {
    public static function create(array $data): int {
        $code = generateRequestCode();
        $id = db()->insert('emergency_requests', [
            'request_code' => $code,
            'user_id' => $data['user_id'],
            'pickup_address' => $data['pickup_address'],
            'pickup_latitude' => $data['pickup_latitude'],
            'pickup_longitude' => $data['pickup_longitude'],
            'destination_address' => $data['destination_address'],
            'patient_name' => $data['patient_name'],
            'patient_age' => $data['patient_age'],
            'condition_description' => $data['condition_description'],
            'hospital_id' => $data['hospital_id'],
            'priority' => $data['priority'],
            'status' => 'pending',
        ]);

        $ambulance = db()->fetchOne("SELECT * FROM ambulances WHERE status = 'available' LIMIT 1");
        if ($ambulance) {
            db()->update('emergency_requests', [
                'ambulance_id' => $ambulance['id'],
                'driver_id' => $ambulance['driver_id'],
                'status' => 'accepted',
                'assigned_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);
            db()->update('ambulances', ['status' => 'on_duty'], 'id = ?', [$ambulance['id']]);
        }

        createNotification(
            (int)$data['user_id'],
            null,
            'Emergency Request Submitted',
            "Your request $code has been submitted and is being processed.",
            'success'
        );

        return $id;
    }
}
