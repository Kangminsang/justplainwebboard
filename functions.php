<?php

function check_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
function h($str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function getClientIP(): string {
    // NPM 또는 다른 프록시에서 전달한 클라이언트 IP 추출
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function getAllBoards($pdo) {
    return $pdo->query("
        SELECT * FROM boards 
        WHERE is_active = TRUE 
        ORDER BY sort_order, name
    ")->fetchAll();
}

function getBoardBySlug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM boards WHERE slug = ? AND is_active = TRUE");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function validateSortMethod($sort_method) {
    $allowed_sort = ['ASC', 'DESC'];
    $sort_method = strtoupper($sort_method ?? 'DESC');
    return in_array($sort_method, $allowed_sort) ? $sort_method : 'DESC';
}
function getPosts($pdo, $sort_method = 'DESC', $search_conditions = null, $limit = null, $offset = null, $board_slug = null) {
    $sort_method = validateSortMethod($sort_method);

    $base_sql = "SELECT p.*, u.username, b.name as board_name, b.slug as board_slug
                 FROM public.posts p 
                 JOIN public.users u ON p.user_id = u.id
                 JOIN public.boards b ON p.board_id = b.id";

    $where = [];
    $params = [];

    if ($board_slug) {
        $where[] = "b.slug = ?";
        $params[] = $board_slug;
    }

    if ($search_conditions) {
        $where[] = $search_conditions['where'];
        $params = array_merge($params, $search_conditions['params']);
    }

    if (!empty($where)) {
        $base_sql .= " WHERE " . implode(' AND ', $where);
    }

    $base_sql .= " ORDER BY p.created_at " . $sort_method;

    // PostgreSQL의 LIMIT/OFFSET 지원
    if ($limit !== null) {
        $base_sql .= " LIMIT ?";
        $params[] = $limit;

        if ($offset !== null) {
            $base_sql .= " OFFSET ?";
            $params[] = $offset;
        }
    }

    $stmt = $pdo->prepare($base_sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function buildSearchConditions($type, $keyword) {
    $conditions = [];

    switch ($type) {
        case 'title':
            // PostgreSQL의 ILIKE를 사용하여 대소문자 구분 없는 검색
            $conditions['where'] = 'p.title ILIKE ?';
            break;
        case 'content':
            $conditions['where'] = 'p.content ILIKE ?';
            break;
        case 'username':
            $conditions['where'] = 'u.username ILIKE ?';
            break;
        case 'title_content':
            // 제목과 내용을 동시에 검색 (PostgreSQL OR 조건)
            $conditions['where'] = '(p.title ILIKE ? OR p.content ILIKE ?)';
            $conditions['params'] = ['%' . $keyword . '%', '%' . $keyword . '%'];
            return $conditions;
        default:
            return null;
    }

    $conditions['params'] = ['%' . $keyword . '%'];
    return $conditions;
}

// CSRF 토큰 생성 함수
function generate_csrf_token(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// CSRF 토큰 검증 함수
function validate_csrf_token(): void
{
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF 공격 차단됨!');
    }
    //사용한 토큰 제거
    unset($_SESSION['csrf_token']);
}
