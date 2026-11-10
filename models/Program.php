<?php
// ============================================================
// RapidAid - Program Model
// ============================================================

class Program {
    public ?int $id = null;
    public string $nama = '';
    public ?string $deskripsi = null;

    public function __construct(array $data = []) {
        if (isset($data['id'])) {
            $this->id = (int)$data['id'];
        }

        if (isset($data['nama'])) {
            $this->nama = (string)$data['nama'];
        }

        if (array_key_exists('deskripsi', $data)) {
            $this->deskripsi = $data['deskripsi'] !== null ? (string)$data['deskripsi'] : null;
        }
    }

    /** @return self[] */
    public static function all(): array {
        $rows = db()->fetchAll('SELECT * FROM programs');

        return array_map(static function (array $row): self {
            return new self($row);
        }, $rows);
    }

    public static function find(int $id): ?self {
        $row = db()->fetchOne('SELECT * FROM programs WHERE id = ?', [$id]);
        return $row ? new self($row) : null;
    }

    public static function create(array $data): self {
        if (!isset($data['nama']) || trim((string)$data['nama']) === '') {
            throw new InvalidArgumentException('Nama program wajib diisi.');
        }

        $id = db()->insert('programs', [
            'nama' => trim((string)$data['nama']),
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);

        return new self([
            'id' => $id,
            'nama' => trim((string)$data['nama']),
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);
    }

    public function save(): int {
        if (trim($this->nama) === '') {
            throw new InvalidArgumentException('Nama program wajib diisi.');
        }

        $data = [
            'nama' => trim($this->nama),
            'deskripsi' => $this->deskripsi !== null ? trim($this->deskripsi) : null,
        ];

        if ($this->id !== null) {
            db()->update('programs', $data, 'id = ?', [$this->id]);
            return $this->id;
        }

        $this->id = db()->insert('programs', $data);
        return $this->id;
    }

    public function delete(): bool {
        if ($this->id === null) {
            return false;
        }

        return db()->delete('programs', 'id = ?', [$this->id]) > 0;
    }
}
