<?php require "config/database.php"; ?>
<?php require "header.php"; ?>
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
        echo "<tr>";
        echo "<td>";
        echo $row['serial_id'];
        echo "</td>";

        // マットに製品が紐づけられているか
        if(!empty($row["stock_name"])){
            echo "<td>";
            echo $row['stock_name'];
            echo "</td>";
        } else {
            echo "<td class='mat-unlinked'>";
            echo "未登録";
            echo "</td>";
        }

        echo "<td>";
        echo '<button onclick="location.href=', "'mat-edit.php'", '">編集</button>';
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>

<button onclick="location.href='mat-add.php'">登録</button>