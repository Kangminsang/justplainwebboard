<?php

const UPLOAD_DIR = __DIR__ . '/uploads/';

try {
    $db = 'pgsql:host=db;port=5432;dbname=web_db';
    $user = 'php_user';
    /** @noinspection SpellCheckingInspection */
    $password = 'gju&2%fyzRUOHCO!3u43y61buNeKtzJ@h0Cvc8TQ';

    $pdo = new PDO($db, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo date("접속 시간: Y년 m월 d일 h:i:s");
} catch (PDOException $e) {
    echo "DB 접속 실패" . $e->getMessage();
}

