<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php
$error_message = "";

// ログインボタンが押された場合の処理
if ($_SERVER["REQUEST_METHOD"] === "POST") { //送信方式がPOSTか判定
    $user_name = $_POST['user-name'] ?? ''; //送られてきたデータを代入
    $password = $_POST['password'] ?? ''; //?? '' もし送られてきた値がnullなら空の文字列を代入

    if (!empty($user_name) && !empty($password)) { //どっちも入力されている場合
        try {
            // ユーザー名でデータベースを検索
            $sql = $pdo->prepare('SELECT * FROM user WHERE user_name = ?');
            $sql->execute([$user_name]);
            $row = $sql->fetch(); // データベースを検索し、該当するユーザーを配列として取得し、変数に代入

            // ユーザーが存在し、かつパスワードが一致するかどうか
            if ($row && $row['password'] === $password) {
                // セッションにユーザー情報を保存
                $_SESSION['user'] = [
                    'id' => $row['id'],
                    'user_name' => $row['user_name'],
                    'authority' => $row['authority']
                ];

                // --- 権限によって遷移先を切り替える処理 ---
                // authorityが 1 なら menu.php、0なら inventory.php へリダイレクト
                if ((int)$row['authority'] === 1) {
                    header('Location: menu.php');
                } else {
                    header('Location: inventory.php');
                }
                exit();
            } else {
                $error_message = 'ユーザー名またはパスワードが間違っています。'; // ユーザーが見つからなかった、パスワードが違う場合の処理
            }
        } catch (Throwable $e) {
            $error_message = 'データベースエラーが発生しました: ' . $e->getMessage(); // tryの中でデータベース接続やSQLの実行エラーが起きた場合
        }
    } else {
        $error_message = 'ユーザー名とパスワードを入力してください。'; // ユーザー名またはパスワードどちらかが空欄の場合
    }
}
?>

<?php require "header.php"; ?>
<header>
    <h1>ログイン</h1>
</header>

<!-- エラーメッセージがある場合に赤字で表示 -->
<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

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