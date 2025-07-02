<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
    <button type="button" onclick="location.href='/board/<?= $board_slug ?>/<?= $post['id'] ?>/edit'">수정</button>
    <button type="button" onclick="if(confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다!')) location.href='/delete.php?id=<?= $post['id'] ?>'">삭제</button>
<?php endif; ?>