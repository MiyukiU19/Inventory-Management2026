
async function createChart(){
    // PHPに送るデータ
    const requestData = {
        action: "get_graph_data",
        stock: 1,
        year: 2023,
        month: 10,
    };

    const response = await fetch('api.php', {
        method: 'POST', // 「データを送るよ」という合図
        headers: {
            'Content-Type': 'application/json' // 「中身はJSON形式だよ」という説明
        },
        body: JSON.stringify(requestData) // JavaScriptのデータをJSONの文字列に変換して送る
    });

    const data = await response.json();

    // もしPHP側からエラーが返ってきた場合の処理
    if (data.status === 'error') {
        alert('エラーが発生しました: ' + data.message);
        return; // ここで処理を止める
    }

    console.log(data);
    const canvas = document.getElementById('stock-chart');
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: data.name ? data.name : "データ",
                data: data.values,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)'
            }]
        }
    });
}

createChart();