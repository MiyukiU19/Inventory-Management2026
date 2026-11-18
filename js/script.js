cancelMatAddBtn = document.querySelector("#cancel-mat-add");
cancelMatAddBtn.addEventListener("click", () => {
    let result = window.confirm("マット一覧に戻りますか？");
    if (!result){
        // はいが押されたら移動する
        window.location.href='mat-add.php';
    }
});

// mat-editで更新ボタンが押さたときと削除ボタンが押されたときで処理を変える。
const matEditForm = document.querySelector("#mat-edit-form");
matEditForm.addEventListener("submit", e => {
    const submitter = e.submitter;
    e.preventDefault(); // formの送信をキャンセル

    // 押されたボタンによって送信する項目を変える。
    let action = "";
    if(submitter.id == "mat-delete"){
        action = "delete";
    } else if(submitter.id == "mat-edit"){
        action = "edit";
    }

    let hidden = document.createElement("input");
    hidden.type = "hidden";
    hidden.name = "action";
    hidden.value = action;
    matEditForm.appendChild(hidden);

    // formを送信する。
    matEditForm.submit();
});

// 三桁ごとにカンマを付ける
function formatNumberKeepDecimals(num) {
    // 文字列に変換して小数点で分割
    const parts = String(num).split('.');

    // 整数部分に正規表現で3桁ごとのカンマを挿入
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // 小数部分があれば結合して返す
    return parts.join('.');
}

// 三桁ごとのカンマを取り除く(第二引数がtrueなら数値型で返す)
function removeCommas(str, asNumber = false) {
    // すべてのカンマを空文字に置換
    const cleanStr = String(str).replaceAll(',', '');

    // 第2引数がtrueなら数値型に変換して返す
    return asNumber ? Number(cleanStr) : cleanStr;
}