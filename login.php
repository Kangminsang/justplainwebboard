<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php'; // PDO 인스턴스 \$pdo 생성

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $success_message = $user['username']."님, 환영합니다!";
    } else {
        $error_message = "⚠ 이메일 또는 비밀번호가 잘못되었습니다";
    }

}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
    <h1>로그인</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success-message">
            <?php echo $success_message; ?>
            <meta http-equiv="refresh" content="1;url=/"/>
        </div>
    <?php else: ?>
        <form method="post" action="/login">
            <p>
            <div class="form-group">
                <label for="email">이메일</label><br>
                <input type="text" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <p>
            <div class="form-group">
                <label for="password">비밀번호</label><br>
                <input type="password" id="password" name="password" required>
            </div>
            <p>
            <div class="form-actions">
                <button type="submit">로그인</button>
            </div>
            <p>
            <div class="form-footer">
                계정이 없으신가요? <a href="/register">가입하기</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>