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
        onclick="location.href='menu.php'">戻る</button>
    <h1>在庫一覧</h1>
</header>

<!-- エラーメッセージがある場合 -->
<?php if (!empty($error_message)): ?>
    <p><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<!-- 検索フォーム -->
<div class="inventory-controls">
    <form action="inventory.php" method="get">
        <input type="text" name="search" placeholder="🔍在庫を検索">
        <input type="submit" style="background-color:blue; color:white" value="検索">
    </form>
<!-- 在庫登録ボタン -->
<button type="button" class="mat-edit-button"
        onclick="location.href='inventory-add.php'">登録</button>
</div>
<!-- 在庫一覧表示 -->
<?php
// 該当する在庫が1件以上する場合
// if (isset($sql) && $sql->rowCount() > 0){
//     foreach ($sql as $row){
//         echo "<div>";
//         echo "<a href='inventory-edit.php?id=".(int)$row['stock_id']."'>";
//         // 在庫ID
//         echo "<span>{$row['stock_id']} </span>";
//         // 商品画像
//         echo "<span><img src='images/{$row['img']}' style='width: 60px;'> </span>";
//         // 商品名
//         echo "<span>{$row['stock_name']} </span>";
//         // 在庫数・重量・金額
//         echo "<span>在庫：{$row['weight']}個 </span>"; //個数 総重量(データ取得)÷1個あたりの重量(weight)
//         echo "<span>総重量：kg </span>"; //総重量(データ取得) 
//         echo "<span>金額：{$row['amount']}円 </span>"; //合計金額 1つあたりの金額(amount)×個数
//         echo "</a>";
//         echo "</div>";
//     }
// }
if (isset($sql) && $sql->rowCount() > 0){
    foreach ($sql as $row){
        echo "<div class='inventory-item'>";
        echo "<a href='inventory-edit.php?id=".(int)$row['stock_id']."'>";
        echo "<span class='inventory-id'>{$row['stock_id']}</span>";
        echo "<span class='inventory-image'><img src='images/{$row['img']}'></span>";
        echo "<span class='inventory-name'>{$row['stock_name']}</span>";
        echo "<span class='inventory-detail'>在庫：{$row['weight']}個</span>";
        echo "<span class='inventory-detail'>総重量：kg</span>";
        echo "<span class='inventory-detail'>金額：{$row['amount']}円</span>";
        echo "</a>";
        echo "</div>";
    }
}
?>
<?php require "footer.php"; ?>