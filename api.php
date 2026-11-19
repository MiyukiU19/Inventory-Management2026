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
    $targetStock = $request['stock'] ?? null;

    if (empty($targetYear) || empty($targetMonth) || empty($targetStock)) {
        return ['status' => 'error', 'message' => '必要なデータが足りません。'];
    }

    $startDate = sprintf('%04d-%02d-01', $targetYear, $targetMonth);
    $endDate = date('Y-m-01', strtotime($startDate . ' +1 month'));
    $daysInMonth = (int)date('t', strtotime($startDate));

    $labels = [];
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $labels[] = $i . '日';
    }

    $sql = $pdo->prepare("
        SELECT * FROM inventory_history
        WHERE stock_id = ? AND update_date >= ? AND update_date < ?
        ORDER BY update_date ASC
    ");

    $sql->execute([$targetStock, $startDate, $endDate]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    // 1日〜末日までを null で初期化（インデックスは1〜末日）
    $values = array_fill(1, $daysInMonth, null);
    $stockName = '商品 ' . $targetStock;

    foreach ($result as $row) {
        $day = (int)date('d', strtotime($row['update_date']));
        $values[$day] = $row['quantity'];

        if (!empty($row['stock_name'])) {
            $stockName = $row['stock_name'];
        }
    }

    $stockData = [
        'label'  => $stockName,
        'data'   => array_values($values)
    ];

    return [
        'status'   => 'success',
        'labels'   => $labels,
        'datasets' => [$stockData]
    ];
}

function getMultiGraphData($pdo, $request) {
    $targetYear = $request['year'] ?? null;
    $targetMonth = $request['month'] ?? null;
    $targetStocks = $request['stocks'] ?? [];

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

        // --- 前年のデータを取得して追加する ---
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
        $allStockDatasets[] = $stockData;
    }

    return [
        'status'   => 'success',
        'labels'   => $labels,
        'datasets' => $allStockDatasets
    ];
}

function getNotConnectedMat($pdo){
    $sql = $pdo -> query("
        select * from possess_mat
        where stock_id is null
    ");

    $notConnectedMat = $sql -> fetchAll(PDO::FETCH_ASSOC);
    return [
        "status" => "success",
        "mat" => $notConnectedMat
    ];
}

function setUpdateInventory($pdo, $request){
    $stockId         = (int)$request["stockId"]; 
    $prevMatId       = (int)$request["prevMatId"];
    $newMatId        = (($request["newMatId"] ?? "") === "") ? null : (int)$request["newMatId"]; // 未連携を選んだら空文字が届くのでnullにする
    $borderLine      = $request["borderLine"];
    $containerWeight = $request["containerWeight"];
    $amount          = $request["amount"];
    $weight          = $request["weight"];
    $categoryId      = (int)$request["categoryId"];

    $sql = $pdo -> prepare('
        update inventory
        set border_line = ?, container_weight = ?, amount = ?, weight = ?, category_id = ? 
        where stock_id = ?
    ');
    $sql -> execute([$borderLine, $containerWeight, $amount, $weight, $categoryId, $stockId]);

    // 元のマットに連携されていたstock_idをnullにして新しく連携するマットにstock_idを登録する
    $sql = $pdo -> prepare("
        update possess_mat
        set stock_id = NULL
        where mat_id = ?;
    ");
    $sql -> execute([$prevMatId]);

    // 新しいマットにstock_idを割り振る
    if ($newMatId !== null) {  //未連携を選んだ場合は何もしない
            $sql = $pdo -> prepare("
                update possess_mat
                set stock_id = ?
                where mat_id = ?
            ");
            $sql -> execute([$stockId, $newMatId]);
        }

    return [
        "status" => "success"
    ];
}

// $requestに入っているstock_idに紐づけられたmat_idを返す
function getConnectedMatById($pdo, $request){
    $stockId = $request['stock_id'];
    $sql = $pdo -> prepare("
        select * from possess_mat
        where stock_id = ?
    ");
    $sql -> execute([$stockId]);
    $mat = $sql -> fetch(PDO::FETCH_ASSOC);
    return [
        "status" => "success",
        "mat" => $mat
    ];
}

// $requestに入っているborderLineを取得する
function getBorderLine($pdo, $request){
    $stockId = $request['stock_id'];
    $sql = $pdo -> prepare("
        select * from inventory
        where stock_id = ?
    ");
    $sql -> execute([$stockId]);
    $borderLine = $sql -> fetch(PDO::FETCH_ASSOC)['border_line'];

    return [
        "status" => "success",
        "border_line" => $borderLine
    ];
}

// action の中身によって、処理を振り分ける（switch文）
switch ($action) {
    case 'get_graph_data':
        $response = getGraphData($pdo, $request);
        break;

    case "get_multi_graph_data":
        $response = getMultiGraphData($pdo, $request);
        break;

    case "get_not_connected_mat":
        $response = getNotConnectedMat($pdo);
        break;

    case "set_update_inventory":
        $response = setUpdateInventory($pdo, $request);
        break;

    case "get_connected_mat_by_id":
        $response = getConnectedMatById($pdo, $request);
        break;

    case "get_border_line":
        $response = getBorderLine($pdo, $request);
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