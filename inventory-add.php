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
    $mat_id           = $_POST['serial_id'] ?? null; // マットID　※設定しなくてもOK
    $img              = ''; // 商品画像

    // 画像ファイルが選択され、エラーなくアップロードされたか確認
    // $_FILES → PHPでenctype="multipart/form-data"を使って送信された情報を格納する変数（ファイル特化）
    // $FILES['img']['error'] ファイルのアップロード時にエラーが発生したかどうかを示すコードを取得
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) { 
        $file_tmp  = $_FILES['img']['tmp_name']; 
        $file_name = basename($_FILES['img']['name']);  //basename ファイル名だけを取り出す関数

        // すでに存在するimages/ フォルダを保存先として指定
        $upload_path = 'images/' . $file_name;

        // 一時保存された画像ファイルをimagesフォルダへ移動
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $img = $upload_path; // データベースに画像を保存
        } else {
            $error_message = "画像のアップロードに失敗しました。";
        }
    } else {
        // 画像が選択されなかった場合は仮の画像を自動設定
        $img = 'no_toroku.png';
    }

    // 必須事項の入力があるかチェック
    if (!empty($stock_name) && !empty($weight) && !empty($amount) && !empty($border_line)) {
        try {
            // トランザクション開始
            $pdo->beginTransaction();

            // データベースに新しい在庫情報を追加
            $sql = $pdo->prepare("insert into inventory (stock_name, weight, container_weight, amount, border_line, img) values (?, ?, ?, ?, ?, ?)");
            $sql->execute([$stock_name, $weight, $container_weight, $amount, $border_line, $img]);

            // 2. 今登録したばかりの在庫の自動採番された stock_id を取得する
            $new_stock_id = $pdo->lastInsertId();

            // 3. シリアル番号（mat_id）が選択されていた場合、所有マット (possess_mat) 側を更新する
            if (!empty($mat_id)) {
                $update_sql = $pdo->prepare("UPDATE possess_mat SET stock_id = ? WHERE mat_id = ?");
                $update_sql->execute([$new_stock_id, $mat_id]);
            }

            // すべて成功したらコミット（確定）
            $pdo->commit();

            // 登録成功したらadmin.phpへリダイレクト
            header('Location: inventory.php');
            exit();
            
        } catch (Throwable $e) {
            // エラーが発生した場合はロールバック
            // エラーが発生しても入力された内容がリセットされないようになった
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
    <h1>在庫登録</h1>
</div>

<!-- エラーメッセージの表示 -->
<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form action="" method="post" enctype="multipart/form-data">
    <div>
        <p>商品名</p>
        <input type="text" name="stock_name" required>
    </div>
    <div>
        <p>1つあたりの重量</p>
        <input type="text" name="weight" required>kg
    </div>
    <div>
        <p>容器の重さ</p>
        <input type="text" name="container_weight" required>kg
    </div>
    <div>
        <p>1つあたりの金額</p>
        <input type="text" name="amount" required>円
    </div>
    <div>
        <p>発注基準値</p>
        <input type="text" name="border_line" required>個
    </div>
    <div>
        <p>シリアル番号</p>
        <?php
            $sql = $pdo->query("select * from possess_mat where stock_id is null");
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
        <div>
            <button type="button" onclick="location.href='inventory.php'">キャンセル</button>
            <input type="submit" value="登録">
        </div>
    </div>
</form>

<?php require "footer.php"; ?>