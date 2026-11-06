<?php
// ============================================================
// RapidAid - Ambulance Domain Model
// ============================================================

class Ambulance {
    public $id;
    public $vehicle_number = '';
    public $vehicle_type = 'BLS';
    public $make = '';
    public $model = '';
    public $year;
    public $color = '';
    public $driver_id;
    public $driver_name = '';
    public $status = 'available';
    public $equipment = '';

    public function __construct(array $data = []) {
        foreach (['id', 'vehicle_number', 'vehicle_type', 'make', 'model', 'year', 'color', 'driver_id', 'driver_name', 'status', 'equipment'] as $field) {
            if (array_key_exists($field, $data)) {
                $this->$field = $data[$field];
            }
        }
    }

    public static function allWithDrivers(): array {
        $rows = db()->fetchAll(
            "SELECT a.*, d.full_name as driver_name
             FROM ambulances a
             LEFT JOIN drivers d ON a.driver_id = d.id
             ORDER BY a.created_at DESC"
        );

        return array_map(function (array $row): self {
            return new self($row);
        }, $rows);
    }

    public static function findById(int $id): ?self {
        $data = db()->fetchOne('SELECT * FROM ambulances WHERE id = ?', [$id]);
        return $data ? new self($data) : null;
    }

    public static function countByStatus(string $status): int {
        return db()->count('ambulances', 'status = ?', [$status]);
    }

    public function save(): int {
        $data = [
            'vehicle_number' => $this->vehicle_number,
            'vehicle_type' => $this->vehicle_type,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'driver_id' => $this->driver_id,
            'status' => $this->status,
            'equipment' => $this->equipment,
        ];

        if ($this->id) {
            db()->update('ambulances', $data, 'id = ?', [$this->id]);
            return (int)$this->id;
        }

        $this->id = db()->insert('ambulances', $data);
        return (int)$this->id;
    }

    public function getVehicleLabel(): string {
        $name = trim($this->make . ' ' . $this->model);
        return $name !== '' ? $name : $this->vehicle_type;
    }

    public function isAvailable(): bool {
        return $this->status === 'available';
    }

    public function deactivate(): void {
        if (!$this->id) {
            return;
        }

        db()->update('ambulances', ['status' => 'inactive', 'driver_id' => null], 'id = ?', [$this->id]);
        $this->status = 'inactive';
        $this->driver_id = null;
    }
}