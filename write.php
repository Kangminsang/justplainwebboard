<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';
require_once 'functions.php';
//check_login();

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$board_slug = $_GET['board'] ?? null;
$board_id = null;
$board_name = null;

if ($board_slug) {
    $board_info = getBoardBySlug($pdo, $board_slug);
    $board_id = $board_info['id'];
    $board_name = $board_info['name'];
} else {
    die('게시판 미지정');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //CSRF 토큰 검증
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF 공격 차단됨!");
    }
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $post_board_id = $_POST['board_id'] ?? null;
    if (!$title || !$content) {
        die('제목과 내용을 입력하세요.');
    }

    if (!$post_board_id || !is_numeric($post_board_id)) {
        die('유효하지 않은 게시판 ID입니다.');
    }

    $stmt = $pdo->prepare("SELECT created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $last_post = $stmt->fetch();

    if ($last_post) {
        $last_time = strtotime($last_post['created_at']);
        $current_time = time();

        if ($current_time - $last_time < 5) {
            die("너무 빠르게 작성하고 있습니다. 잠시 후 다시 시도해주세요.");
        }
    }
    // 게시글 삽입
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, board_id, title, content) VALUES (?, ?, ?, ?) RETURNING id');
    $stmt->execute([$_SESSION['user_id'], $post_board_id, $title, $content]);
    $post_id = $stmt->fetchColumn();

    // 첨부파일 처리
    if (!empty(array_filter($_FILES['attachments']['name']))) {
        if (count($_FILES['attachments']['name']) > 5) {
            die('첨부파일은 최대 5개까지 가능합니다.');
        }
        foreach ($_FILES['attachments']['error'] as $key => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['attachments']['tmp_name'][$key];
                $originalName = basename($_FILES['attachments']['name'][$key]);
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $newName = uniqid() . ($ext ? ".$ext" : '');
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }
                move_uploaded_file($tmpName, UPLOAD_DIR . $newName);

                // DB에 첨부파일 정보 저장
                $stmt = $pdo->prepare('INSERT INTO attachments (post_id, file_name, original_name) VALUES (?, ?, ?)');
                $stmt->execute([$post_id, $newName, $originalName]);
            }
        }
    }

    header('Location: /board/' . h($board_slug) . '/' . h($post_id));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>새 글 작성</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<h1>새 글 작성</h1>
<form action="/board/<?= h($board_slug) ?>/write" method="post" enctype="multipart/form-data">
    <input type="hidden" name="board_id" value="<?= h($board_id) ?>">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
    <p>제목: <input type="text" name="title" required></p>
    <p>내용:<br><textarea name="content" rows="10" cols="50" required></textarea></p>
    <p>첨부파일 (최대 5개, 총 100MB 미만): <input type="file" name="attachments[]" multiple></p>
    <p><input type="submit" value="등록"></p>
</form>
<p><a href="/board/<?= h($board_slug) ?>">목록으로</a></p>
</body>
</html>
