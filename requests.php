<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$student = current_user();
$errors = [];
$tab = ($_GET['tab'] ?? '') === 'sent' ? 'sent' : 'received';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $action = post_string('action');
    if (!$requestId || !in_array($action, ['accept', 'reject'], true)) {
        $errors[] = 'Choose a valid request action.';
    } else {
        $status = $action === 'accept' ? 'accepted' : 'rejected';
        $statement = $pdo->prepare(
            "UPDATE exchange_requests SET status = ?, updated_at = CURRENT_TIMESTAMP
             WHERE id = ? AND receiver_id = ? AND status = 'pending'"
        );
        $statement->execute([$status, $requestId, $student['id']]);
        if ($statement->rowCount()) {
            set_flash($action === 'accept' ? 'Exchange accepted! You can now connect with your learning partner.' : 'Request declined.');
            redirect('requests.php');
        }
        $errors[] = 'This request is no longer available to update.';
    }
}

$statement = $pdo->prepare(
    "SELECT r.id, r.status, r.message, r.created_at, other.id AS other_id, other.name AS other_name,
            other.course, other.year_of_study, offered.name AS offered_name, wanted.name AS wanted_name
     FROM exchange_requests r
     JOIN students other ON other.id = r.sender_id
     JOIN skills offered ON offered.id = r.offered_skill_id
     JOIN skills wanted ON wanted.id = r.wanted_skill_id
     WHERE r.receiver_id = ?
     ORDER BY CASE r.status WHEN 'pending' THEN 0 WHEN 'accepted' THEN 1 ELSE 2 END, r.created_at DESC"
);
$statement->execute([$student['id']]);
$receivedRequests = $statement->fetchAll();
$statement = $pdo->prepare(
    'SELECT r.id, r.status, r.message, r.created_at, other.id AS other_id, other.name AS other_name,
            other.course, other.year_of_study, offered.name AS offered_name, wanted.name AS wanted_name
     FROM exchange_requests r
     JOIN students other ON other.id = r.receiver_id
     JOIN skills offered ON offered.id = r.offered_skill_id
     JOIN skills wanted ON wanted.id = r.wanted_skill_id
     WHERE r.sender_id = ?
     ORDER BY r.created_at DESC'
);
$statement->execute([$student['id']]);
$sentRequests = $statement->fetchAll();
$pageTitle = 'Skill exchange requests';
$activePage = 'requests';
include __DIR__ . '/includes/header.php';
?>
<section class="page-section">
    <div class="container narrow-container">
        <div class="page-heading"><div><span class="eyebrow">MAKE THE CONNECTION</span><h1>Your exchange requests.</h1><p>Every great skill share starts with a hello.</p></div></div>
        <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
        <div class="tabs" role="tablist"><a class="<?= $tab === 'received' ? 'selected' : '' ?>" href="requests.php?tab=received">Received <span><?= count($receivedRequests) ?></span></a><a class="<?= $tab === 'sent' ? 'selected' : '' ?>" href="requests.php?tab=sent">Sent <span><?= count($sentRequests) ?></span></a></div>
        <?php $visibleRequests = $tab === 'received' ? $receivedRequests : $sentRequests; ?>
        <?php if ($visibleRequests): ?>
            <div class="exchange-request-list">
                <?php foreach ($visibleRequests as $request): ?>
                    <article class="exchange-request-card">
                        <div class="exchange-request-head"><div class="request-avatar large-avatar"><?= e(strtoupper(substr($request['other_name'], 0, 1))) ?></div><div class="request-person"><h2><?= e($request['other_name']) ?></h2><p><?= e($request['course'] ?: 'Student') ?><?= $request['year_of_study'] ? ' · Year ' . e((string) $request['year_of_study']) : '' ?></p></div><span class="status-pill status-<?= e($request['status']) ?>"><?= e(ucfirst($request['status'])) ?></span></div>
                        <div class="request-exchange"><div><span><?= $tab === 'received' ? 'THEY CAN TEACH' : 'YOU OFFER' ?></span><strong><?= e($request['offered_name']) ?></strong></div><span class="exchange-symbol">↔</span><div><span><?= $tab === 'received' ? 'THEY WANT TO LEARN' : 'YOU WANT TO LEARN' ?></span><strong><?= e($request['wanted_name']) ?></strong></div></div>
                        <?php if ($request['message']): ?><blockquote>“<?= e($request['message']) ?>”</blockquote><?php endif; ?>
                        <div class="request-card-footer"><span><?= e(date('M j, Y', strtotime($request['created_at']))) ?></span>
                            <?php if ($tab === 'received' && $request['status'] === 'pending'): ?>
                                <div class="request-actions"><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><input type="hidden" name="action" value="reject"><button class="button button-outline button-small" type="submit">Decline</button></form><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><input type="hidden" name="action" value="accept"><button class="button button-small" type="submit">Accept <span aria-hidden="true">✓</span></button></form></div>
                            <?php elseif ($tab === 'sent' && $request['status'] === 'pending'): ?><span class="waiting-label">Waiting for a reply</span>
                            <?php elseif ($request['status'] === 'accepted'): ?><span class="accepted-label">You’re connected! Find them on campus to get started.</span><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><span class="empty-state-icon">✉</span><h2><?= $tab === 'received' ? 'No requests yet' : 'Nothing sent so far' ?></h2><p><?= $tab === 'received' ? 'When someone wants to learn from you, their request will show up here.' : 'Find a student whose skills inspire you and send a request.' ?></p><a class="button" href="discover.php">Discover students <span aria-hidden="true">↗</span></a></div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
