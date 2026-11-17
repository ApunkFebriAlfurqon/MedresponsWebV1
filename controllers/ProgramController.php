<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Program.php';

class ProgramController {
    /** @return array{programs: Program[], formProgram: Program, isEditing: bool, flash: ?array, csrfToken: string, validationErrors: array<string, string>} */
    public function handle(): array {
        requireAdminLogin();
        $this->ensureCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->hasValidCsrfToken()) {
                setFlash('danger', 'Permintaan tidak valid. Silakan coba lagi.');
                header('Location: programs.php');
                exit();
            }

            $action = (string)($_POST['action'] ?? '');
            $id = (int)($_POST['id'] ?? 0);

            try {
                if ($action === 'create') {
                    $this->store($this->requestData());
                } elseif ($action === 'update' && $id > 0) {
                    $this->update($id, $this->requestData());
                } elseif ($action === 'delete' && $id > 0) {
                    $this->destroy($id);
                } else {
                    setFlash('danger', 'Aksi program tidak valid.');
                }
            } catch (ProgramValidationException $exception) {
                http_response_code(422);
                return $this->index($this->requestData(), $exception->errors, $action, $id);
            } catch (InvalidArgumentException $exception) {
                setFlash('danger', $exception->getMessage());
            }

            header('Location: programs.php');
            exit();
        }

        return $this->index();
    }

    /** @return array{programs: Program[], formProgram: Program, isEditing: bool, flash: ?array, csrfToken: string, validationErrors: array<string, string>} */
    public function index(?array $submittedData = null, array $validationErrors = [], ?string $submittedAction = null, int $submittedId = 0): array {
        if ($submittedData !== null) {
            $isEditing = $submittedAction === 'update' && $submittedId > 0;
            $formProgram = new Program([
                'id' => $isEditing ? $submittedId : null,
                'nama' => $submittedData['nama'] ?? '',
                'deskripsi' => $submittedData['deskripsi'] ?? '',
            ]);
        } else {
            $editingId = ($_GET['action'] ?? '') === 'edit' ? (int)($_GET['id'] ?? 0) : 0;
            $editingProgram = $editingId > 0 ? $this->edit($editingId) : null;

            if ($editingId > 0 && !$editingProgram) {
                setFlash('danger', 'Program tidak ditemukan.');
            }

            $formProgram = $editingProgram ?? $this->create();
            $isEditing = $editingProgram !== null;
        }

        return [
            'programs' => Program::all(),
            'formProgram' => $formProgram,
            'isEditing' => $isEditing,
            'flash' => getFlash(),
            'csrfToken' => $_SESSION['program_csrf_token'],
            'validationErrors' => $validationErrors,
        ];
    }

    public function create(): Program {
        return new Program();
    }

    public function store(array $data): void {
        Program::create($this->validatedData($data));
        setFlash('success', 'Program berhasil ditambahkan.');
    }

    public function edit(int $id): ?Program {
        return Program::find($id);
    }

    public function update(int $id, array $data): bool {
        $data = $this->validatedData($data);
        $program = $this->edit($id);
        if (!$program) {
            setFlash('danger', 'Program tidak ditemukan.');
            return false;
        }

        $program->nama = (string)($data['nama'] ?? '');
        $program->deskripsi = (string)($data['deskripsi'] ?? '');
        $program->save();
        setFlash('success', 'Program berhasil diperbarui.');
        return true;
    }

    public function destroy(int $id): bool {
        $program = $this->edit($id);
        if (!$program || !$program->delete()) {
            setFlash('danger', 'Program tidak ditemukan atau sudah dihapus.');
            return false;
        }

        setFlash('success', 'Program berhasil dihapus.');
        return true;
    }

    public function handleApi(): void {
        header('Content-Type: application/json; charset=utf-8');

        if (!isAdminLoggedIn()) {
            jsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        $this->ensureCsrfToken();
        header('X-CSRF-Token: ' . $_SESSION['program_csrf_token']);

        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $id = null;
        if (isset($_GET['id'])) {
            $validatedId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($validatedId === false || $validatedId < 1) {
                jsonResponse(['message' => 'Program tidak ditemukan.'], 404);
            }
            $id = (int)$validatedId;
        }

        $allowedMethods = $id === null ? ['GET', 'POST'] : ['GET', 'PUT', 'PATCH', 'DELETE'];
        if (!in_array($method, $allowedMethods, true)) {
            header('Allow: ' . implode(', ', $allowedMethods));
            jsonResponse(['message' => 'Method not allowed.'], 405);
        }

        $payload = [];
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $payload = $this->readJsonPayload();
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $this->verifyApiCsrfToken($payload);
        }

        try {
            if ($method === 'GET' && $id === null) {
                $programs = array_map(fn(Program $program): array => $this->serializeProgram($program), Program::all());
                jsonResponse(['data' => $programs]);
            }

            if ($method === 'GET' && $id !== null) {
                $program = Program::find($id);
                if (!$program) {
                    jsonResponse(['message' => 'Program tidak ditemukan.'], 404);
                }
                jsonResponse(['data' => $this->serializeProgram($program)]);
            }

            if ($method === 'POST') {
                $program = Program::create($this->validatedData($payload));
                jsonResponse([
                    'message' => 'Program berhasil dibuat.',
                    'data' => $this->serializeProgram($program),
                ], 201);
            }

            if ($method === 'PUT' || $method === 'PATCH') {
                $program = Program::find($id);
                if (!$program) {
                    jsonResponse(['message' => 'Program tidak ditemukan.'], 404);
                }

                if ($method === 'PATCH') {
                    $payload = array_merge([
                        'nama' => $program->nama,
                        'deskripsi' => $program->deskripsi ?? '',
                    ], $payload);
                }

                $data = $this->validatedData($payload);
                $program->nama = $data['nama'];
                $program->deskripsi = $data['deskripsi'];
                $program->save();
                jsonResponse([
                    'message' => 'Program berhasil diperbarui.',
                    'data' => $this->serializeProgram($program),
                ]);
            }

            if ($method === 'DELETE' && $id !== null) {
                $program = Program::find($id);
                if (!$program || !$program->delete()) {
                    jsonResponse(['message' => 'Program tidak ditemukan.'], 404);
                }
                jsonResponse(['message' => 'Program berhasil dihapus.']);
            }
        } catch (ProgramValidationException $exception) {
            jsonResponse([
                'message' => 'Data yang dikirim tidak valid.',
                'errors' => $exception->errors,
            ], 422);
        } catch (Throwable $exception) {
            error_log('Program API error: ' . $exception->getMessage());
            jsonResponse(['message' => 'Terjadi kesalahan pada server.'], 500);
        }

        jsonResponse(['message' => 'Request tidak dapat diproses.'], 400);
    }

    private function requestData(): array {
        return [
            'nama' => trim((string)($_POST['nama'] ?? '')),
            'deskripsi' => trim((string)($_POST['deskripsi'] ?? '')),
        ];
    }

    private function validatedData(array $data): array {
        $nama = trim((string)($data['nama'] ?? ''));
        $deskripsi = trim((string)($data['deskripsi'] ?? ''));
        $errors = [];

        if ($nama === '') {
            $errors['nama'] = 'Nama program wajib diisi.';
        } elseif (mb_strlen($nama, 'UTF-8') > 100) {
            $errors['nama'] = 'Nama program maksimal 100 karakter.';
        }

        if ($deskripsi === '') {
            $errors['deskripsi'] = 'Deskripsi program wajib diisi.';
        }

        if ($errors) {
            throw new ProgramValidationException($errors);
        }

        return ['nama' => $nama, 'deskripsi' => $deskripsi];
    }

    private function readJsonPayload(): array {
        $contentType = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
        if ($contentType !== 'application/json' && !str_ends_with($contentType, '+json')) {
            jsonResponse(['message' => 'Content-Type harus application/json.'], 415);
        }

        $rawBody = file_get_contents('php://input');
        if (!is_string($rawBody) || trim($rawBody) === '') {
            jsonResponse(['message' => 'Body request harus berupa JSON object yang valid.'], 400);
        }

        $decodedObject = json_decode($rawBody);
        if (!is_object($decodedObject)) {
            jsonResponse(['message' => 'Body request harus berupa JSON object yang valid.'], 400);
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            jsonResponse(['message' => 'Body request harus berupa JSON object yang valid.'], 400);
        }

        return $payload;
    }

    private function verifyApiCsrfToken(array $payload): void {
        $headerToken = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $submittedToken = $headerToken !== '' ? $headerToken : (string)($payload['_csrf'] ?? $payload['csrf_token'] ?? '');
        if (!hash_equals($_SESSION['program_csrf_token'], $submittedToken)) {
            jsonResponse(['message' => 'CSRF token tidak valid.'], 403);
        }
    }

    private function serializeProgram(Program $program): array {
        return [
            'id' => $program->id,
            'nama' => $program->nama,
            'deskripsi' => $program->deskripsi,
        ];
    }

    private function ensureCsrfToken(): void {
        if (empty($_SESSION['program_csrf_token'])) {
            $_SESSION['program_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function hasValidCsrfToken(): bool {
        $submittedToken = (string)($_POST['_csrf'] ?? '');
        return hash_equals($_SESSION['program_csrf_token'], $submittedToken);
    }
}

class ProgramValidationException extends InvalidArgumentException {
    public array $errors;

    public function __construct(array $errors) {
        $this->errors = $errors;
        parent::__construct('Periksa kembali data program yang dimasukkan.');
    }
}