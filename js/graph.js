
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
                stepped: true,
                label: data.name ? data.name : "データ",
                data: data.values,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)'
            }]
        },
        options: {
            responsive: true,

            // 【ここを追加】マウス操作を大雑把でもOKにする魔法の設定
            interaction: {
                mode: 'index',
                intersect: false, // 点に直接触れていなくても反応させる
            },

            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

createChart();

const stockSelect = document.getElementById('stock-select');

const choices = new Choices(stockSelect, {
    // 必須級のオプション
    removeItemButton: true,  // 選択した項目に「×」ボタンを表示し、クリックで解除できるようにする

    // 便利なオプション
    searchEnabled: true,     // 選択肢が多い場合に検索ボックスを表示する
    placeholder: true,
    placeholderValue: 'グラフに表示する商品を選択してください', // 何も選択されていない時のテキスト
});