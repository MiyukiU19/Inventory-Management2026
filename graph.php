<?php require "config/database.php"; ?>
<?php require "header.php"; ?>
<script src="js/chart.js" defer></script>
<script src="js/choices.min.js" defer></script>
<script src="js/graph.js" defer></script>
<link rel="stylesheet" href="css/choices.min.css">

<form>
    <div id="stocks">
        <p>商品名</p>
        <select id="stock-select" multiple>
            <?php
            // 全ての在庫を複数選択できるようにする。
            $sql = $pdo -> query("select * from inventory");
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
            // 2026年11月から現在の月まで選択肢を作成する
            $currentYear = 2022;  // 開始年
            $currentMonth = 10;   // 開始月
            $endYear = (int)date('Y'); // 現在の年
            $endMonth = (int)date('n'); // 現在の月

            // 「現在の年が終了年より小さい」または「同じ年だけど月が終了月以下」の間ループし続ける
            while (($currentYear < $endYear) || ($currentYear == $endYear && $currentMonth <= $endMonth)) {
                // sprintfで2桁空白埋めし、できた空白をHTML用の '&nbsp;' に置き換える
                $displayMonth = str_replace(' ', '&nbsp;', sprintf("%2d", $currentMonth)); // <- うまくいっていないので、可能ならCSSで調整してほしいです。

                // optionタグの出力
                echo "<option value='{$currentYear} {$currentMonth}'>";
                echo "{$currentYear}年 {$displayMonth}月";
                echo "</option>\n";

                // 月を1つ進める
                $currentMonth++;

                // もし12月を超えたら、翌年の1月にする
                if ($currentMonth > 12) {
                    $currentMonth = 1;
                    $currentYear++;
                }
            }
            ?>
        </select>
    </div>
    <div>
        <p>前年と比較</p>
        <input id="check-last-year" type="checkbox">
    </div>
</form>


<!-- グラフ -->
<div>
    <canvas id="stock-chart"></canvas>
</div>
<?php require "footer.php"; ?>