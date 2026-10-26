<?php require "header.php"; ?>
<?php require "config/database.php"; ?>
<table>
    <tr>
        <th>権限</th>
        <th>ユーザー名</th>
        <th></th>
    </tr>
    <?php
    $sql = $pdo -> query("
    select * from user
    ");
    
    foreach($sql as $row){
        // 権限の数値（0 or 1）を文字列に変換
        $authority_text = ($row['authority'] == 0) ? '管理者' : '作業者';

        echo "<tr>";
        echo "<td>";
        echo $authority_text; // 変換した文字列を表示
        echo "</td>";
        echo "<td>";
        echo $row['user_name'];
        echo "</td>";
        echo "<td>";
        echo "<a href='user-edit.php'><button>編集</button></a>";
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>
<?php require "footer.php"; ?>