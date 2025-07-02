<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'functions.php';
check_login();

$comment_id = $_GET['id'] ?? null;
if (!$comment_id) {
    die('댓글 ID가 없습니다.');
}

// 댓글 조회 및 권한 확인
$stmt = $pdo->prepare('SELECT * FROM comments WHERE id = ?');
$stmt->execute([$comment_id]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$comment) {
    die('댓글이 존재하지 않습니다.');
}
if ($comment['user_id'] != $_SESSION['user_id']) {
    die('권한이 없습니다.');
}

// 댓글 삭제
$stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
$stmt->execute([$comment_id]);

header('Location: view.php?id=' . $comment['post_id']);
exit;

