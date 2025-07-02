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

// 첨부파일 경로 검증

// 파일명에서 Path traversal 관련 문자 제거
$fileName = basename($file['file_name']);

// 실제 파일 경로 계산
$filePath = UPLOAD_DIR . $fileName;

// 파일이 실제로 UPLOAD_DIR 내에 있는지 검증
if (strpos(realpath($filePath), realpath(UPLOAD_DIR)) !== 0) {
    die('잘못된 접근입니다.');
}

if (!file_exists($filePath)) {
    die('파일을 찾을 수 없습니다.');
}

// 다운로드
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;