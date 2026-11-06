// 選択されている在庫
let selectedStock = null;

// 期間
const chartPeriod = document.querySelector("#chart-period");
let period = chartPeriod.value.split(" ");
let selectedYear = period[0];
let selectedMonth = period[1];

// グラフ
let myChart = null;

// グラフを作成し表示する
async function createChart() {
    // 【変更】Choices.jsなどで選ばれた複数の値を配列として用意します
    // 実際の画面では、UIから選択された値を取得してここに入れます
    const requestData = {
        action: "get_graph_data",
        stocks: selectedStock, // 複数のstock_idを配列として送る
        year: selectedYear,
        month: selectedMonth
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

    // グラフを描く瞬間のチェックボックスの状態を見ておく
    const isCompare = document.getElementById('check-last-year').checked;

    // グラフの線の「色」のリストを用意しておく（商品が増えても対応できるように）
    const colors = [
        { border: 'rgba(54, 162, 235, 1)', bg: 'rgba(54, 162, 235, 0.2)' }, // 青
        { border: 'rgba(255, 99, 132, 1)', bg: 'rgba(255, 99, 132, 0.2)' }, // 赤
        { border: 'rgba(75, 192, 192, 1)', bg: 'rgba(75, 192, 192, 0.2)' }, // 緑
        { border: 'rgba(255, 159, 64, 1)', bg: 'rgba(255, 159, 64, 0.2)' }  // オレンジ
    ];

    // 【変更】PHPから届いた複数商品のデータを、Chart.js用の配列に変換する
    const chartDatasets = [];

    // data.datasets にはPHPから送られた複数の商品データが入っています
    data.datasets.forEach((item, index) => {
        const color = colors[index % colors.length];

        chartDatasets.push({
            label: item.name,
            data: item.values,
            borderColor: color.border,
            backgroundColor: color.bg,
            pointHitRadius: 20,
            spanGaps: true, // 空白の日を繋ぐ
        });

        // 前年のデータ（常に存在している）
        chartDatasets.push({
            label: item.name + '（前年）',
            data: item.last_year_values,
            borderColor: color.border,
            backgroundColor: color.bg,
            borderDash: [5, 5],
            pointHitRadius: 20,
            spanGaps: true,

            // 【重要】チェックボックスがOFFなら最初から隠しておく
            hidden: !isCompare,

            // 【重要】あとで切り替えるときに見つけやすいよう、オリジナルの目印をつけておく
            isLastYear: true
        });
    });

    const canvas = document.getElementById('stock-chart');
    myChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: chartDatasets // まとめて作った配列を渡す
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    labels: {
                        // 前年のラベルを表示しない。
                        // filter機能を使って、表示するラベルを選別する
                        filter: function(item, chart) {
                            // item.datasetIndex で、対象のデータセットの番号がわかる
                            const dataset = chart.datasets[item.datasetIndex];

                            // isLastYearが「ついていない」ものだけを表示（true）にする
                            return !dataset.isLastYear;
                        }
                    },
                    title: {
                        // 
                        display: true,
                        text: '(個)',
                        align: 'start', // 左端に寄せる
                        color: '#666',
                        font: {
                            size: 12,
                            weight: 'normal' // デフォルトの太字を解除する
                        }
                    }
                }
            },
            // 【変更】scales（目盛り）の設定に title を追加します
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });
}

// 在庫選択
const stockSelect = document.getElementById('stock-select');
stockSelect.addEventListener("change", () => {
    selectedStock = choices.getValue(true);
    createChart();
})

const choices = new Choices(stockSelect, {
    removeItemButton: true,  // 選択した項目に「×」ボタンを表示
    searchEnabled: true,     // 検索ボックスを表示する
    placeholder: true,
    placeholderValue: 'グラフに表示する商品を選択してください'
});

// 【修正点】 selectElementではなく、Choicesインスタンスから直接コンテナ要素を取得します
const choicesContainer = choices.containerOuter.element;

// mousedownイベントで検知
choicesContainer.addEventListener('mousedown', function(event) {
    // 選択されている項目の要素（青い背景の部分）を取得
    const selectedItem = event.target.closest('.choices__list--multiple .choices__item');

    if (selectedItem) {
        // もし元々の×ボタンを直接クリックした場合は、Choices標準の動作に任せるため処理を抜ける
        if (event.target.classList.contains('choices__button')) return;

        // 選択された項目の「値(value)」を取得
        const itemValue = selectedItem.getAttribute('data-value');

        if (itemValue) {
            // ChoicesのAPIを呼び出して、該当の値を直接削除
            choices.removeActiveItemsByValue(itemValue);

            // changeEventを強制的に発生させる。
            const changeEvent = new Event('change', { bubbles: true });
            stockSelect.dispatchEvent(changeEvent);
        }
    }
});

// 期間選択
chartPeriod.addEventListener("change", (e) => {
    console.log(e.target.value);
    period = e.target.value.split(" ");
    selectedYear = period[0];
    selectedMonth = period[1];

    createChart();
});

// 前年比
const checkLastYear = document.querySelector("#check-last-year");
checkLastYear.addEventListener("click", () => {
    if (!myChart) return; // まだグラフが出ていなければ何もしない。

    // 現在のチェック状態を取得
    const isCompare = document.getElementById('check-last-year').checked;

    // グラフのデータセットを1つずつ確認する
    myChart.data.datasets.forEach(dataset => {
        // さきほど付けた目印「isLastYear: true」を持っている線（前年の線）だけ設定を変える
        if (dataset.isLastYear === true) {
            // チェックが入っていれば hidden を false(表示) に、入っていなければ true(非表示) にする
            dataset.hidden = !isCompare;
        }
    });

    // グラフの見た目だけを再描画する（fetchしないので一瞬で終わる）
    myChart.update();
});