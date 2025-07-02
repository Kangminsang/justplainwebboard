<?php
if (!isset($_GET['url'])) {
    die("url 파라미터를 입력하세요");
}

$target = $_GET['url'];

$response = @file_get_contents($target);

if ($response === false) {
    http_response_code(502);
    echo "요청에 실패했습니다";
} else {
    header("Content-Type: text/plain; charset=utf-8");
    echo $response;
}
