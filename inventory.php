<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php require "header.php"; ?>

<?php
// 検索キーワードが送信された場合の処理
$search = $_GET['search'] ?? '';

try {
    if (isset($search)) {
        // キーワードを取得して部分検索
        $sql = $pdo->prepare("select * from inventory where stock_name like ?");
        $sql->execute(['%'.$search.'%']);
    } else {
        // キーワードがない場合 全部表示
        $sql = $pdo->query("select * from inventory");
    }
} catch (Throwable $e) {
    $error_message = "データ取得エラー: " . $e->getMessage();
}
?>

<div>
    <a href="menu.php"> 戻る</a>
    <h1>在庫一覧</h1>
</div>

<!-- エラーメッセージがある場合 -->
<?php if (!empty($error_message)): ?>
    <p><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<!-- 検索フォーム -->
<form action="inventory.php" method="get">
    <input type="text" name="search" placeholder="在庫を検索">
    <input type="submit" value="検索">
</form>
<!-- 在庫登録ボタン -->
<div>
    <button type="button" onclick="location.href='inventory-add.php'">登録</button>
</div>

<!-- 在庫一覧表示 -->
<?php
// 該当する在庫が1件以上する場合
if (isset($sql) && $sql->rowCount() > 0){
    foreach ($sql as $row){
        echo "<div>";
        // 在庫ID
        echo "<span>{$row['stock_id']} </span>";
        // 商品画像
        echo "<span><img src='images/{$row['img']}' style='width: 60px;'> </span>";
        // 商品名
        echo "<span>{$row['stock_name']} </span>";
        // 在庫数・重量
        echo "<span>在庫 {$row['amount']}個 </span>";
        echo "<span>{$row['weight']}kg </span>";
        echo "</div>";
    }
}
?>
<?php require "footer.php"; ?>