<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$sort_method = $_GET['sort'] ?? 'DESC';
require_once 'db.php';
require_once 'functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $board_slug = $_GET['board'] ?? null;
    $keyword = $_GET['search'] ?? '';
    $type = $_GET['search_type'];

    $posts = getPosts($pdo, $sort_method, buildSearchConditions($type, $keyword), null, null, $board_slug);

}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>검색</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<?php if (empty($posts)): ?>
    <div class="no-result">
        <h1>:(</h1>
        <p><?= $keyword?>에 대한 검색 결과가 없습니다</p>
    </div>
<?php else: ?>
<h1>:)</h1>
<p><?= $keyword?>에 대한 검색 결과</p>
<table border="1">
    <tr><th>제목</th><th>작성자</th><th>작성일</th><th>관리</th></tr>
    <?php /** @noinspection PhpUndefinedVariableInspection */
    foreach ($posts as $post): ?>
        <tr>
            <td><a href="view.php?id=<?= $post['id'] ?>"><?= h($post['title']) ?></a></td>
            <td><?= h($post['username']) ?></td>
            <td><?= $post['created_at'] ?></td>
            <td>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                    <a href="edit.php?id=<?= $post['id'] ?>">수정</a>
                    <a href="delete.php?id=<?= $post['id'] ?>" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include 'includes/viewpage_footer.php'; ?>
</body>
</html>