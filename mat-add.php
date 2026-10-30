<?php require "config/database.php"; ?>
<?php require "header.php"; ?>

    <button type="button" class="b mat-edit-button" style="margin-left:23px; margin-top:20px" onclick="history.back()">戻る</button>
    <h1 class="hd_h1">マット一覧</h1>

<form action="mat.php" method="post" class="mat-add-form">
    <div>
        <p>シリアル番号</p>
        <input type="text" name="serial"  required>
    </div>
    <div>
        <p>サイズ</p>
        <select name="size" required>
            <option value="1">500キロマット</option>
            <option value="2">A6</option>
            <option value="3">A5</option>
            <option value="4">A4</option>
            <option value="5">A3</option>
        </select>
    </div>
    <?php
    echo "<input type='hidden' name='action' value='add'>";
    $sql = $pdo -> query("select * from inventory");
    echo "<p>連携製品</p>";
    echo "<select name='inventory'>";
    echo "<option></option>";
    foreach($sql as $row){
        echo "<option value='", $row['stock_id'],"'>", $row['stock_name'],"</option>";
    }
    echo "</select>";
    ?>
    <div>
        <button id="cancel-mat-add" type="button" onclick="location.href='mat.php'">キャンセル</button>
        <input type="submit" value="追加する" style="color: white;">
    </div>
</form>