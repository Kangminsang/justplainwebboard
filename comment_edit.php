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

// 댓글 조회
$stmt = $pdo->prepare('SELECT * FROM comments WHERE id = ?');
$stmt->execute([$comment_id]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$comment) {
    die('댓글이 존재하지 않습니다.');
}
if ($comment['user_id'] != $_SESSION['user_id']) {
    die('권한이 없습니다.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    if (!$content) {
        die('댓글 내용을 입력하세요.');
    }
    // 댓글 수정
    $stmt = $pdo->prepare('UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$content, $comment_id]);
    header('Location: view.php?id=' . $comment['post_id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>댓글 수정</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<h1>댓글 수정</h1>
<form action="comment_edit.php?id=<?= $comment_id ?>" method="post">
    <textarea name="content" rows="4" cols="50" required><?= h($comment['content']) ?></textarea><br>
    <input type="submit" value="수정">
</form>
<p><a href="view.php?id=<?= $comment['post_id'] ?>">뒤로</a></p>
</body>
</html>
