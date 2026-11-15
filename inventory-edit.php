<?php
require "header.php";
require "config/database.php";

$stockId = $_GET['id'];

// inventoryからデータを取り出す
$stockSql = $pdo -> prepare("select * from inventory where stock_id = ?");
$stockSql -> execute([$stockId]);
$stockData = $stockSql -> fetch(PDO::FETCH_ASSOC);

// inventory_historyからデータを取り出す
$lastStockSql = $pdo -> prepare("
    select * from inventory_history
    where stock_id = ?
    order by update_date desc
    limit 1
");
$lastStockSql -> execute([$stockId]);
$lastStockData = $lastStockSql -> fetch(PDO::FETCH_ASSOC);

// possess_matからデータを取り出す
$matSql = $pdo -> prepare("select * from possess_mat where stock_id = ?");
$matSql -> execute([$stockId]);
$mat = $matSql -> fetch(PDO::FETCH_ASSOC);
?>
<script src="js/chart.js" defer></script>
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
            echo '<img src="images/', $stockData['img'], '">'
            ?>

            <div class="inventory-detail-stock">
                <div>
                    <p>在庫 <?php echo $lastStockData['quantity'] ?>個</p>
                    <p><?php echo $lastStockData['max_weight'] ?>kg</p>
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
                    <div id="border-line"><?php echo $stockData["border_line"]?></div>個
                </div>

                <!-- 編集ボタンが押されるまで非表示 -->
                <div style="display: none;"><input type="text" id="border-line-input">個</div>
            </div>
            <div>
                <p>容器の重量</p>
                <div>
                    <div id="container-weight"><?php echo $stockData["container_weight"]?></div>kg
                </div>

                <!-- 編集ボタンが押されるまで非表示 -->
                <div style="display: none;"><input type="text" id="container-input">kg</div>
            </div>
            <div>
                <p>マットのシリアル番号</p>
                <div>
                    <p id="serial-id"><?php echo $mat["serial_id"]?></p>
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
                    <div id="amount"><?php echo $stockData["amount"] ?></div>円
                </div>
                <div style="display: none;"><input id="amount-input">円</div>
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