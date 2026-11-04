<?php session_start(); ?>
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