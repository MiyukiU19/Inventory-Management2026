// 【追加点】api.php にJSONをPOSTして結果を返す共通関数(同じfetchの繰り返しをまとめた)
async function postApi(data) {
    const response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

let myChart = null;
async function createLineChart(canvas, year, month, stockId){
    const requestGraphData = {
        action: "get_graph_data",
        stock: stockId,
        year: year,
        month: month
    };

    const responseGraphData = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestGraphData)
    });

    const graphData = await responseGraphData.json();

    const requestBorderData = {
        action: "get_border_line",
        stock_id: stockId
    };

    const responseBorder = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestBorderData)
    });

    const borderData = await responseBorder.json();
    if (myChart) {
        myChart.destroy();
    }

    // グラフの線の「色」のリストを用意しておく（商品が増えても対応できるように）
    const color = {
        border: 'rgba(54, 162, 235, 1)', bg: 'rgba(54, 162, 235, 0.2)'
    }

    // PHPから届いた商品のデータを、Chart.js用の配列に変換する
    const chartDataset = [];

    const chartData = graphData.datasets[0];
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
            labels: graphData.labels,
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
                },
                // 【ここを追加】基準線（アノテーション）の設定
                annotation: {
                    annotations: {
                        // 任意の名前（line1 などでOK）
                        line1: {
                            type: 'line',
                            yMin: borderData.border_line, // 縦軸（Y軸）のどの位置に線を引くか（※ここを商品の基準値の数値にする）
                            yMax: borderData.border_line, // yMinと同じ数値を指定すると水平な線になります
                            borderColor: 'rgba(255, 99, 132, 0.8)', // 線の色（赤系）
                            borderWidth: 2, // 線の太さ
                            borderDash: [6, 6], // 点線にしたい場合（実線ならこの行を消す）

                            // 線のそばに「基準値：50」などのラベルを表示したい場合
                            label: {
                                display: true,
                                content: `基準値: ${borderData.border_line}`,
                                position: 'end', // ラベルの位置（'start', 'center', 'end'）
                                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                                color: '#fff',
                                font: {
                                    size: 10
                                }
                            }
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


// ===== 編集用要素の定義 =====
// 【修正点】編集用の要素(Input類)の定義を、使う前の位置にまとめた
// 表示用(普段見えている方)
const borderLine = document.querySelector("#border-line");
const containerWeight = document.querySelector("#container-weight");
const serialId = document.querySelector("#serial-id");
const amount = document.querySelector("#amount");
const inventoryEditBtn = document.querySelector("#inventory-edit-btn");
const weight = document.querySelector("#weight");
const categoryName = document.querySelector("#category-name");

const displayItems = [
    borderLine,
    containerWeight,
    serialId,
    amount,
    categoryName,
    weight
];

// 編集用(編集ボタンを押すと見える方)
const borderLineInput = document.querySelector("#border-line-input");
const containerInput = document.querySelector("#container-input");
const serialSelect = document.querySelector("#serial-select");
const amountInput = document.querySelector("#amount-input");
const categorySelect = document.querySelector("#category-select");
const weightInput = document.querySelector("#weight-input");

const editItems = [
    borderLineInput,
    containerInput,
    serialSelect,
    amountInput,
    categorySelect,
    weightInput
];

const editBtns = document.querySelector("#edit-btns");
const confirmBtn = document.querySelector("#inventory-confirm-btn");
const cancelBtn = document.querySelector("#inventory-cancel-btn");
// 【追加点】保存後に更新するため、マットIDの要素を定義
const hiddenMatId = document.querySelector("#hidden-mat-id");

// 【追加点】表示モードにする(編集用を隠して、表示用を出す)。保存とキャンセルで共通して使う
function showDisplayMode() {
    editBtns.style.display = "none";
    editItems.forEach(e => {
        e.parentElement.style.display = "none";
    });

    displayItems.forEach(e => {
        e.parentElement.style.display = "block";
    });
    inventoryEditBtn.style.display = "block";
}

// ===== 在庫編集ボタンが押されたとき =====
inventoryEditBtn.addEventListener("click", async () => {
    // ボタンと表示用の要素を非表示にする
    inventoryEditBtn.style.display = "none";
    displayItems.forEach(e => {
        e.parentElement.style.display = "none";
    });

    // 元のデータを編集用テキストボックスに入れる
    borderLineInput.value = borderLine.innerText;
    containerInput.value = containerWeight.innerText;
    amountInput.value = amount.innerText;
    weightInput.value = weight.innerText;

    // 【追加点】カテゴリーは、保存済みのカテゴリーを選択状態に戻す
    categorySelect.value = categoryName.dataset.categoryId;

    // 編集用のテキストボックスなどを表示する
    editBtns.style.display = "block";
    editItems.forEach(e => {
        e.parentElement.style.display = "block";
    });

    // 【修正点】マットの選択肢を作り直す(押すたびに選択肢が重複して増えていたので、最初に空にする)
    serialSelect.innerHTML = "";

    // どの在庫とも接続されていないマットを取得
    const notConnectData = await postApi({
        action: "get_not_connected_mat"
    });

    // 在庫idに紐づけられたマットを取得
    const connectData = await postApi({
        action: "get_connected_mat_by_id",
        stock_id: stockId
    });

    // 【追加点】「未連携」の選択肢
    const noneOption = document.createElement("option");
    noneOption.value = "";
    noneOption.innerText = "未連携";
    serialSelect.appendChild(noneOption);

    // 【修正点】マットが未連携でもエラーにならないようにした(以前は connectData.mat.mat_id で止まっていた)
    if (connectData.mat) {
        const option = document.createElement("option");
        option.value = connectData.mat.mat_id;
        option.innerText = connectData.mat.serial_id; // 【修正点】「(identification_id)」を削除
        option.selected = true;
        serialSelect.appendChild(option);
    } else {
        noneOption.selected = true;
    }

    // 未連携のマット
    notConnectData.mat.forEach((e) => {
        const option = document.createElement("option");
        option.value = e.mat_id;
        option.innerText = e.serial_id; // 【修正点】「(identification_id)」を削除
        serialSelect.appendChild(option);
    });
});

// ===== 保存 =====
confirmBtn.addEventListener("click", async () => {
    const prevMatId = Number(hiddenMatId.value);

    // 入力値を取得
    const newBorder = borderLineInput.value.trim();
    const newContainerWeight = containerInput.value.trim();
    const newAmount = amountInput.value.trim();
    const newWeight = weightInput.value.trim();
    const newMatId = serialSelect.value;
    const newCategory = categorySelect.value;

    // 【追加点】数値チェック
    const numbers = [newBorder, newContainerWeight, newAmount, newWeight];
    if (numbers.some(v => v === "" || isNaN(Number(v)))) {
        alert("数値を正しく入力してください。");
        return;
    }

    // 【修正点】try/catch を追加し、通信に失敗してもエラーで止まらないようにした
    // PHPに送信(データベース編集)
    let data;
    try {
        data = await postApi({
            action: "set_update_inventory",
            stockId: stockId,
            borderLine: newBorder,
            containerWeight: newContainerWeight,
            prevMatId: prevMatId,
            newMatId: newMatId,
            amount: newAmount,
            categoryId: newCategory, // 【追加点】カテゴリーを送信
            weight: newWeight // 【追加点】1個あたりの重さを送信
        });
    } catch (e) {
        alert("保存に失敗しました。");
        return;
    }

    // 【追加点】保存に失敗した場合はメッセージを出して、画面はそのままにする
    if (data.status !== "success") {
        alert(data.message || "保存に失敗しました。");
        return;
    }

    // 元の情報を書き換える
    borderLine.innerText = newBorder;
    containerWeight.innerText = newContainerWeight;
    amount.innerText = newAmount;
    weight.innerText = newWeight;
    serialId.innerText = serialSelect.options[serialSelect.selectedIndex].text;
    categoryName.innerText = categorySelect.options[categorySelect.selectedIndex].text;
    // 【追加点】保存したカテゴリーIDを覚えておく(次の編集やキャンセルで使う)
    categoryName.dataset.categoryId = newCategory;

    // 【修正点】次に保存するときの「元のマット」を、今保存したマットにしておく
    // (以前の prevMatId.innerText = newMatId は数値への代入で効かず、2回目の保存でマットの連携が壊れていた)
    hiddenMatId.value = newMatId;

    // 【追加点】次回の編集で選択肢を取り直すため空にする
    serialSelect.innerHTML = "";

    // 【修正点】表示を元に戻す処理を showDisplayMode にまとめた
    // (以前は途中でエラーが出ると、値が全部消えて編集ボタンも出なくなっていた)
    showDisplayMode();

    // 基準値が変わった場合に備えてグラフを再描画
    createLineChart(stockChart, selectedYear, selectedMonth.innerText, stockId);
});

// ===== キャンセル =====
cancelBtn.addEventListener("click", () => {
    // 【修正点】表示を元に戻す処理を showDisplayMode にまとめた
    serialSelect.innerHTML = "";
    showDisplayMode();
});