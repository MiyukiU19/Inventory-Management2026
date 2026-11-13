<?php require "header.php"; ?>
<?php require "config/database.php"; ?>

<main class="page-container user-list-page">

<div class="hd user-list-header">
    <a href="menu.php"  style="left: 30px;" class="b mat-edit-button back-button">❮</a>
    <h1 style="font-size: 25px;">ユーザー管理</h1>
</div>
<div class="user-list-footer">
    <button type="button" class="mat-edit-button" onclick="location.href='user-add.php'">
        登録
    </button>
</div>
<section class="card user-list-card">

    <table class="user-table">
        <thead>
            <tr>
                <th>権限</th>
                <th>ユーザー名</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <?php
            $sql = $pdo->query("SELECT * FROM user");

            foreach ($sql as $row) {
                $authority_text = ($row['authority'] == 1) ? '管理者' : '作業者';

                echo "<tr>";

                echo "<td>";
                echo "<span class='authority-badge ";
                echo ($row['authority'] == 1) ? "admin" : "worker";
                echo "'>";
                echo $authority_text;
                echo "</span>";
                echo "</td>";

                echo "<td>";
                echo htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8');
                echo "</td>";

                echo "<td>";
                echo "<a href='user-edit.php?id=" . (int)$row['user_id'] . "' class='b mat-edit-button'>編集</a>";
                echo "</td>";

                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

</section>
</main>
<?php require "footer.php"; ?>