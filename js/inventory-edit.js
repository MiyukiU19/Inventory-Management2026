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


// 編集
const borderLine = document.querySelector("#border-line");
const containerWeight = document.querySelector("#container-weight");
const serialId = document.querySelector("#serial-id");
const amount = document.querySelector("#amount");
const inventoryEditBtn = document.querySelector("#inventory-edit-btn");
const displayItems = [
    borderLine,
    containerWeight,
    serialId,
    amount
];

// 在庫編集ボタンが押されたとき
inventoryEditBtn.addEventListener("click", async () => {
    // ボタンと編集中の要素を非表示にする
    inventoryEditBtn.style.display = "none";
    displayItems.forEach(e => {
        e.parentElement.style.display = "none";
    });

    // 元のデータを編集用テキストボックスに入れる
    borderLineInput.value = borderLine.innerText;
    containerInput.value = containerWeight.innerText;
    amountInput.value = amount.innerText;

    // 編集用のテキストボックスなどを表示する
    editBtns.style.display = "block";
    editItems.forEach(e => {
        e.parentElement.style.display = "block";
    })

    // どの在庫とも接続されていないマットを取得
    let requestData = {
        action: "get_not_connected_mat"
    };

    let response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    });

    const notConnectData = await response.json();

    // 在庫idに紐づけられたマットidを取得
    requestData = {
        action: "get_connected_mat_by_id",
        stock_id: stockId
    }

    response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    });

    const connectData = await response.json();
    let option = document.createElement("option");
    option.value = connectData.mat.mat_id;
    option.innerText = connectData.mat.serial_id;
    option.selected = true;
    serialSelect.appendChild(option);

    notConnectData.mat.forEach((e) => {
        let option = document.createElement("option");
        option.value = e.mat_id;
        option.innerText = e.serial_id;

        serialSelect.appendChild(option);
    });
});

const borderLineInput = document.querySelector("#border-line-input");
const containerInput = document.querySelector("#container-input");
const serialSelect = document.querySelector("#serial-select");
const amountInput = document.querySelector("#amount-input");
// まとめて非表示にするための配列
const editItems = [
    borderLineInput,
    containerInput,
    serialSelect,
    amountInput
];

// 保存
const editBtns = document.querySelector("#edit-btns");
const confirmBtn = document.querySelector("#inventory-confirm-btn");
confirmBtn.addEventListener("click", async () => {
    let prevMatId = Number(document.querySelector("#hidden-mat-id").value);

    // PHPに送信(データベース編集)
    const newBorder = borderLineInput.value;
    const newContainerWeight = containerInput.value;
    const newMatId = serialSelect.value;
    const newAmount = amountInput.value;
    const requestData = {
        action: "set_update_inventory",
        stockId: stockId,
        borderLine: newBorder,
        containerWeight: newContainerWeight,
        prevMatId: prevMatId,
        newMatId: newMatId,
        amount: newAmount
    };

    const response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    });

    // 元の情報を書き換え、再表示
    const data = await response.json();
    if(data.status == "success"){
        editBtns.style.display = "none";
        editItems.forEach(e => {
            e.parentElement.style.display = "none";
        });

        borderLine.innerText = borderLineInput.value;
        serialId.innerText = serialSelect.options[serialSelect.selectedIndex].text;
        containerWeight.innerText = containerInput.value;
        prevMatId.innerText = newMatId;
        amount.innerText = newAmount;

        inventoryEditBtn.style.display = "block";
        displayItems.forEach(e => {
            e.parentElement.style.display = "block";
        })
    }
})

// キャンセル
// const cancelBtn =document.createElement("#inventory-cancel-btn");