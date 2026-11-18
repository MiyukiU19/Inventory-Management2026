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
// 1. URLのパラメータ（?id=〜）から削除するユーザーのIDを取得
$user_id = $_GET['id'] ?? null;

// 2. IDが空でなければデータベースから削除を実行
if (!empty($user_id)) {
    try {
        $sql = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
        $sql->execute([$user_id]);
    } catch (Throwable $e) {
        // エラー処理（必要に応じてログ出力やエラー表示）
    }
}

// 3. 処理が終わったら自動的に管理画面（admin.php）へ戻す
header('Location: admin.php');
exit();
?>