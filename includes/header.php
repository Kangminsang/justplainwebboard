<div class="user-management">
<?php if (!isset($_SESSION['user_id'])): ?>
<button type="button" onclick="location.href='/login'">로그인</button>
<button type="button" onclick="location.href='/register'">회원가입</button>
<?php else: ?>
    <span><?= htmlspecialchars($_SESSION['username']) ?>님 환영합니다.</span>
    <button type="button" onclick="location.href='/logout'">로그아웃</button>
<?php endif; ?>
</div>
<div class="navigatior">
<nav>
<?php if (isset($board_slug)): ?>
<h1><?= getBoardBySlug($pdo, $board_slug)['name'] ?></h1>
<?php endif; ?>
</nav>
</div>