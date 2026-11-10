<?php require "header.php"; ?>
<?php require "config/database.php"; ?>

<form id="mat-edit-form" class="mat-add-form" action="mat.php" method="post">
<div class="hd mat-edit-header">
    <button type="button" class="b mat-edit-button"  onclick="location.href='mat.php'">❮</button>
    <h1 style="margin-left: 150px;">マット編集</h1>
</div>
    <div class="mat-edit-group">
        <p class="mat-edit-label">シリアル番号</p>
        <?php
        $matId = $_GET['id'];
        $sql = $pdo -> prepare("select * from possess_mat where mat_id = ?");
        $sql -> execute([$matId]);
        $mat = $sql->fetch(PDO::FETCH_NUM);
        echo "<input class='mat-edit-input' type='text' name='serial' value='", $mat[1], "' required>"
        ?>
    </div>

    <div class="mat-edit-group">
        <p class="mat-edit-label">サイズ</p>
        <select class="mat-edit-select" name="size" required>
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
    echo "<p class='mat-edit-label'>連携製品</p>";
    echo "<select class='mat-edit-select' name='inventory'>";
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

    <div class="mat-edit-actions" style="gap: 20px; " >
        <input id="mat-delete" class="mat-delete-button" type="submit"  style="background-color: red; color:white; " value="削除する">
        <input id="mat-edit" class="mat-update-button" type="submit" style="color: white;" value="更新する">
    </div>

</form>

<?php require "footer.php"; ?>

