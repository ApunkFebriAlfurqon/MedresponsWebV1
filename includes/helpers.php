<?php
// ============================================================
// RapidAid - Helper Functions & Session Management
// ============================================================

require_once __DIR__ . '/../config/database.php';

// ---- Session ----
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params(['lifetime' => SESSION_LIFETIME, 'httponly' => true]);
        session_start();
    }
}

function isUserLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn(): bool {
    startSession();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireUserLogin(): void {
    if (!isUserLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/admin-login.php');
        exit();
    }
}

function currentUser(): ?array {
    if (!isUserLoggedIn()) return null;
    return db()->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function currentAdmin(): ?array {
    if (!isAdminLoggedIn()) return null;
    return db()->fetchOne("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
}

// ---- Sanitize ----
function clean(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail(string $email): string {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

// ---- Flash Messages ----
function setFlash(string $type, string $message): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ---- Formatting ----
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate(string $date): string {
    if (!$date) return '-';
    return date('d M Y', strtotime($date));
}

function formatDateTime(string $datetime): string {
    if (!$datetime) return '-';
    return date('d M Y, H:i', strtotime($datetime));
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return round($diff / 60) . ' minutes ago';
    if ($diff < 86400) return round($diff / 3600) . ' hours ago';
    return round($diff / 86400) . ' days ago';
}

// ---- Generators ----
function generateRequestCode(): string {
    $year = date('Y');
    $count = db()->count('emergency_requests', "YEAR(created_at) = ?", [$year]);
    return 'REQ-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function generateInvoiceNumber(): string {
    $year = date('Y');
    $count = db()->count('payments', "YEAR(created_at) = ?", [$year]);
    return 'INV-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

// ---- Status Badges ----
function statusBadge(string $status): string {
    $map = [
        'active'      => ['label' => 'Active',      'class' => 'badge-success'],
        'expired'     => ['label' => 'Expired',     'class' => 'badge-danger'],
        'pending'     => ['label' => 'Pending',     'class' => 'badge-warning'],
        'cancelled'   => ['label' => 'Cancelled',   'class' => 'badge-secondary'],
        'paid'        => ['label' => 'Paid',         'class' => 'badge-success'],
        'failed'      => ['label' => 'Failed',       'class' => 'badge-danger'],
        'available'   => ['label' => 'Available',   'class' => 'badge-success'],
        'on_duty'     => ['label' => 'On Duty',     'class' => 'badge-warning'],
        'maintenance' => ['label' => 'Maintenance', 'class' => 'badge-secondary'],
        'on_the_way'  => ['label' => 'On The Way',  'class' => 'badge-info'],
        'arrived'     => ['label' => 'Arrived',     'class' => 'badge-primary'],
        'completed'   => ['label' => 'Completed',   'class' => 'badge-success'],
        'accepted'    => ['label' => 'Accepted',    'class' => 'badge-info'],
    ];
    $info = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-secondary'];
    return "<span class=\"badge {$info['class']}\">{$info['label']}</span>";
}

// ---- Notifications ----
function createNotification(int $userId = null, int $adminId = null, string $title, string $message, string $type = 'info', string $link = null): void {
    db()->insert('notifications', [
        'user_id'  => $userId,
        'admin_id' => $adminId,
        'title'    => $title,
        'message'  => $message,
        'type'     => $type,
        'link'     => $link,
    ]);
}

function getUserNotifications(int $userId, bool $unreadOnly = false): array {
    $where = $unreadOnly ? "user_id = ? AND is_read = 0" : "user_id = ?";
    return db()->fetchAll("SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT 20", [$userId]);
}

function getUnreadCount(int $userId): int {
    return db()->count('notifications', 'user_id = ? AND is_read = 0', [$userId]);
}

// ---- JSON Response ----
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// ---- File Upload ----
function uploadFile(array $file, string $folder = 'general'): ?string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null; // 5MB max

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        return $folder . '/' . $filename;
    }
    return null;
}

// ---- Active Subscription ----
function getUserActiveSubscription(int $userId): ?array {
    return db()->fetchOne(
        "SELECT s.*, sp.name as plan_name, sp.features, sp.price, sp.color, sp.max_members 
         FROM subscriptions s 
         JOIN subscription_plans sp ON s.plan_id = sp.id 
         WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE()
         ORDER BY s.created_at DESC LIMIT 1",
        [$userId]
    );
}
