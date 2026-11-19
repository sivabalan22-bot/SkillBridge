<?php
$pageTitle = $pageTitle ?? 'SkillBridge';
$activePage = $activePage ?? '';
$loggedInUser = current_user();
$flashMessage = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10172f">
    <title><?= e($pageTitle) ?> · SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="index.php" aria-label="SkillBridge home">
            <span class="brand-mark">S</span><span>skill<span class="brand-light">bridge</span></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
            <span></span><span></span><span class="sr-only">Toggle navigation</span>
        </button>
        <nav class="primary-nav" id="primary-nav">
            <?php if ($loggedInUser): ?>
                <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
                <a class="<?= $activePage === 'discover' ? 'active' : '' ?>" href="discover.php">Discover</a>
                <a class="<?= $activePage === 'skills' ? 'active' : '' ?>" href="skills.php">My skills</a>
                <a class="<?= $activePage === 'requests' ? 'active' : '' ?>" href="requests.php">Requests</a>
                <a class="nav-profile" href="profile.php"><?= e($loggedInUser['name']) ?></a>
                <form class="nav-logout-form" method="post" action="logout.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button class="nav-logout" type="submit">Log out</button>
                </form>
            <?php else: ?>
                <a href="index.php#how-it-works">How it works</a>
                <a class="nav-signin" href="login.php">Log in</a>
                <a class="button button-small" href="register.php">Join SkillBridge <span aria-hidden="true">↗</span></a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
    <?php if ($flashMessage): ?>
        <div class="container flash-wrap">
            <div class="flash flash-<?= e($flashMessage['type']) ?>" role="status"><?= e($flashMessage['message']) ?></div>
        </div>
    <?php endif; ?>
