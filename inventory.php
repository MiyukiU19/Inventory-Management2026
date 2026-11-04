<?php require "config/database.php"; ?>
<?php require "header.php"; ?>
<div>
    <h1>在庫一覧</h1>
</div>
<form action="">
    <input type="text" name="search-holder">
    <input type="submit" value="検索">
</form>
<table>
    <tr>
        <td>id</td>
        <td>画像</td>
        <td>商品名</td>
        <td>個数</td>
        <td>総重量</td>
    </tr>
</table>
<?php require "footer.php"; ?>