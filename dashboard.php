<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$student = current_user();

$statement = $pdo->prepare(
    "SELECT skill_type, name FROM skills WHERE student_id = ? ORDER BY skill_type, name"
);
$statement->execute([$student['id']]);
$skillRows = $statement->fetchAll();
$teachingSkills = array_values(array_filter($skillRows, static fn(array $skill): bool => $skill['skill_type'] === 'teach'));
$learningSkills = array_values(array_filter($skillRows, static fn(array $skill): bool => $skill['skill_type'] === 'learn'));

$statement = $pdo->prepare(
    "SELECT
        SUM(CASE WHEN receiver_id = ? AND status = 'pending' THEN 1 ELSE 0 END) AS incoming_count,
        SUM(CASE WHEN sender_id = ? AND status = 'pending' THEN 1 ELSE 0 END) AS outgoing_count,
        SUM(CASE WHEN (sender_id = ? OR receiver_id = ?) AND status = 'accepted' THEN 1 ELSE 0 END) AS accepted_count
     FROM exchange_requests WHERE sender_id = ? OR receiver_id = ?"
);
$statement->execute([$student['id'], $student['id'], $student['id'], $student['id'], $student['id'], $student['id']]);
$counts = $statement->fetch();

$statement = $pdo->prepare(
    "SELECT r.id, r.created_at, s.name AS sender_name, offered.name AS offered_name, wanted.name AS wanted_name
     FROM exchange_requests r
     JOIN students s ON s.id = r.sender_id
     JOIN skills offered ON offered.id = r.offered_skill_id
     JOIN skills wanted ON wanted.id = r.wanted_skill_id
     WHERE r.receiver_id = ? AND r.status = 'pending'
     ORDER BY r.created_at DESC LIMIT 3"
);
$statement->execute([$student['id']]);
$recentRequests = $statement->fetchAll();

$pageTitle = 'Your dashboard';
$activePage = 'dashboard';
include __DIR__ . '/includes/header.php';
?>
<section class="page-section dashboard-section">
    <div class="container">
        <div class="page-heading dashboard-heading">
            <div><span class="eyebrow">YOUR SKILLBRIDGE</span><h1>Welcome back, <?= e(explode(' ', $student['name'])[0]) ?> <span class="wave">✦</span></h1><p>Your next great exchange could be one connection away.</p></div>
            <a class="button" href="discover.php">Find a learning partner <span aria-hidden="true">↗</span></a>
        </div>
        <div class="stats-grid">
            <article class="stat-card"><span class="stat-icon stat-violet">✳</span><div><span class="stat-label">TEACHING</span><strong><?= count($teachingSkills) ?></strong><small>skills you can share</small></div></article>
            <article class="stat-card"><span class="stat-icon stat-orange">⌕</span><div><span class="stat-label">LEARNING</span><strong><?= count($learningSkills) ?></strong><small>skills you want to learn</small></div></article>
            <article class="stat-card"><span class="stat-icon stat-green">↗</span><div><span class="stat-label">NEW REQUESTS</span><strong><?= (int) ($counts['incoming_count'] ?? 0) ?></strong><small>waiting for your reply</small></div></article>
            <article class="stat-card"><span class="stat-icon stat-blue">↔</span><div><span class="stat-label">CONNECTIONS</span><strong><?= (int) ($counts['accepted_count'] ?? 0) ?></strong><small>accepted exchanges</small></div></article>
        </div>
        <div class="dashboard-grid">
            <section class="panel skills-panel">
                <div class="panel-heading"><div><span class="eyebrow">YOUR SKILL MIX</span><h2>What’s on your list?</h2></div><a class="text-link" href="skills.php">Manage skills <span aria-hidden="true">↗</span></a></div>
                <div class="skill-columns">
                    <div class="skill-column"><span class="column-label"><span class="column-dot dot-purple"></span> I CAN TEACH</span>
                        <?php if ($teachingSkills): ?><div class="tag-list"><?php foreach ($teachingSkills as $skill): ?><span class="skill-tag"><?= e($skill['name']) ?></span><?php endforeach; ?></div><?php else: ?><p class="empty-note">Add a skill you’re happy to share.</p><?php endif; ?>
                    </div>
                    <div class="skill-column"><span class="column-label"><span class="column-dot dot-orange"></span> I WANT TO LEARN</span>
                        <?php if ($learningSkills): ?><div class="tag-list"><?php foreach ($learningSkills as $skill): ?><span class="skill-tag skill-tag-warm"><?= e($skill['name']) ?></span><?php endforeach; ?></div><?php else: ?><p class="empty-note">Add something you’re curious about.</p><?php endif; ?>
                    </div>
                </div>
                <a class="add-skills-link" href="skills.php"><span>+</span> Add or update your skills</a>
            </section>
            <section class="panel request-panel">
                <div class="panel-heading"><div><span class="eyebrow">IN YOUR INBOX</span><h2>Incoming requests</h2></div><a class="text-link" href="requests.php">View all <span aria-hidden="true">↗</span></a></div>
                <?php if ($recentRequests): ?>
                    <div class="request-list"><?php foreach ($recentRequests as $request): ?><div class="mini-request"><div class="request-avatar"><?= e(strtoupper(substr($request['sender_name'], 0, 1))) ?></div><div class="mini-request-copy"><strong><?= e($request['sender_name']) ?></strong><span>offers <?= e($request['offered_name']) ?> for <?= e($request['wanted_name']) ?></span></div><span class="pending-pill">New</span></div><?php endforeach; ?></div>
                <?php else: ?><div class="empty-state compact-empty"><span class="empty-state-icon">✉</span><p>No new requests just yet.</p><a href="discover.php">Explore students →</a></div><?php endif; ?>
            </section>
        </div>
        <section class="dashboard-banner"><div class="banner-icon">✦</div><div><strong>Good at something? Pass it on.</strong><p>A skill you take for granted might be exactly what someone else wants to learn.</p></div><a href="profile.php">Complete your profile <span aria-hidden="true">↗</span></a></section>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
