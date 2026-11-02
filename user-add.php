<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php
$error_message = "";

//  登録ボタン（POST）が押されたときの処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 送られてきたデータをそれぞれ変数に代入
    $user_name = $_POST['user_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $authority = $_POST['authority'] ?? '';

    if (!empty($user_name) && !empty($password) && $authority !== '') { //それぞれ空になってないか
        try {
            // データベースに新しいユーザー情報を追加
            $sql = $pdo->prepare("insert into user (user_name, password, authority) values (?, ?, ?)");
            $sql->execute([$user_name, $password, $authority]);

            // 登録成功したらadmin.phpへリダイレクト
            header('Location: admin.php');
            exit();
            
        } catch (Throwable $e) {
            $error_message = "登録エラーが発生しました: " . $e->getMessage();
        }
    } else {
        $error_message = "すべての項目を入力してください。";
    }
}
?>

<?php require "header.php"; ?>
<header>
    <h1>ユーザー登録</h1>
</header>

<!-- エラーメッセージの表示 -->
<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form action="" method="post">
    <div>
        <p>ユーザー名</p>
        <input type="text" name="user_name" required>
    </div>
    
    <div>
        <p>パスワード</p>
        <input type="text" name="password" required>
    </div>

    <div>
        <p>権限</p>
        <select name="authority" required>
            <!-- 1:管理者、0:作業者 に設定 -->
            <option value="1">管理者</option>
            <option value="0">作業者</option>
        </select>
    </div>

    <div>
        <div>
            <button type="button" onclick="location.href='admin.php'">キャンセル</button>
            <input type="submit" value="登録">
        </div>
    </div>
</form>

<?php require "footer.php"; ?>