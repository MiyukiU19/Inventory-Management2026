<?php require "header.php"; ?>
<?php require "config/database.php"; ?>
<header>
    <h1>ユーザー一覧</h1>
</header>
<div>
    <button type="button" onclick="location.href='user-add.php'">＋ 新規登録</button>
</div>
<table>
    <tr>
        <th>権限</th>
        <th>ユーザー名</th>
        <th></th>
    </tr>
    <?php
    $sql = $pdo->query("SELECT * FROM user");
    
    foreach($sql as $row){
        // 1が管理者、0が作業者に変換し代入
        $authority_text = ($row['authority'] == 1) ? '管理者' : '作業者';

        echo "<tr>";
        echo "<td>";
        echo $authority_text;
        echo "</td>";
        echo "<td>";
        echo $row['user_name'];
        echo "</td>";
        echo "<td>";
        echo "<a href='user-edit.php?id=" . $row['user_id'] . "'><button>編集</button></a>"; // idを送って判別
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>
<?php require "footer.php"; ?>