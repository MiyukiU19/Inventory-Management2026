<?php require "header.php"; ?>
<?php
if(empty($_SESSION['user'])){
    echo "ログインしてください。";
    echo "<a href='login.php'>ログイン</a>";
    exit();
} else if($_SESSION['user']['authority'] != 1){
    ?>
    <p>権限がありません。自動的に3秒後に前のページに戻ります。</p>
    <p>移動しない場合は<a href="menu.php">こちら</a>をクリックしてください。</p>
    <script>
        setTimeout(function() {
            // 履歴があれば1つ戻る、直接アクセスなどで履歴がなければトップへ
            if (window.history.length > 1) {
                history.back();
            } else {
                location.href = '/index.php';
            }
        }, 3000); // 3000ミリ秒
    </script>
    <?php
    exit();
}
?>
<?php require "config/database.php"; ?>

<form id="mat-edit-form" class="mat-add-form" action="mat.php" method="post">
<div class="hd mat-edit-header">
    <button type="button" class="b mat-edit-button"  onclick="location.href='mat.php'">❮</button>
    <h1>マット編集</h1>
</div>
    <div class="mat-edit-group">
        <p class="mat-edit-label">識別番号</p>
        <?php
        $matId = $_GET['id'];
        $sql = $pdo -> prepare("select * from possess_mat where mat_id = ?");
        $sql -> execute([$matId]);
        $mat = $sql->fetch(PDO::FETCH_ASSOC);
        echo "<input class='mat-edit-input' type='text' name='identification_id' value='", $mat['identification_id'], "' placeholder='1'>";
        ?>
    </div>

    <div class="mat-edit-group">
        <p class="mat-edit-label">シリアル番号</p>
        <?php
        echo "<input class='mat-edit-input' type='text' name='serial' value='", $mat["serial_id"], "' placeholder='W12345678901' required>"
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
                if($mat['mat_spec_id'] == $key){
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
        if($mat["stock_id"] == $row['stock_id']){
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

