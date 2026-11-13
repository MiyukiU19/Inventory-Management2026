<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php require "header.php"; ?>

<?php
// 検索キーワードが送信されたか判定
$is_search = isset($_GET['search']);
$search = $_GET['search'] ?? '';

try {
    if ($is_search) {
        // 検索ボタンが押された場合
        $sql = $pdo->prepare("SELECT * FROM inventory WHERE stock_name LIKE ?");
        $sql->execute(['%' . $search . '%']);
    } else {
        // 最初にページを開いた場合は全件表示
        $sql = $pdo->query("SELECT * FROM inventory");
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
    <div style="display: flex; align-items: center; gap: 10px;">
        <form action="inventory.php" method="get">
            <input type="text" name="search"
                style="border: 1px solid black; border-radius:8px"
                placeholder="🔍在庫を検索">
            <input type="submit" style="background-color:blue; color:white" value="検索">
        </form>

        <!-- 検索後だけ表示 -->
        <?php if ($is_search): ?>
            <button type="button" class="mat-edit-button"
                onclick="location.href='inventory.php'">クリア</button>
        <?php endif; ?>
    </div>

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

// 商品画像
echo "<span class='inventory-image'><img src='images/{$row['img']}'></span>";

// 商品名と在庫情報をまとめる
echo "<div class='inventory-info'>";

// 1行目：商品名
echo "<span class='inventory-name'>{$row['stock_name']}</span>";

// 合計金額
$totalQuantity = $stockData['quantity'] * $stockData['amount'];

// 2行目：在庫・総重量・合計金額
echo "<div class='inventory-details'>";
echo "<span class='inventory-detail'>在庫：{$stockData['amount']}個</span>";
echo "<span class='inventory-detail'>総重量：{$stockData['max_weight']}kg</span>";
echo "<span class='inventory-detail'>合計：{$totalQuantity}円</span>";
echo "</div>";

echo "</div>";

echo "</a>";
echo "</div>";
    }
}else {
    echo "該当する在庫はありません。";
}
?>
</div>
<?php require "footer.php"; ?>