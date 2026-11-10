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
<div>
    <div>
        <!-- 画像 -->
        <?php
        echo '<img src="images/', $stockData['img'], '">'
        ?>
        <div>
            <div>
                <!-- 個数と重量 -->
                <p>在庫 <?php echo $lastStockData['quantity'] ?>個</p>
                <p><?php echo $lastStockData['max_weight'] ?>kg</p>
            </div>
            <button>在庫編集</button>
        </div>
    </div>
    <div>
        <div>
            <p>発注基準値</p>
            <p><?php echo $stockData["border_line"]?>個</p>
        </div>
        <div>
            <p>容器の重量</p>
            <p><?php echo $stockData["container_weight"]?>kg</p>
        </div>
        <div>
            <p>マットのシリアル番号</p>
            <p><?php echo $mat["serial_id"]?></p>
        </div>
    </div>
</div>

<!-- グラフ -->
<div>
    <div>
        <button id="prev-month-btn"><</button>
        <div id="display-month">
            <p id="selected-month">10</p>
            <p>月</p>
        </div>
        <button id="next-month-btn">></button>
    </div>
    <canvas id="stock-chart"></canvas>
</div>
<script defer>
    const stockId = <?php echo $stockId; ?>;
    const prevMonthBtn = document.querySelector("#prev-month-btn");
    const selectedMonth = document.querySelector("#selected-month");
    const nextMonthBtn = document.querySelector("#next-month-btn");
    let stockChart = document.querySelector("#stock-chart");
    let selectedYear = 2023; // 後で変更
</script>