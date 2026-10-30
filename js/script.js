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