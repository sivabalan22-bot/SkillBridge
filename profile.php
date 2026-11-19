<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$student = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('name');
    $course = post_string('course');
    $year = post_string('year_of_study');
    $bio = post_string('bio');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors[] = 'Your name must be between 2 and 80 characters.';
    }
    if (mb_strlen($course) > 100) {
        $errors[] = 'Course or major must be 100 characters or fewer.';
    }
    if (!in_array($year, ['', '1', '2', '3', '4', '5', 'Graduate'], true)) {
        $errors[] = 'Select a valid year of study.';
    }
    if (mb_strlen($bio) > 500) {
        $errors[] = 'Your introduction must be 500 characters or fewer.';
    }
    if (!$errors) {
        $statement = $pdo->prepare('UPDATE students SET name = ?, course = ?, year_of_study = ?, bio = ? WHERE id = ?');
        $statement->execute([$name, $course === '' ? null : $course, $year === '' ? null : $year, $bio === '' ? null : $bio, $student['id']]);
        set_flash('Your profile has been updated.');
        redirect('profile.php');
    }
    $student['name'] = $name;
    $student['course'] = $course;
    $student['year_of_study'] = $year;
    $student['bio'] = $bio;
}

$pageTitle = 'Your profile';
$activePage = 'profile';
include __DIR__ . '/includes/header.php';
?>
<section class="page-section">
    <div class="container narrow-container">
        <div class="page-heading"><div><span class="eyebrow">YOUR PROFILE</span><h1>Let people get to know you.</h1><p>A little context makes it easier to find the right learning partner.</p></div></div>
        <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
        <section class="panel profile-form-panel">
            <div class="profile-intro"><div class="profile-avatar"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></div><div><span class="eyebrow">STUDENT PROFILE</span><h2><?= e($student['email']) ?></h2><p>Your email is private and is not shown on your public profile.</p></div></div>
            <form method="post" class="form-stack">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label>Full name<input type="text" name="name" value="<?= e($student['name']) ?>" maxlength="80" autocomplete="name" required></label>
                <div class="form-row">
                    <label>Course or major <span class="label-hint">Optional</span><input type="text" name="course" value="<?= e($student['course']) ?>" maxlength="100" placeholder="e.g. Computer science"></label>
                    <label>Year of study <span class="label-hint">Optional</span><select name="year_of_study"><option value="">Choose a year</option><?php foreach (['1' => 'Year 1', '2' => 'Year 2', '3' => 'Year 3', '4' => 'Year 4', '5' => 'Year 5+', 'Graduate' => 'Graduate'] as $value => $label): ?><option value="<?= e($value) ?>" <?= (string) $student['year_of_study'] === (string) $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
                </div>
                <label>A little about you <span class="label-hint">Optional · 500 characters max</span><textarea name="bio" rows="5" maxlength="500" placeholder="What are you studying? What are you curious about?"><?= e($student['bio']) ?></textarea></label>
                <div class="form-actions"><a class="text-link" href="dashboard.php">Cancel</a><button class="button" type="submit">Save changes <span aria-hidden="true">↗</span></button></div>
            </form>
        </section>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
