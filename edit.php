<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'functions.php';
check_login();

$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    die('게시글 ID가 없습니다.');
}

$board_slug = $_GET['board'] ?? null;

// 게시글 조회
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    die('게시글이 존재하지 않습니다.');
}
if ($post['user_id'] != $_SESSION['user_id']) {
    die('권한이 없습니다.');
}

// 첨부파일 조회
$stmt = $pdo->prepare('SELECT * FROM attachments WHERE post_id = ?');
$stmt->execute([$post_id]);
$attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $post_board_slug = $_POST['board_slug'] ?? null;
    if (!$title || !$content) {
        die('제목과 내용을 입력하세요.');
    }

    // 게시글 수정
    $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$title, $content, $post_id]);

    // 기존 첨부파일 삭제 처리
    if (isset($_POST['delete_attachments']) && is_array($_POST['delete_attachments'])) {
        foreach ($_POST['delete_attachments'] as $attachment_id) {
            // 해당 첨부파일 정보 조회
            $stmt = $pdo->prepare('SELECT * FROM attachments WHERE id = ? AND post_id = ?');
            $stmt->execute([$attachment_id, $post_id]);
            $file_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($file_to_delete) {
                // 실제 파일 삭제
                $file_path = UPLOAD_DIR . $file_to_delete['file_name'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                // DB에서 첨부파일 정보 삭제
                $stmt = $pdo->prepare('DELETE FROM attachments WHERE id = ?');
                $stmt->execute([$attachment_id]);
            }
        }
    }

    // 첨부파일 처리 (새로운 파일 추가)
    if (!empty(array_filter($_FILES['attachments']['name']))) {
        // 현재 첨부파일 개수 확인 (삭제될 파일 제외)
        $current_attachments_count = count($attachments) - (isset($_POST['delete_attachments']) ? count($_POST['delete_attachments']) : 0);
        $new_files_count = count(array_filter($_FILES['attachments']['name']));

        if (($current_attachments_count + $new_files_count) > 5) {
            die('첨부파일은 최대 5개까지 가능합니다. (현재 ' . $current_attachments_count . '개, 새로 추가 ' . $new_files_count . '개)');
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
                $stmt = $pdo->prepare('INSERT INTO attachments (post_id, file_name, original_name) VALUES (?, ?, ?)');
                $stmt->execute([$post_id, $newName, $originalName]);
            }
        }
    }

    header('Location: /board/' . $board_slug . '/' . $post_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시글 수정</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<h1>게시글 수정</h1>
<form action="/board/<?= h($board_slug) ?>/<?= $post_id ?>/edit" method="post" enctype="multipart/form-data">
    <input type="hidden" name="board_slug" value="<?= h($board_slug) ?>">
    <p>제목: <input type="text" name="title" value="<?= h($post['title']) ?>" required></p>
    <p>내용:<br><textarea name="content" rows="10" cols="50" required><?= h($post['content']) ?></textarea></p>
    <?php if (!empty($attachments)): ?>
        <h3>현재 첨부파일</h3>
        <ul>
            <?php foreach ($attachments as $attachment): ?>
                <li>
                    <?= h($attachment['original_name']) ?>
                    <input type="checkbox" name="delete_attachments[]" value="<?= h($attachment['id']) ?>"> 삭제
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p>새 첨부파일 추가 (최대 5개): <input type="file" name="attachments[]" multiple></p>
    <p><input type="submit" value="수정"></p>
</form>
<p><a href="/board/<?= h($board_slug) ?>/<?= $post_id ?>">게시글으로</a></p>
</body>
</html>
