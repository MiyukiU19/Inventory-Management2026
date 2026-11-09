<?php session_start(); ?>
<?php require "config/database.php"; ?>
<?php
$error_message = "";

//  登録ボタン（POST）が押されたときの処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 送られてきたデータをそれぞれ変数に代入
    $stock_name       = $_POST['stock_name'] ?? ''; // 商品名
    $weight           = $_POST['weight'] ?? ''; // 1個あたりの重さ
    $container_weight = $_POST['container_weight'] ?? ''; // 容器の重さ
    $amount           = $_POST['amount'] ?? ''; // 1個あたりの金額
    $border_line      = $_POST['border_line'] ?? ''; // 発注基準値
    $mat_id           = $_POST['mat_id'] ?? null; // マットID　※設定しなくてもOK
    $img              = $_POST['img'] ?? ''; // 商品画像

    // 必須事項の入力があるかチェック
    if (!empty($stock_name) && !empty($weight) && !empty($amount) && !empty($border_line)) {
        try {
            // データベースに新しい在庫情報を追加
            $sql = $pdo->prepare("insert into inventory (stock_name, weight, container_weight, amount, border_line, mat_id, img) values (?, ?, ?, ?, ?, ?, ?)");
            $sql->execute([$stock_name, $weight, $container_weight, $amount, $border_line, $mat_id, $img]);

            // 登録成功したらadmin.phpへリダイレクト
            header('Location: inventory.php');
            exit();
            
        } catch (Throwable $e) {
            $error_message = "登録エラーが発生しました: " . $e->getMessage();
        }
    } else {
        $error_message = "必須項目を入力してください。";
    }
}
?>

<?php require "header.php"; ?>
<div>
    <!-- <a href="inventory.php"> 戻る</a> -->
    <h1 class="hd_h1">在庫登録</h1>
</div>

<!-- エラーメッセージの表示 -->
<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form class="mat-add-form inventory-add-form" action="" method="post" enctype="multipart/form-data">
    <div>
        <p>商品名</p>
        <input type="text" name="stock_name" required>
    </div>
    <div>
        <p>1つあたりの重量</p>
            <div class="inventory-input-unit">
                <input type="text" name="weight" required>
                <span>kg</span>
            </div>
    </div>
    <div>
        <p>容器の重さ</p>
            <div class="inventory-input-unit">
                <input type="text" name="container_weight" required>
                <span>kg</span>
            </div>
    </div>
    <div>
        <p>1つあたりの金額</p>
            <div class="inventory-input-unit">
                <input type="text" name="amount" required>
                <span>円</span>
            </div>
    </div>
    <div>
        <p>発注基準値</p>
            <div class="inventory-input-unit">
                <input type="text" name="border_line" required>
                <span>個</span>
            </div>
    </div>
    <div>
        <p>シリアル番号</p>
        <?php
            echo "<input type='hidden' name='action' value='add'>";
            $sql = $pdo->query("select * from possess_mat");
            echo "<select name='serial_id'>";
            echo "<option></option>";
            foreach($sql as $row){
                echo "<option value='{$row['mat_id']}'>{$row['serial_id']}</option>";
            }
            echo "</select>";
        ?>
    </div>
    <div>
        <p>商品画像</p>
        <input type="file" name="img" accept="image/*">
    </div>

    <div>
        <div class="inventory-add-buttons">
            <button type="button" class="cancel-button" onclick="location.href='inventory.php'">キャンセル</button>
            <input type="submit" value="登録">
        </div>  
    </div>
</form>

<?php require "footer.php"; ?>