<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php require "header.php"; ?>

<?php
// 検索キーワードが送信されたか判定
$search = trim($_GET['search'] ?? ''); //trim()は文字列の不要な空白や改行を取り除く
$is_search = ($search !== ''); //searchが空白でない時 判定
$category_id = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? intval($_GET['category_id']) : '';
$is_category_selected = ($category_id !== '');

// カテゴリ一覧の取得
try {
    $cat_stmt = $pdo->query("SELECT * FROM category");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $categories = [];
    $error_message = "カテゴリ取得エラー: " . $e->getMessage();
}

try {
    // 検索機能と絞り込み機能
    $sql_parts = ['select * from inventory where 1=1'];
    $params = [];

    // 検索ボタンが押された場合
    if ($is_search) {
        $sql_parts[] = "stock_name LIKE ?";
        $params[] = '%' . $search . '%';
    }
    // 絞り込み カテゴリーを選択したら
    if ($is_category_selected) {
        $sql_parts[] = "category_id = ?";
        $params[] = $category_id;
    }

    $sql = $pdo->prepare(implode(" AND ", $sql_parts));
    $sql->execute($params);

} catch (Throwable $e) {
    $error_message = "データ取得エラー: " . $e->getMessage();
}

// 小数点以下2桁以降の0を削除する、小数点以下が存在しない場合.0を付ける
function formatDecimalFlexible($num) {
    // 小数点を含まない整数なら .0 をつけて返す
    if (strpos((string)$num, '.') === 0) {
        return $num . '.0';
    }

    // 末尾の0を削除（例: 123.00 -> 123.）
    $trimmed = rtrim((string)$num, '0');

    // 末尾が小数点になってしまった場合（例: 123.00 -> 123.）は 0 を足す
    if (substr($trimmed, -1) === '.') {
        return $trimmed . '0';
    }

    return $trimmed;
}

function formatNumberKeepDecimals($num) {
    // 文字列に変換して小数点で分割
    $parts = explode('.', (string)$num);

    // 整数部分のみ3桁区切りにする
    $integerPart = number_format((float)$parts[0]);

    // 小数部分があれば結合して返す
    if (isset($parts[1])) {
        return $integerPart . '.' . $parts[1];
    }

    return $integerPart;
}
?>

<header class="hd_h1 inventory-header">
    <button type="button" class="mat-edit-button back-button"
        onclick="location.href='menu.php'">❮</button>
    <h1>在庫一覧</h1>
</header>

<!-- 検索フォーム & 絞り込み機能-->
<div class="inventory-controls">
    <div style="display: flex; align-items: center; gap: 10px;">
        <!-- キーワード検索 -->
        <form action="inventory.php" method="get" onsubmit="return checkSearch(this)">
            <input type="text" name="search" id="searchInput"
                value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                style="border: 1px solid black; border-radius:8px" placeholder="🔍在庫を検索">
            <!-- カテゴリ選択中の場合は保持する -->
            <?php
            if($is_category_selected) {
                echo "<input type='hidden' name='category_id' value='" . htmlspecialchars($category_id, ENT_QUOTES, 'UTF-8') . "'>";
            }
            ?>
            <input type="submit" style="background-color:blue; color:white" value="検索">
        </form>

        <!-- クリアボタン 検索後だけ表示（キーワード検索のみを解除） -->
        <?php if ($is_search): ?>
            <button type="button" class="mat-edit-button"
                onclick="location.href='inventory.php<?php echo $is_category_selected ? '?category_id=' . $category_id : ''; ?>'">クリア</button>
        <?php endif; ?>
    </div>

    <!-- 絞り込みプルダウン -->
    <div>
        <form action="inventory.php" method="get">
            <!-- キーワード検索中の場合は保持する -->
            <?php
                if($is_search) {
                echo "<input type='hidden' name='search' value='" . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . "'>";
                }
            ?>
            <select name="category_id" onchange="this.form.submit()" style="border: 1px solid black; border-radius:8px; padding: 4px 8px;">
                <option value="">すべてのカテゴリ</option>
                <?php
                foreach ($categories as $cat){
                    $selected = ($category_id === (int)$cat['category_id']) ? 'selected' : '';
                    $cat_id = htmlspecialchars($cat['category_id'], ENT_QUOTES, 'UTF-8');
                    $cat_name = htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8');
                    echo "<option value='{$cat_id}' {$selected}>{$cat_name}</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <!-- 在庫登録ボタン -->
    <div>
    <button type="button" class="mat-edit-button"
        onclick="location.href='inventory-add.php'">登録</button>
    </div>
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
$totalAmount = $stockData['quantity'] * $stockData['amount'];

// 総重量の末尾の0を調整
$maxWeight = formatDecimalFlexible($stockData['max_weight']);

// 3桁ごとにカンマを付ける
$formatQuantity = formatNumberKeepDecimals($stockData['quantity']);
$formatMaxWeight = formatNumberKeepDecimals($maxWeight);
$formatTotalAmount = formatNumberKeepDecimals($totalAmount);

// 2行目：在庫・総重量・合計金額
echo "<div class='inventory-details'>";
echo "<span class='inventory-detail'>在庫：{$formatQuantity}個</span>";
echo "<span class='inventory-detail'>総重量：{$formatMaxWeight}kg</span>";
echo "<span class='inventory-detail'>合計：{$formatTotalAmount}円</span>";
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