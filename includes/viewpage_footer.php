<div class="footer">
    <form method="get">
        <select name="sort" id="sort_method" onchange="this.form.submit()">
            <option value="DESC"<?= $sort_method == "DESC" ? "selected" : "" ?>>최신순</option>
            <option value="ASC"<?= $sort_method == "ASC" ? "selected" : "" ?>>날짜순</option>
        </select>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button type="button" onclick="location.href='/board/<?= $board_slug?>/write'">글쓰기</button>
        <?php endif; ?>
    </form>
    <form action="/board/<?= $board_slug?>/search" method="get">
        <select name="search_type" id="search_type">
            <option value="title">제목</option>
            <option value="content">내용</option>
            <option value="username">작성자</option>
            <option value="title_content">제목+내용</option>
        </select>
        <input type="text" name="search" placeholder="검색어를 입력하세요">
        <input type="submit" value="검색">
    </form>
    <a href="/board">게시판 목록으로</a>
</div>