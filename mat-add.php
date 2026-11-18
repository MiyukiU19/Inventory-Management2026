<?php require "header.php"; ?>
<?php
if(empty($_SESSION['user'])){
    echo "ログインしてください。";
    echo "<a href='login.php'>ログイン</a>";
    exit();
}
?>
<?php require "config/database.php"; ?>

    <header class="hd_h1 mat-add-header">
    <button type="button" class="b mat-edit-button back-button"
    onclick="history.back()">❮</button>
    <h1 class="hd_h1">マット追加</h1>
    </header>

<form action="mat.php" method="post" class="mat-add-form">
    <div>
        <p>識別番号</p>
        <input type="text" name="identification_id" placeholder="1">
    </div>
    <div>
        <p>シリアル番号</p>
        <input type="text" name="serial" placeholder="W12345678901" required>
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
        <input type="submit" value="追加する" style="color: white;">
    </div>
</form>