<?php
require_once __DIR__ . '/includes/functions.php';
if (current_user()) {
    redirect('dashboard.php');
}

$errors = [];
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('name');
    $email = strtolower(post_string('email'));
    $password = $_POST['password'] ?? '';
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors[] = 'Your name must be between 2 and 80 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errors[] = 'Enter a valid email address (up to 190 characters).';
    }
    if (!is_string($password) || strlen($password) < 8 || strlen($password) > 72) {
        $errors[] = 'Choose a password between 8 and 72 characters.';
    }
    if (!$errors) {
        $statement = $pdo->prepare('SELECT id FROM students WHERE email = ?');
        $statement->execute([$email]);
        if ($statement->fetch()) {
            $errors[] = 'An account with that email already exists. Try logging in.';
        } else {
            $statement = $pdo->prepare('INSERT INTO students (name, email, password_hash) VALUES (?, ?, ?)');
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            set_flash('Your account is ready. Add a few skills to get started!');
            redirect('dashboard.php');
        }
    }
}
$pageTitle = 'Create your account';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-section">
    <div class="auth-card">
        <a class="back-link" href="index.php">← Back to home</a>
        <span class="eyebrow">A community of shared potential</span>
        <h1>Your next skill starts here.</h1>
        <p class="auth-intro">Create your free student account and meet your next learning partner.</p>
        <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
        <form method="post" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Your name<input type="text" name="name" value="<?= e($name) ?>" maxlength="80" autocomplete="name" required></label>
            <label>Student email<input type="email" name="email" value="<?= e($email) ?>" maxlength="190" autocomplete="email" required></label>
            <label>Password <span class="label-hint">At least 8 characters</span><input type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required></label>
            <button class="button button-full" type="submit">Create account <span aria-hidden="true">↗</span></button>
        </form>
        <p class="auth-switch">Already part of the community? <a href="login.php">Log in</a></p>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
