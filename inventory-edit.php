<?php
require "header.php";
require "config/database.php";

$stockId = $_GET['id'] ?? 0;

// カテゴリー一覧を取得する
$categories = $pdo->query("SELECT category_id, category_name FROM category ORDER BY category_id")
                ->fetchAll(PDO::FETCH_ASSOC);

// inventoryからデータを取り出す
$stockSql = $pdo -> prepare("
    select inventory.* , category.category_name 
    from inventory left join category on inventory.category_id = category.category_id
    where inventory.stock_id = ?
    ");
$stockSql -> execute([$stockId]);
$stockData = $stockSql -> fetch(PDO::FETCH_ASSOC);

// 該当する在庫がない場合は、エラーにならないようにメッセージを出して終了する
if (!$stockData) {
    echo "<p>該当する在庫が見つかりません。</p>";
    exit();
}

// inventory_historyからデータを取り出す
$lastStockSql = $pdo -> prepare("
    select * from inventory_history
    where stock_id = ?
    order by update_date desc
    limit 1
");
$lastStockSql -> execute([$stockId]);
$lastStockData = $lastStockSql -> fetch(PDO::FETCH_ASSOC);

// 履歴が1件もない在庫でもエラーにならないようにする
if (!$lastStockData) {
    $lastStockData = ['quantity' => 0, 'max_weight' => 0];
}

// possess_matからデータを取り出す
$matSql = $pdo -> prepare("select * from possess_mat where stock_id = ?");
$matSql -> execute([$stockId]);
$mat = $matSql -> fetch(PDO::FETCH_ASSOC);

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
?>
<script src="js/chart.js" defer></script>
<script src="js/chartjs-plugin-annotation.min.js" defer></script>
<script src="js/inventory-edit.js" defer></script>
<header class="hd_h1 inventory-header">
    <button type="button" class="mat-edit-button back-button"
        onclick="location.href='inventory.php'">❮</button>
    <h1></h1>
</header>

<main class="page-container inventory-detail-page">
    <div class="inventory-detail-top">
        <div class="inventory-detail-product">
            <!-- 画像 -->
            <?php
            echo '<img src="images/', htmlspecialchars($stockData['img'], ENT_QUOTES, 'UTF-8'), '">'
            ?>

            <div class="inventory-detail-stock">
                <div>
                    <p>在庫 <?php echo $lastStockData['quantity'] ?>個</p>
                    <p><?php
                    echo formatDecimalFlexible($lastStockData['max_weight']);
                    ?>kg</p>
                </div>
                <button type="button" id="inventory-edit-btn" class="mat-edit-button">在庫編集</button>

                <!-- 保存とキャンセルボタン(編集ボタンが押されるまでは非表示) -->
                <div id="edit-btns" style="display: none;">
                    <button id="inventory-confirm-btn">保存</button>
                    <button id="inventory-cancel-btn">キャンセル</button>
                </div>
            </div>
        </div>

        <!-- このdivの子要素にインデントを付けるとバグる可能性があります -->
        <div class="inventory-detail-info">
            <div>
                <p>発注基準値</p>
                <div>
                    <div id="border-line"><?php echo htmlspecialchars($stockData["border_line"], ENT_QUOTES, 'UTF-8'); ?></div>個
                </div>

                <!-- 編集ボタンが押されるまで非表示 -->
                <div style="display: none;"><input type="text" id="border-line-input">個</div>
            </div>
            <div>
                <p>容器の重量</p>
                <div>
                    <div id="container-weight"><?php
                    echo formatDecimalFlexible($stockData["container_weight"]);
                    ?></div>kg
                </div>

                <!-- 編集ボタンが押されるまで非表示 -->
                <div style="display: none;"><input type="text" id="container-input">kg</div>
            </div>
            <div>
                <p>マット番号</p>
                <div>
                    <p id="serial-id">(<?php echo $mat["identification_id"]?>) <?php echo $mat["serial_id"]?></p>
                </div>

                <!-- 編集ボタンが押されるまで非表示 -->
                <div style="display: none;"><select id="serial-select"></select></div>

                <!-- マットID -->
                <div style="display: none;">
                    <input id="hidden-mat-id" type="hidden" value="<?php echo $mat["mat_id"] ?>">
                </div>
            </div>
            <div>
                <p>一つ当たりの金額</p>
                <div>
                    <div id="amount"><?php echo htmlspecialchars($stockData["amount"], ENT_QUOTES, 'UTF-8'); ?></div>円
                </div>
                <div style="display: none;"><input id="amount-input">円</div>
            </div>
            <div>
                <p>1個あたりの重さ</p>
                <div>
                    <div id="weight"><?php echo formatDecimalFlexible($stockData["weight"]); ?></div>kg
                </div>
                <!-- 編集ボタンが押されるまで非表示 -->
                <div style="display: none;"><input type="text" id="weight-input">kg</div>
            </div>
            <div>
                <p>カテゴリー</p>
                <div>
                    <!-- <div id="category-name"><?php //echo htmlspecialchars($stockData["category_name"] ?? '', ENT_QUOTES, 'UTF-8'); ?></div> -->
                    <!-- data-category-id: キャンセル時や再編集時に、元のカテゴリーの選択に戻すために使う -->
                    <div id="category-name" data-category-id="<?php echo (int)$stockData['category_id']; ?>"><?php echo htmlspecialchars($stockData["category_name"] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <!-- 編集ボタンが押されるまで非表示 -->
                <div style="display: none;">
                    <select id="category-select">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['category_id']; ?>"
                                <?php if ($cat['category_id'] == $stockData['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- グラフ -->
    <div class="inventory-detail-graph">
        <div class="inventory-month-control">
            <button id="prev-month-btn">&lt;</button>
            <div id="display-month">
                <p id="selected-month">10</p>
                <p>月</p>
            </div>
            <button id="next-month-btn">&gt;</button>
        </div>

        <canvas id="stock-chart"></canvas>
    </div>

</main>
<script defer>
    const stockId = <?php echo $stockId; ?>;
    const prevMonthBtn = document.querySelector("#prev-month-btn");
    const selectedMonth = document.querySelector("#selected-month");
    const nextMonthBtn = document.querySelector("#next-month-btn");
    let stockChart = document.querySelector("#stock-chart");
    let selectedYear = 2023; // 後で変更
</script>