cancelMatAddBtn = document.querySelector("#cancel-mat-add");
cancelMatAddBtn.addEventListener("click", () => {
    let result = window.confirm("マット一覧に戻りますか？");
    if (!result){
        // はいが押されたら移動する
        window.location.href='mat-add.php';
    }
})