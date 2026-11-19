<?php require "header.php"; ?>
<?php
if(empty($_SESSION['user'])){
    echo "ログインしてください。";
    echo "<a href='login.php'>ログイン</a>";
    exit();
}
?>
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
        <?php
        if($_SESSION['user']['authority'] == 1){
            echo '<a href="admin.php" class="menu-card">';
            echo '<div class="menu-icon">👥</div>';
            echo '<h4>ユーザー管理</h4>';
            echo '</a>';
            echo '<a href="mat.php" class="menu-card">';
            echo '<div class="menu-icon">⚙️</div>';
            echo '<h4>マット管理</h4>';
            echo '</a>';
        }
        ?>
    </div>
</main>
<div>
    <a href="login.php">ログアウト</a>
</div>
<?php require "footer.php"; ?>