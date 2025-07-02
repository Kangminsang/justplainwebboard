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

// 게시글 조회 및 권한 확인
$stmt = $pdo->prepare('SELECT user_id FROM posts WHERE id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    die('게시글이 존재하지 않습니다.');
}
if ($post['user_id'] != $_SESSION['user_id']) {
    die('권한이 없습니다.');
}

//첨부파일 삭제 (파일 시스템)
$stmt = $pdo->prepare('SELECT file_name FROM attachments WHERE post_id = ?');
$stmt->execute([$post_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($files as $file) {
    $filePath = UPLOAD_DIR . $file['file_name'];
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            die('Failed to delete file: ' . $file['file_name']);
        }
    }
}

// 게시글 삭제 (comments 및 attachments는 CASCADE)
$stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
$stmt->execute([$post_id]);

header('Location: index.php');
exit;

