<?php require "header.php"; ?>
<?php require "config/database.php"; ?>
<form id="mat-edit-form" action="mat.php" method="post">
    <div>
        <p>シリアル番号</p>
        <?php
        $matId = $_GET['id'];
        $sql = $pdo -> prepare("select * from possess_mat where mat_id = ?");
        $sql -> execute([$matId]);
        $mat = $sql->fetch(PDO::FETCH_NUM);
        echo "<input type='text' name='serial' value='", $mat[1], "' required>"
        ?>
    </div>
    <div>
        <p>サイズ</p>
        <select name="size" required>
            <?php
            $matSpecs = [
                1 => "500キロマット",
                2 => "A6",
                3 => "A5",
                4 => "A4",
                5 => "A3"
            ];

            foreach($matSpecs as $key => $value){
                if($mat[3] == $key){
                    echo "<option value='$key' selected>$value</option>";
                } else {
                    echo "<option value='$key'>$value</option>";
                }
            }
            ?>
        </select>
    </div>
    <?php
    echo "<input id='mat-edit-action' type='hidden' name='action' value='edit'>";
    echo "<input type='hidden' name='id' value='$matId'>";
    $sql = $pdo -> query("select * from inventory");
    echo "<p>連携製品</p>";
    echo "<select name='inventory'>";
    echo "<option></option>";
    foreach($sql as $row){
        if($mat['2'] == $row['stock_id']){
            echo "<option value='", $row['stock_id'],"' selected>", $row['stock_name'],"</option>";
        } else {
            echo "<option value='", $row['stock_id'],"'>", $row['stock_name'],"</option>";
        }
    }
    echo "</select>";
    ?>
    <div>
        <input id="mat-delete" type="submit" value="削除する">
        <button id="cancel-mat-add" type="button" onclick="location.href='mat.php'">キャンセル</button>
        <input id="mat-edit" type="submit" value="更新する">
    </div>
</form>
<?php require "footer.php"; ?>