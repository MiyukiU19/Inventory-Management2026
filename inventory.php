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

<header class="hd_h1 inventory-header">
    <button type="button" class="mat-edit-button back-button"
        onclick="location.href='menu.php'">❮</button>
    <h1>在庫一覧</h1>
</header>

<!-- 検索フォーム -->
<div class="inventory-controls">
    <form action="inventory.php" method="get">
        <input type="text" name="search" style="border: 1px solid black ; border-radius:8px" placeholder="🔍在庫を検索">
        <input type="submit" style="background-color:blue; color:white" value="検索">
    </form>
<!-- 在庫登録ボタン -->
<button type="button" class="mat-edit-button"
        onclick="location.href='inventory-add.php'">登録</button>
</div>

<!-- エラーメッセージがある場合 -->
<?php if (!empty($error_message)): ?>
    <p><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<!-- 在庫一覧表示 -->
<div>
<?php
// 該当する在庫が1件以上する場合
if (isset($sql) && $sql->rowCount() > 0){
    foreach ($sql as $row){
        // $rowに入っているデータから単価や総重量などを取り出すためのSQL文
        $stockSql = $pdo -> prepare("
            select * from inventory_history
            where stock_id = ?
            order by update_date desc
            limit 1
        ");
        $stockSql -> execute([$row['stock_id']]);
        $stockData = $stockSql -> fetchAll(PDO::FETCH_ASSOC)[0];

        echo "<div class='inventory-item'>";
        echo "<a href='inventory-edit.php?id=".(int)$row['stock_id']."'>";
        // 在庫ID
        // echo "<span class='inventory-id'>{$row['stock_id']}</span>";

       // 商品画像
        echo "<span class='inventory-image'><img src='images/{$row['img']}'></span>";

        // 商品名
        echo "<span class='inventory-name'>{$row['stock_name']}</span>";

        // 在庫数
        echo "<span class='inventory-detail'>在庫：{$stockData['amount']}個</span>";

        // 総重量
        echo "<span class='inventory-detail'>総重量：{$stockData['max_weight']}kg</span>";

        // 合計金額
        $totalQuantity = $stockData['quantity'] * $stockData['amount'];
        echo "<span class='inventory-detail'>合計金額：{$totalQuantity}円</span>";
        echo "</a>";
        echo "</div>";
    }
}else {
    echo "該当する在庫はありません。";
}
?>
</div>
<?php require "footer.php"; ?>