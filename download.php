<?php

global $pdo;
require_once 'db.php';
require_once 'functions.php';

$attachment_id = $_GET['id'] ?? null;
if (!$attachment_id) {
    die('파일 ID가 없습니다.');
}

// 첨부파일 조회
$stmt = $pdo->prepare('SELECT * FROM attachments WHERE id = ?');
$stmt->execute([$attachment_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$file) {
    die('파일이 존재하지 않습니다.');
}

$filePath = UPLOAD_DIR . $file['file_name'];
if (!file_exists($filePath)) {
    die('파일을 찾을 수 없습니다.');
}

// 다운로드
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;

