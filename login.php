<?php require "header.php"; ?>
<header>
    <h1>ログイン</h1>
</header>
<form action="menu.php" method="post">
    <p>
        ユーザー名<br>
        <input type="text" name="user-name"><br>
    </p>
    <p>
        パスワード<br>
        <input type="password" name="password"><br>
    </p>
    <p>
        <input type="submit" value="ログイン">
    </p>
</form>
<?php require "footer.php"; ?>