<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$student = current_user();
$errors = [];
$searchValue = $_GET['q'] ?? '';
$search = is_string($searchValue) ? trim($searchValue) : '';
if (mb_strlen($search) > 80) {
    $search = mb_substr($search, 0, 80);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $receiverId = filter_var($_POST['receiver_id'] ?? null, FILTER_VALIDATE_INT);
    $offeredSkillId = filter_var($_POST['offered_skill_id'] ?? null, FILTER_VALIDATE_INT);
    $wantedSkillId = filter_var($_POST['wanted_skill_id'] ?? null, FILTER_VALIDATE_INT);
    $message = post_string('message');
    if (!$receiverId || !$offeredSkillId || !$wantedSkillId || $receiverId === (int) $student['id']) {
        $errors[] = 'Choose a valid student and skills for your exchange.';
    }
    if (mb_strlen($message) > 300) {
        $errors[] = 'Your message must be 300 characters or fewer.';
    }
    if (!$errors) {
        $statement = $pdo->prepare("SELECT id FROM skills WHERE id = ? AND student_id = ? AND skill_type = 'teach'");
        $statement->execute([$offeredSkillId, $student['id']]);
        $offeredSkill = $statement->fetch();
        $statement = $pdo->prepare("SELECT id FROM skills WHERE id = ? AND student_id = ? AND skill_type = 'teach'");
        $statement->execute([$wantedSkillId, $receiverId]);
        $wantedSkill = $statement->fetch();
        if (!$offeredSkill || !$wantedSkill) {
            $errors[] = 'Those skills are no longer available for exchange.';
        } else {
            $statement = $pdo->prepare(
                "SELECT id FROM exchange_requests
                 WHERE sender_id = ? AND receiver_id = ? AND offered_skill_id = ? AND wanted_skill_id = ? AND status = 'pending'"
            );
            $statement->execute([$student['id'], $receiverId, $offeredSkillId, $wantedSkillId]);
            if ($statement->fetch()) {
                $errors[] = 'You already have a pending request for this skill exchange.';
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO exchange_requests (sender_id, receiver_id, offered_skill_id, wanted_skill_id, message)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $statement->execute([$student['id'], $receiverId, $offeredSkillId, $wantedSkillId, $message === '' ? null : $message]);
                set_flash('Your skill exchange request has been sent.');
                redirect('requests.php?tab=sent');
            }
        }
    }
}

