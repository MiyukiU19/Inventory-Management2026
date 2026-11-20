<?php require "header.php"; ?>
<?php
if(empty($_SESSION['user'])){
    echo "ログインしてください。";
    echo "<a href='login.php'>ログイン</a>";
    exit();
}
?>
<?php require "config/database.php"; ?>
<script src="js/chart.js" defer></script>
<script src="js/choices.min.js" defer></script>
<script src="js/graph.js" defer></script>
<link rel="stylesheet" href="css/choices.min.css">


<div class="hd user-list-header graph-header">
    <a href="menu.php" class="b mat-edit-button back-button">❮</a>
    <h1 style="font-size: 25px;">在庫状況</h1>
</div>
<form class="graph-controls">
    <div id="stocks">
        <p>商品名</p>
        <select id="stock-select" multiple>
            <?php
            $sql = $pdo->query("select * from inventory");
            foreach($sql as $row){
                echo "<option value='", $row['stock_id'], "'>";
                echo $row['stock_name'];
                echo "</option>";
            }
            ?>
        </select>
    </div>

    <div>
        <p>期間</p>
        <select id="chart-period">
            <?php
            $year = (int)date('Y');  // 画面表示時の年（ここから遡る）
            $month = (int)date('n'); // 画面表示時の月（ここから遡る）
            $minYear = 2023;         // 下限の年
            $minMonth = 10;          // 下限の月

            // 現在の年月が、下限（2026年11月）以上の間ループし続ける
            while (($year > $minYear) || ($year == $minYear && $month >= $minMonth)) {
                $displayMonth = str_replace(' ', ' ', sprintf("%2d", $month));
                echo "<option>{$year}年 {$displayMonth}月</option>\n";

                // 月を1つ戻す
                $month--;

                // 1月より前になったら、前年の12月にする
                if ($month < 1) {
                    $month = 12;
                    $year--;
                }
            }
            ?>
        </select>
    </div>
    <div>
        <p>前年比</p>
        <input id="check-last-year" type="checkbox" style="width: 20px; height:20px; border:1px solid black;">
    </div>
</form>


<!-- グラフ -->
<div>
    <canvas id="stock-chart"></canvas>
</div>
<?php require "footer.php"; ?>