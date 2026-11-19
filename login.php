<?php require "header.php"; ?>
<?php require "config/database.php"; ?>
<?php
$_SESSION['user'] = null; // このページにアクセスしたら自動的にログアウトする。

$error_message = "";
// ログインボタンが押された場合の処理
if ($_SERVER["REQUEST_METHOD"] === "POST") { //送信方式がPOSTか判定
    $uniform_number = $_POST['uniform-number'] ?? ''; //送られてきたデータを代入
    $password = $_POST['password'] ?? ''; //?? '' もし送られてきた値がnullなら空の文字列を代入

    if (!empty($uniform_number) && !empty($password)) { //どっちも入力されている場合
        try {
            // ユーザー名でデータベースを検索
            $sql = $pdo->prepare('SELECT * FROM user WHERE uniform_number = ?');
            $sql->execute([$uniform_number]);
            $row = $sql->fetch(); // データベースを検索し、該当するユーザーを配列として取得し、変数に代入

            // ユーザーが存在し、かつパスワードが一致するかどうか
            if ($row && $row['password'] === $password) {
                // セッションにユーザー情報を保存
                $_SESSION['user'] = [
                    'id' => $row['id'],
                    'user_name' => $row['user_name'],
                    'authority' => $row['authority'],
                    'uniform_number' => $row['uniform_number']
                ];

                // --- 権限によって遷移先を切り替える処理 ---
                // authorityが 1 なら menu.php、0なら inventory.php へリダイレクト
                if ((int)$row['authority'] === 1) {
                    header('Location: menu.php');
                } else {
                    header('Location: menu.php');
                }
                exit();
            } else {
                $error_message = '背番号またはパスワードが間違っています。'; // ユーザーが見つからなかった、パスワードが違う場合の処理
            }
        } catch (Throwable $e) {
            $error_message = 'データベースエラーが発生しました: ' . $e->getMessage(); // tryの中でデータベース接続やSQLの実行エラーが起きた場合
        }
    } else {
        $error_message = '背番号とパスワードを入力してください。'; // 背番号またはパスワードどちらかが空欄の場合
    }
}
?>

<?php require "header.php"; ?>

<header>
    <h1 class="hd_h1" style="font-size:33px;">ログイン</h1>
</header>

<main class="login-page">
    <form action="" class="login_form" method="post">

        <p>
            背番号<br>
            <input type="text" name="uniform-number">
        </p>

        <p>
            パスワード<br>
            <input type="password" name="password">
        </p>
    <!-- エラーメッセージ -->
    <?php if (!empty($error_message)): ?>
        <p class="login-error">
            <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>
        <p class="login-submit">
            <input type="submit" value="ログイン">
        </p>

    </form>

</main>

<?php require "footer.php"; ?>