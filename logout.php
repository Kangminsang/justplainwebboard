<?php

$prevPage = $_SERVER['HTTP_REFERER'];
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_destroy();

header('location:'.$prevPage);
exit;