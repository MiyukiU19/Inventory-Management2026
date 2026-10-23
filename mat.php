<?php require "config/database.php"; ?>
<table>
    <tr>
        <th>識別番号</th>
        <th>シリアル</th>
        <th>製品</th>
    </tr>
    <?php
    $sql = $pdo -> query("
        select * from possess_mat as pm
        left join inventory as i
        on pm.stock_id = i.stock_id
        order by pm.identification_id
    ");

    foreach($sql as $row){
        echo "<tr>";
        echo "<td>";
        echo $row['identification_id'];
        echo "</td>";
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
        echo "</tr>";
    }
    ?>
</table>