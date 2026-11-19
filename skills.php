<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$student = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post_string('action');
    if ($action === 'add') {
        $name = post_string('name');
        $type = post_string('skill_type');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
            $errors[] = 'A skill name must be between 2 and 80 characters.';
        }
        if (!in_array($type, ['teach', 'learn'], true)) {
            $errors[] = 'Choose whether you can teach the skill or want to learn it.';
        }
        if (!$errors) {
            $statement = $pdo->prepare('SELECT id FROM skills WHERE student_id = ? AND skill_type = ? AND LOWER(name) = LOWER(?)');
            $statement->execute([$student['id'], $type, $name]);
            if ($statement->fetch()) {
                $errors[] = 'That skill is already on your list.';
            } else {
                $statement = $pdo->prepare('INSERT INTO skills (student_id, skill_type, name) VALUES (?, ?, ?)');
                $statement->execute([$student['id'], $type, $name]);
                set_flash('Skill added to your profile.');
                redirect('skills.php');
            }
        }
    } elseif ($action === 'delete') {
        $skillId = filter_var($_POST['skill_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$skillId) {
            $errors[] = 'Select a valid skill to remove.';
        } else {
            $statement = $pdo->prepare('DELETE FROM skills WHERE id = ? AND student_id = ?');
            $statement->execute([$skillId, $student['id']]);
            set_flash($statement->rowCount() ? 'Skill removed.' : 'That skill could not be found.', $statement->rowCount() ? 'success' : 'error');
            redirect('skills.php');
        }
    } else {
        $errors[] = 'That action is not available.';
    }
}

$statement = $pdo->prepare('SELECT id, skill_type, name FROM skills WHERE student_id = ? ORDER BY skill_type, name');
$statement->execute([$student['id']]);
$skills = $statement->fetchAll();
$teachingSkills = array_values(array_filter($skills, static fn(array $skill): bool => $skill['skill_type'] === 'teach'));
$learningSkills = array_values(array_filter($skills, static fn(array $skill): bool => $skill['skill_type'] === 'learn'));
$pageTitle = 'My skills';
$activePage = 'skills';
include __DIR__ . '/includes/header.php';
?>
<section class="page-section">
    <div class="container narrow-container">
        <div class="page-heading"><div><span class="eyebrow">YOUR SKILL MIX</span><h1>Skills make the bridge.</h1><p>Add what you can teach and what you’re excited to learn.</p></div></div>
        <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
        <section class="panel form-panel">
            <div class="panel-heading"><div><span class="eyebrow">ADD A SKILL</span><h2>What’s on your mind?</h2></div><span class="panel-spark">✳</span></div>
            <form method="post" class="skill-add-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add">
                <label>Skill name<input type="text" name="name" maxlength="80" placeholder="e.g. Guitar, Excel, Public speaking" required></label>
                <label>I want to…<select name="skill_type" required><option value="teach">Teach this skill</option><option value="learn">Learn this skill</option></select></label>
                <button class="button" type="submit">Add skill <span aria-hidden="true">+</span></button>
            </form>
        </section>
        <div class="skills-manage-grid">
            <?php foreach ([['teach', 'I CAN TEACH', $teachingSkills, 'dot-purple'], ['learn', 'I WANT TO LEARN', $learningSkills, 'dot-orange']] as [$type, $label, $items, $dotClass]): ?>
                <section class="panel manage-list-panel">
                    <div class="panel-heading"><h2><span class="column-dot <?= $dotClass ?>"></span> <?= $label ?></h2><span class="count-pill"><?= count($items) ?></span></div>
                    <?php if ($items): ?><ul class="manage-skill-list"><?php foreach ($items as $skill): ?><li><span><?= e($skill['name']) ?></span><form method="post" onsubmit="return confirm('Remove this skill from your profile?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="skill_id" value="<?= (int) $skill['id'] ?>"><button class="icon-button" type="submit" aria-label="Remove <?= e($skill['name']) ?>">×</button></form></li><?php endforeach; ?></ul><?php else: ?><p class="empty-note">Nothing here yet. Add a skill above to get started.</p><?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
        <a class="back-link page-back-link" href="dashboard.php">← Back to dashboard</a>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
