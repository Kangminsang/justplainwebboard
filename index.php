<?php

global $pdo;
$sort_method = $_GET['sort'] ?? 'DESC';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'functions.php';

$board_slug = $_GET['board'] ?? null;
if ($board_slug) {
    $board = getBoardBySlug($pdo, $board_slug);
    $posts = getPosts($pdo, $sort_method, null, null, null, $board_slug);
} else {
    $boards = getAllBoards($pdo);
}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>메인 페이지</title>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
<?php if (!empty($board_slug)): ?>

    <!--
        <details>
            <summary>16기 유정모</summary>
            <img src="image-2.png"/>
        </details>
    !-->

<table border="1">
    <tr><th>제목</th><th>작성자</th><th>작성일</th><th>관리</th></tr>
    <?php foreach ($posts as $post): ?>
    <tr>
        <td><a href="/board/<?= $post['board_slug'] ?>/<?= $post['id'] ?>"><?= h($post['title']) ?></a></td>
        <td><?= h($post['username']) ?></td>
        <td><?= $post['created_at'] ?></td>
        <td>
            <?php include 'includes/geul_management.php' ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include 'includes/viewpage_footer.php'; ?>
<?php else: ?>
    <h1>게시판 목록</h1>
<table border="1">
    <tr><th>게시판</th><th>설명</th><th>생성일자</th></tr>
    <?php foreach ($boards as $board): ?>
    <tr>
        <td><a href="/board/<?= $board['slug'] ?>"><?= h($board['name']) ?></a></td>
        <td><?= h($board['description']) ?></td>
        <td><?= $board['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</div>
</body>
</html>
