<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php
$error_message = "";

// 1. admin.phpのURL（?id=〜）からユーザーIDを取得
$user_id = $_GET['id'] ?? null;

// idが取得できなかったらadminページへ追い返す
if (empty($user_id)) {
    // admin.phpにリダイレクト
    header('Location: admin.php');
    exit();
}

// 2. 更新ボタン（POST）が押されたときの処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 送られてきたデータをそれぞれ変数に代入
    $user_name = $_POST['user_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $authority = $_POST['authority'] ?? '';

    if (!empty($user_name) && !empty($password) && $authority !== '') { //それぞれ空になってないか
        try {
            // データベースの情報を更新
            $sql = $pdo->prepare("UPDATE user SET user_name = ?, password = ?, authority = ? WHERE user_id = ?");
            // 用意した?の部分に入力したものを当てはめて、データベースの情報を更新する
            $sql->execute([$user_name, $password, $authority, $user_id]);

            // 更新成功したらadmin.phpへ移動する
            header('Location: admin.php');
            exit();
            
        } catch (Throwable $e) {
            $error_message = "更新エラーが発生しました: " . $e->getMessage();
        }
    } else {
        $error_message = "すべての項目を入力してください。";
    }
}

// 3. データベースから現在のユーザー情報を取得してフォームに初期表示する
try {
    // user_idが?の人の情報をすべて取得
    $sql = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $sql->execute([$user_id]);
    // PHPで扱いやすい連想配列に変えて代入
    $user = $sql->fetch();

    if (!$user) {
        // 該当するユーザーがいない場合、admin画面に飛ばす
        header('Location: admin.php');
        exit();
    }
} catch (Throwable $e) { // データベース接続失敗などのトラブルがあった場合
    echo "データ取得エラー: " . $e->getMessage();
    exit();
}
?>

<?php require "header.php"; ?>
<header>
    <h1>ユーザー編集</h1>
</header>

<!-- エラーや成功メッセージの表示 -->
<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form action="" method="post">
    <div>
        <p>ユーザー名</p>
        <input type="text" name="user_name" value="<?php echo htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    
    <div>
        <p>パスワード</p>
        <input type="text" name="password" value="<?php echo htmlspecialchars($user['password'], ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div>
        <p>権限</p>
        <select name="authority" required>
            <!-- 1:管理者、0:作業者 に設定 -->
            <option value="1" <?php if ($user['authority'] == 1) echo 'selected'; ?>>管理者</option>
            <option value="0" <?php if ($user['authority'] == 0) echo 'selected'; ?>>作業者</option>
        </select>
    </div>

    <div>
        <!-- 削除ボタン -->
        <button type="button" onclick="if(confirm('本当に削除しますか？')) location.href='user-delete.php?id=<?php echo $user_id; ?>';">削除</button>
        
        <div>
            <button type="button" onclick="location.href='admin.php'">キャンセル</button>
            <input type="submit" value="更新">
        </div>
    </div>
</form>

<?php require "footer.php"; ?>