$statement = $pdo->prepare("SELECT id, name FROM skills WHERE student_id = ? AND skill_type = 'teach' ORDER BY name");
$statement->execute([$student['id']]);
$myTeachingSkills = $statement->fetchAll();
$statement = $pdo->prepare(
    "SELECT s.id, s.name, s.course, s.year_of_study, s.bio, skill_list.teaching_skills
     FROM students s
     LEFT JOIN (
        SELECT student_id, GROUP_CONCAT(CONCAT(id, ':', name) ORDER BY name SEPARATOR '||') AS teaching_skills
        FROM skills WHERE skill_type = 'teach' GROUP BY student_id
     ) skill_list ON skill_list.student_id = s.id
     WHERE s.id <> ? AND (
        s.name LIKE ? OR COALESCE(s.course, '') LIKE ?
        OR EXISTS (SELECT 1 FROM skills matched WHERE matched.student_id = s.id AND matched.skill_type = 'teach' AND matched.name LIKE ?)
     )
     ORDER BY s.created_at DESC
     LIMIT 60"
);
$pattern = '%' . $search . '%';
$statement->execute([$student['id'], $pattern, $pattern, $pattern]);
$students = $statement->fetchAll();
$pageTitle = 'Discover students';
$activePage = 'discover';
include __DIR__ . '/includes/header.php';
?>
<section class="page-section discover-section">
    <div class="container">
        <div class="page-heading discover-heading"><div><span class="eyebrow">YOUR NEXT LEARNING PARTNER</span><h1>Good skills are meant to be shared.</h1><p>Meet students with something to teach and find the skills you’re curious about.</p></div><div class="discover-stamp">✳<span>COMMUNITY<br>OVER<br>COMPETITION</span></div></div>
        <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
        <form class="search-bar" method="get" action="discover.php"><span class="search-icon">⌕</span><input type="search" name="q" value="<?= e($search) ?>" maxlength="80" placeholder="Search by name, course or skill"><button class="button button-small" type="submit">Search</button><?php if ($search !== ''): ?><a class="clear-search" href="discover.php">Clear</a><?php endif; ?></form>
        <div class="results-heading"><h2><?= $search !== '' ? 'Search results' : 'Students to learn from' ?></h2><span><?= count($students) ?> student<?= count($students) === 1 ? '' : 's' ?> found</span></div>
        <?php if (!$myTeachingSkills): ?>
            <div class="callout-note"><span>✦</span><p>Add at least one teaching skill before sending an exchange request. <a href="skills.php">Add your skills →</a></p></div>
        <?php endif; ?>
        <?php if ($students): ?>
            <div class="student-grid">
                <?php foreach ($students as $person): $personSkills = $person['teaching_skills'] ? array_map(static fn(string $item): array => explode(':', $item, 2), explode('||', $person['teaching_skills'])) : []; ?>
                    <article class="student-card">
                        <div class="student-card-top"><div class="person-avatar <?= ['avatar-indigo', 'avatar-pink', 'avatar-teal', 'avatar-amber'][(int) $person['id'] % 4] ?>"><?= e(strtoupper(substr($person['name'], 0, 1))) ?></div><span class="student-status"><span class="live-dot"></span> OPEN TO EXCHANGE</span></div>
                        <h3><?= e($person['name']) ?></h3>
                        <p class="student-meta"><?= e($person['course'] ?: 'Student') ?><?= $person['year_of_study'] ? ' · Year ' . e((string) $person['year_of_study']) : '' ?></p>
                        <?php if ($person['bio']): ?><p class="student-bio"><?= e($person['bio']) ?></p><?php endif; ?>
                        <div class="student-skills"><span class="column-label"><span class="column-dot dot-purple"></span> CAN TEACH</span><div class="tag-list"><?php if ($personSkills): foreach ($personSkills as [$skillId, $skillName]): ?><span class="skill-tag"><?= e($skillName) ?></span><?php endforeach; else: ?><span class="empty-note">Still building their skill list.</span><?php endif; ?></div></div>
                        <?php if ($personSkills && $myTeachingSkills): ?>
                            <details class="exchange-details">
                                <summary>Propose an exchange <span aria-hidden="true">↗</span></summary>
                                <form method="post" class="exchange-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="receiver_id" value="<?= (int) $person['id'] ?>">
                                    <label>I can teach<select name="offered_skill_id" required><option value="">Choose one of your skills</option><?php foreach ($myTeachingSkills as $skill): ?><option value="<?= (int) $skill['id'] ?>"><?= e($skill['name']) ?></option><?php endforeach; ?></select></label>
                                    <label>I’d love to learn<select name="wanted_skill_id" required><option value="">Choose one of their skills</option><?php foreach ($personSkills as [$skillId, $skillName]): ?><option value="<?= (int) $skillId ?>"><?= e($skillName) ?></option><?php endforeach; ?></select></label>
                                    <label>Say hello <span class="label-hint">Optional</span><textarea name="message" rows="2" maxlength="300" placeholder="Make it personal…"></textarea></label>
                                    <button class="button button-full" type="submit">Send request <span aria-hidden="true">↗</span></button>
                                </form>
                            </details>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><span class="empty-state-icon">⌕</span><h2>No students found</h2><p><?= $search !== '' ? 'Try another name, course, or skill.' : 'Be the first to share your skills with the community.' ?></p><?php if ($search !== ''): ?><a class="button" href="discover.php">See all students</a><?php endif; ?></div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
