<?php
// JavaScriptとPHPの橋渡しをするファイルです。ユーザーに表示する画面ではありません！

require "config/database.php";

// 1. JavaScriptから送られてきたJSONデータを受け取って、PHPの配列に変換する
$raw_data = file_get_contents('php://input');
$request = json_decode($raw_data, true);

// 2. 「何をしてほしいか（action）」が書いてあるか確認
// 書いていなければ、何も処理できないのでエラーとして返す
$action = isset($request['action']) ? $request['action'] : '';

// 3. 結果を入れるための空の配列を用意
$response = [];

// 4. action の中身によって、処理を振り分ける（switch文）
switch ($action) {
    case 'get_graph_data':
        // グラフに必要なデータを返す
        // 送られてきた追加データ（年など）を変数に入れる
        $targetYear = $request['year'];
        $targetMonth = $request['month'];
        $targetStock = $request['stock'];

        if(empty($targetYear) || empty($targetMonth) || empty($targetStock)){
            break;
        }

        // 日付をsqlで扱いやすい形に変換
        $startDate = sprintf('%04d-%02d-01', $targetYear, $targetMonth);
        $endDate = date('Y-m-01', strtotime($startDate . ' +1 month'));

        $sql = $pdo -> prepare("
            select * from inventory_history
            where stock_id=?
            and update_date >= ?
            and update_date < ?
            order by update_date
        ");
        $sql -> execute([$targetStock, $startDate, $endDate]);
        $sqlResult = $sql -> fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $values = [];
        $stockName = ''; // グラフのタイトル用

        foreach ($sqlResult as $row) {
            // X軸（下）に表示する日付
            $labels[] = $row['update_date'];

            // Y軸（縦）に表示する数値（※カラム名は実際のテーブルに合わせて変更してください）
            $values[] = $row['quantity'];

            // stock_nameはどの行も同じはずなので上書き
            if (isset($row['stock_name'])) {
                $stockName = $row['stock_name'];
            }
        }


        $response = [
            'status' => 'success', // 成功したという印
            'name' => $stockName,
            "labels" => $labels,
            'values' => $values
        ];
        break;

    default:
        // 【エラー】知らない action が送られてきた場合
        $response = [
            'status' => 'error',
            'message' => '無効なリクエストです'
        ];
        break;
}

// 返す値が空の場合
if(empty($response)){
    $response = [
        'status' => 'error',
        'message' => '必要なデータが足りません。'
    ];
}

// 5. 処理結果をJSONにしてJavaScriptに返す
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;