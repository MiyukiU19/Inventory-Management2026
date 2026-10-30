<?php require "config/database.php"; ?>
<?php require "header.php"; ?>
<?php
// マット追加ページから飛んできたとき
if(!empty($_POST['action'])){
    switch($_POST['action']){
        case "add":
            $serial = $_POST['serial'];
            $inventory = (empty($_POST['inventory'])) ? null : (int)$_POST['inventory'];
            $size = (int)$_POST['size'];
            $sql = $pdo -> prepare("insert into possess_mat values (null, ?, ?, ?)");
            $sql -> execute([$serial, $inventory, $size]);
            break;
        case "edit":
            $matId = (int)$_POST['id'];
            $serial = $_POST['serial'];
            $inventory = (empty($_POST['inventory'])) ? null : (int)$_POST['inventory'];
            $size = (int)$_POST['size'];
            $sql = $pdo -> prepare("update possess_mat set serial_id=?, stock_id=?, mat_spec_id=? where mat_id=?");
            $sql -> execute([$serial, $inventory, $size, $matId]);
            break;
        case "delete":
            $matId = (int)$_POST['id'];
            $sql = $pdo -> prepare("delete from possess_mat where mat_id = ?");
            $sql -> execute([$matId]);
            break;
    }
}
?>

<main class="page-container mat-list-page">

<div class="hd">
    <button type="button" class="b mat-edit-button" style="margin-left:23px" onclick="history.back()">戻る</button>
    <h1>マット一覧</h1>
</div>
<section class="card mat-list-card">
            <div class="mat-list-footer">
        <a class="b p mat-add-button" href="mat-add.php">
            ＋ 登録
        </a>
    </div>

    <div class="mat-table-wrapper">
        <table class="mat-table">
            <thead>
                <tr>
                    <th>シリアル</th>
                    <th>製品</th>
                    <th class="mat-action-column"></th>
                </tr>
            </thead>

            <tbody>
                <?php
                $sql = $pdo->query("
                    SELECT *
                    FROM possess_mat AS pm
                    LEFT JOIN inventory AS i
                        ON pm.stock_id = i.stock_id
                ");

                foreach ($sql as $row) {
                    $serialId = $row['serial_id'];
                    $stockName = $row['stock_name'];
                    $matId = (int)$row['mat_id'];
                ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars((string)$serialId, ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?php if (!empty($stockName)) { ?>
                                <?= htmlspecialchars((string)$stockName, ENT_QUOTES, 'UTF-8') ?>
                            <?php } else { ?>
                                <span class="mat-unlinked">未登録</span>
                            <?php } ?>
                        </td>

                        <td class="mat-action-column">
                            <a
                                class="b mat-edit-button"
                                href="mat-edit.php?id=<?= $matId ?>"
                            >編集</a>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($sql->rowCount() === 0) { ?>
                    <tr>
                        <td colspan="3" class="mat-empty">
                            マットが登録されていません。
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    
</section>