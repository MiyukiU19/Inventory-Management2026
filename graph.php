<?php require "config/database.php"; ?>
<?php require "header.php"; ?>
<script src="js/chart.js" defer></script>
<script src="js/choices.min.js" defer></script>
<script src="js/graph.js" defer></script>
<link rel="stylesheet" href="css/choices.min.css">

<form>
    <div>
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
        <select>
            
        </select>
    </div>
    <div>
        <p>前年比</p>
        <input type="checkbox">
    </div>
</form>


<!-- グラフ -->
<div>
    <canvas id="stock-chart"></canvas>
</div>
<?php require "footer.php"; ?>