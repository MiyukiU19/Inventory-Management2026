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
            var_dump($matId, $serial, $inventory, $size);
            $sql -> execute([$serial, $inventory, $size, $matId]);
            break;
    }
}
?>
<table>
    <tr>
        <th>シリアル</th>
        <th>製品</th>
        <th></th>
    </tr>
    <?php
    $sql = $pdo -> query("
        select * from possess_mat as pm
        left join inventory as i
        on pm.stock_id = i.stock_id
    ");

    foreach($sql as $row){
        $serialId = $row['serial_id'];
        $stockName = $row["stock_name"];
        $matId = $row['mat_id'];
        echo "<tr>";
        echo "<td>";
        echo $serialId;
        echo "</td>";

        // マットに製品が紐づけられているか
        if(!empty($stockName)){
            echo "<td>";
            echo $stockName;
            echo "</td>";
        } else {
            echo "<td class='mat-unlinked'>";
            echo "未登録";
            echo "</td>";
        }

        echo "<td>";
        echo '<button onclick="location.href=', "'mat-edit.php?id={$matId}'", '">編集</button>';
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>

<button onclick="location.href='mat-add.php'">登録</button>