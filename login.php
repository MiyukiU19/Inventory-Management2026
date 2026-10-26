<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php

?>

<?php require "header.php"; ?>
<header>
    <h1>ログイン</h1>
</header>
<form action="" method="post">
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