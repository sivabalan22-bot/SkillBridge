<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        set_flash('Please log in to continue.', 'info');
        redirect('login.php');
    }
}

function current_user(): ?array
{
    global $pdo;
    static $user = false;

    if (empty($_SESSION['user_id'])) {
        return null;
    }
    if ($user !== false) {
        return $user;
    }

    $statement = $pdo->prepare(
        'SELECT id, name, email, course, year_of_study, bio, created_at
         FROM students WHERE id = ?'
    );
    $statement->execute([(int) $_SESSION['user_id']]);
    $user = $statement->fetch() ?: null;

    if ($user === null) {
        unset($_SESSION['user_id']);
    }
    return $user;
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('Your session token expired. Please go back, refresh the page, and try again.');
    }
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}
