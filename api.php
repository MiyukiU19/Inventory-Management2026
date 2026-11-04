<?php
// JavaScriptとPHPの橋渡しをするファイルです。ユーザーに表示する画面ではありません！

require "config/database.php";

// JavaScriptから送られてきたJSONデータを受け取って、PHPの配列に変換する
$raw_data = file_get_contents('php://input');
$request = json_decode($raw_data, true);

// 「何をしてほしいか（action）」が書いてあるか確認
// 書いていなければ、何も処理できないのでエラーとして返す
$action = isset($request['action']) ? $request['action'] : '';

// 結果を入れるための空の配列を用意
$response = [];

// get_graph_dataを実行するための関数
function getGraphData($pdo, $request) {
    $targetYear = $request['year'] ?? null;
    $targetMonth = $request['month'] ?? null;
    $targetStocks = $request['stocks'] ?? [];

    // 【削除】$isCompare を受け取る処理はもう不要なので消します

    if(empty($targetYear) || empty($targetMonth) || empty($targetStocks) || !is_array($targetStocks)){
        return ['status' => 'error', 'message' => '必要なデータが足りません。'];
    }

    $startDate = sprintf('%04d-%02d-01', $targetYear, $targetMonth);
    $endDate = date('Y-m-01', strtotime($startDate . ' +1 month'));
    $daysInMonth = date('t', strtotime($startDate));

    $labels = [];
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $labels[] = $i . '日';
    }

    $sql = $pdo->prepare("
        SELECT * FROM inventory_history
        WHERE stock_id = ? AND update_date >= ? AND update_date < ?
        ORDER BY update_date ASC
    ");

    $allStockDatasets = [];

    foreach ($targetStocks as $stockId) {
        // --- 今年のデータを取得 ---
        $sql->execute([$stockId, $startDate, $endDate]);
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);

        $values = array_fill(1, $daysInMonth, null);
        $stockName = '商品' . $stockId;

        foreach ($result as $row) {
            $day = (int)date('d', strtotime($row['update_date']));
            $values[$day] = $row['quantity']; // ※実際のカラム名
            if (isset($row['stock_name'])) {
                $stockName = $row['stock_name'];
            }
        }

        $stockData = [
            'id'     => $stockId,
            'name'   => $stockName,
            'values' => array_values($values)
        ];

        // --- 【変更】if文をなくし、常に前年のデータを取得して追加する ---
        $lastYearStartDate = sprintf('%04d-%02d-01', $targetYear - 1, $targetMonth);
        $lastYearEndDate = date('Y-m-01', strtotime($lastYearStartDate . ' +1 month'));

        $sql->execute([$stockId, $lastYearStartDate, $lastYearEndDate]);
        $lastYearResult = $sql->fetchAll(PDO::FETCH_ASSOC);

        $lastYearValues = array_fill(1, $daysInMonth, null);

        foreach ($lastYearResult as $row) {
            $day = (int)date('d', strtotime($row['update_date']));
            $lastYearValues[$day] = $row['quantity']; // ※実際のカラム名
        }

        $stockData['last_year_values'] = array_values($lastYearValues);
        // -----------------------------------------------------------

        $allStockDatasets[] = $stockData;
    }

    return [
        'status'   => 'success',
        'labels'   => $labels,
        'datasets' => $allStockDatasets
    ];
}

// action の中身によって、処理を振り分ける（switch文）
switch ($action) {
    case 'get_graph_data':
        $response = getGraphData($pdo, $request);
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

// 処理結果をJSONにしてJavaScriptに返す
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;