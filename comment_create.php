<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'functions.php';
check_login();
validate_csrf_token(); // CSRF 토큰 검증

$post_id = $_POST['post_id'] ?? null;
$content = $_POST['content'] ?? '';
if (!$post_id || !$content) {
    die('댓글 내용을 입력하세요.');
}

// 댓글 삽입
$stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
$stmt->execute([$post_id, $_SESSION['user_id'], $content]);

header('Location: view.php?id=' . $post_id);
exit;

