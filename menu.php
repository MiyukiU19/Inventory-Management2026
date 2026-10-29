<?php require "header.php"; ?>
<header>

    <h1 class="hd_h1" >メニュー</h1>
</header>
<main>
    <div class="menu">
        <a href="inventory.php" class="menu-card">
            <div class="menu-icon">🔩</div>
            <h4>在庫管理</h4>
        </a>
        <a href="graph.php" class="menu-card">
            <div class="menu-icon">📈</div>
            <h4>グラフ</h4>
        </a>
        <a href="admin.php" class="menu-card">
            <div class="menu-icon">👥</div>
            <h4>ユーザー管理</h4>
        </a>
        <a href="mat.php" class="menu-card">
            <div class="menu-icon">⚙️</div>
            <h4>マット管理</h4>
        </a>
    </div>
</main>
<?php require "footer.php"; ?>