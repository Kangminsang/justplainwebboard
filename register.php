<?php

global $pdo;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php'; // PDO 인스턴스 \$pdo 생성

$error_message = '';
$success_message = '';

// 회원가입 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = '⚠ 유효한 이메일 주소를 입력해주십시오.';
    } elseif ($password !== $confirm_password) {
        $error_message = '⚠ 입력하신 비밀번호가 일치하지 않습니다.';
    } elseif (strlen($password) < 10) {
        $error_message = '⚠ 비밀번호는 최소 10자 이상이어야 합니다.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = '⚠ 이미 등록된 이메일입니다.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetchColumn() > 0) {
                $error_message = '⚠ 다른 사용자가 사용중인 닉네임입니다.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed,
            ]);
            $success_message = '회원가입이 완료되었습니다! 이제 로그인 페이지로 이동합니다.';
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
    <div class="container">
        <h1>회원가입</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
                <meta http-equiv="refresh" content="2;url=/login"/>
            </div>
        <?php else: ?>
            <form method="post" action="register.php">
                <p>
                    <div class="form-group">
                        <label for="username">닉네임</label><br>
                        <input type="text" id="username" name="username" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>

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
                    <div class="form-group">
                        <label for="confirm_password">비밀번호 확인</label><br>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                <p>
                    <div class="form-actions">
                        <button type="submit">가입하기</button>
                    </div>
                <p>
                <div class="form-footer">
                    이미 가입되어 있으신가요? <a href="/login">로그인하기</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>