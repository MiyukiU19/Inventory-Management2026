<?php require "header.php"; ?>
<?php
if(empty($_SESSION['user'])){
    echo "ログインしてください。";
    echo "<a href='login.php'>ログイン</a>";
    exit();
} else if($_SESSION['user']['authority'] != 1){
    ?>
    <p>権限がありません。自動的に3秒後に前のページに戻ります。</p>
    <p>移動しない場合は<a href="menu.php">こちら</a>をクリックしてください。</p>
    <script>
        setTimeout(function() {
            // 履歴があれば1つ戻る、直接アクセスなどで履歴がなければトップへ
            if (window.history.length > 1) {
                history.back();
            } else {
                location.href = '/index.php';
            }
        }, 3000); // 3000ミリ秒
    </script>
    <?php
    exit();
}
?>
<?php require "config/database.php"; ?>
<?php
$error_message = "";

//  登録ボタン（POST）が押されたときの処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 送られてきたデータをそれぞれ変数に代入
    $uniform_number = trim($_POST['uniform_number'] ?? '');
    $user_name = $_POST['user_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $authority = $_POST['authority'] ?? '';
// それぞれ空になってないか
    if ($uniform_number !== '' && ctype_digit($uniform_number) && !empty($user_name) && !empty($password) && $authority !== '') { //ctype_digit()：文字列が0~9で構成されているか判定
        try {
            // データベースに新しいユーザー情報を追加
            $sql = $pdo->prepare("insert into user (uniform_number, user_name, password, authority) values (?, ?, ?)");
            $sql->execute([$user_name, $password, $authority]);

            // 登録成功したらadmin.phpへリダイレクト
            header('Location: admin.php');
            exit();
            
        } catch (Throwable $e) {
            if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) == 1062) {
                $error_message = "この背番号はすでに使われています。";
            } else {
                $error_message = "登録エラーが発生しました: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "すべての項目を入力してください。背番号は半角数字で入力してください。";
    }
}
?>

<header class="hd_h1 user-list-header user-add-header">
    <button type="button" class="b mat-edit-button back-button" onclick="location.href='admin.php'">❮</button>
    <h1>ユーザー登録</h1>
</header>

<!-- エラーメッセージの表示 -->
<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form action="" method="post" class="mat-add-form user-add-form" id="user-add-form">
    <div>
        <p>背番号</p>
        <input type="text" name="uniform_number" inputmode="numeric" pattern="[0-9]+" required>
    </div>
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
    <div class="user-add-buttons">
        <input type="submit" class="mat-edit-button" style="color:white" value="登録する">
    </div>
</div>
</form>

<?php require "footer.php"; ?>