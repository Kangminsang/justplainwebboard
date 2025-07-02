<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'functions.php';

$board_slug = $_GET['board'] ?? null;
$post_id = $_GET['id'] ?? null;

$redirect_message = '';
if (!$post_id) {
    die('게시글 ID가 없습니다.');
}

// 게시글 조회
$stmt = $pdo->prepare('SELECT p.*, u.username, b.name AS board_name, b.slug AS board_slug
                                FROM posts p
                                JOIN users u ON p.user_id = u.id
                                JOIN public.boards b on b.id = p.board_id
                                WHERE p.id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    $redirect_message = '게시글이 존재하지 않거나 삭제되었습니다. 잠시 후 게시글 목록으로 이동합니다.';
} else if (empty($post['board_slug']) || $post['board_slug'] != $board_slug) {
    header('Location: /board/' . $post['board_slug'] . '/' . $post['id'], true, 301);
    exit();
}
if ($redirect_message) {
    ?>
    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title>게시글을 불러올 수 없음</title>
        <meta http-equiv="refresh" content="2;url=/board/<?= $board_slug ?>">
    </head>
    <body>
    <p><?= h($redirect_message) ?></p>
    </body>
    </html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?= $post['title'] ?></title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<h2><?= h($post['title']) ?></h2>
<p>작성자: <?= h($post['username']) ?> | 작성일: <?= $post['created_at']?> | 수정일: <?= $post['updated_at']?></p>
<p><?= nl2br(h($post['content'])) ?></p>

<!-- 첨부파일 목록 -->
<?php
$stmt = $pdo->prepare('SELECT * FROM attachments WHERE post_id = ?');
$stmt->execute([$post_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($files): ?>
    <h3>첨부파일</h3>
    <ul>
        <?php foreach ($files as $file): ?>
            <li><a href="/download.php?id=<?= $file['id'] ?>"><?= h($file['original_name']) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php include 'includes/geul_management.php'; ?>
<!-- 댓글 목록 -->
<h3>댓글</h3>
<?php
$stmt = $pdo->prepare('SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC');
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($comments as $comment): ?>
    <div style="border:1px solid #ccc; padding:5px; margin:5px 0;">
        <p><?= nl2br(h($comment['content'])) ?></p>
        <small>작성자: <?= h($comment['username']) ?> | <?= $comment['created_at'] ?></small>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
            <a href="/board/<?= $board_slug ?>/<?= $post_id?>/comment_edit?id=<?= $comment['id'] ?>">수정</a>
            <a href="/comment_delete.php?id=<?= $comment['id'] ?>&post_id=<?= $post_id ?>" onclick="return confirm('댓글을 삭제하시겠습니까?')">삭제</a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<!-- 댓글 작성 폼 -->
<?php if (isset($_SESSION['user_id'])): ?>
    <h3>댓글 작성</h3>
    <?php generate_csrf_token(); // CSRF 토큰 생성 ?>
    <form action="/comment_create.php" method="post">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <textarea name="content" rows="4" cols="50" required></textarea><br>
        <input type="submit" value="작성">
    </form>
<?php else: ?>
    <p>댓글을 작성하려면 <a href="/login">로그인</a>이 필요합니다.</p>
<?php endif; ?>

<p><a href="/board/<?= $board_slug ?>">목록으로</a></p>
</body>
