<?php
require_once __DIR__ . '/includes/functions.php';
if (current_user()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(post_string('email'));
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!is_string($password) || $password === '') {
        $errors[] = 'Enter your password.';
    }
    if (!$errors) {
        $statement = $pdo->prepare('SELECT id, password_hash FROM students WHERE email = ?');
        $statement->execute([$email]);
        $student = $statement->fetch();
        if ($student && password_verify($password, $student['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $student['id'];
            set_flash('Welcome back! You’re signed in.');
            redirect('dashboard.php');
        }
        $errors[] = 'That email and password combination was not found.';
    }
}
$pageTitle = 'Log in';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-section">
    <div class="auth-card">
        <a class="back-link" href="index.php">← Back to home</a>
        <span class="eyebrow">Good to have you back</span>
        <h1>Log in to SkillBridge</h1>
        <p class="auth-intro">Pick up where your next skill exchange begins.</p>
        <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
        <form method="post" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Email address<input type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button button-full" type="submit">Log in <span aria-hidden="true">↗</span></button>
        </form>
        <p class="auth-switch">New to SkillBridge? <a href="register.php">Create an account</a></p>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
