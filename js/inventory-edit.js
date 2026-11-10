let myChart = null;
async function createLineChart(canvas, year, month, stockId){
    const requestData = {
        action: "get_graph_data",
        stock: stockId,
        year: year,
        month: month
    };

    const response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    });

    const data = await response.json();

    if (myChart) {
        myChart.destroy();
    }

    // グラフの線の「色」のリストを用意しておく（商品が増えても対応できるように）
    const color = {
        border: 'rgba(54, 162, 235, 1)', bg: 'rgba(54, 162, 235, 0.2)'
    }

    // PHPから届いた商品のデータを、Chart.js用の配列に変換する
    const chartDataset = [];

    const chartData = data.datasets[0];
    console.log(chartData);
    chartDataset.push({
        label: chartData.label,
        data: chartData.data,
        borderColor: color.border,
        backgroundColor: color.bg,
        pointHitRadius: 20,
        spanGaps: true, // 空白の日を繋ぐ
    });

    myChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: chartDataset // まとめて作った配列を渡す
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    align: "start",
                    labels: {
                        // filter機能を使って、ラベルを非表示にする
                        filter: function(item, chart) {
                            // item.datasetIndex で、対象のデータセットの番号がわかる
                            return false;
                        }
                    },
                    title: {
                        display: true,
                        text: '       (個)', // スペースがないと見切れます。対処法が見つかったら編集します。
                        align: 'start', // 左端に寄せる
                        color: '#666',
                        font: {
                            size: 12,
                            weight: 'normal' // デフォルトの太字を解除する
                        }
                    }
                }
            },
            // scales（目盛り）の設定に title を追加する
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });
}

createLineChart(stockChart, selectedYear, selectedMonth.innerText, stockId);

prevMonthBtn.addEventListener("click", () => {
    let month = selectedMonth.innerText;
    month--;
    if(month == 0){
        month = 12;
        selectedYear--;
    }

    selectedMonth.innerText = month;
    createLineChart(stockChart, selectedYear, month, stockId)
});

nextMonthBtn.addEventListener("click", () => {
    let month = selectedMonth.innerText;
    month++;
    if(month == 13){
        month = 1;
        selectedYear++;
    }

    selectedMonth.innerText = month;
    createLineChart(stockChart, selectedYear, month, stockId)
})