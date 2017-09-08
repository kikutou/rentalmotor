$(function() {
    $('[id^=remise-job-]').each(function() {
        $(this).click(function(event) {
            var aid = $(this).prop("id");
            if (aid == "remise-job-sales") {
                extsetSales(event);
            } else if (aid == "remise-job-change") {
                extsetChange(event);
            } else if (aid == "remise-job-return") {
                extsetReturn(event);
            }
        });
    });
});

function extsetSales(event) {
    if ($('#remise-payment-total-change').val() != "0") {
        event.preventDefault();
        alert("金額が変更されています。\n「金額変更を行う」後に再実行してください。");
        return false;
    }
    var msg = "売上処理を行います。よろしいですか？";
    if ($('#remise-point-plugin').val() == "1") {
        msg += "\n\n【注意事項】"
             + "\n売上を行うことで「入金済み」となりますが、ポイントは確定されません。"
             + "\nポイントの確定タイミングを「入金済み」にされている場合は、ポイントの確定タイミングを変更するか、編集画面で再度更新処理をお願いいたします。";
    }
    if (!confirm(msg)) {
        event.preventDefault();
        return false;
    }
    $('#remise-extset-job').val("SALES");
    extset_waitscreen();
}
function extsetChange(event) {
    if ($('#remise-payment-total-change').val() == "0") {
        event.preventDefault();
        alert("金額は変更されていません。\n計算結果の更新後、再実行してください。");
        return false;
    }
    var msg = "金額変更を行います。よろしいですか？\n※編集画面で変更された各設定値は更新されます。";
    if (!confirm(msg)) {
        event.preventDefault();
        return false;
    }
    $('#remise-extset-job').val("CHANGE");
    extset_waitscreen();
}
function extsetReturn(event) {
    var msg = "キャンセルを行います。よろしいですか？";
    if (!confirm(msg)) {
        event.preventDefault();
        return false;
    }
    $('#remise-extset-job').val("RETURN");
    extset_waitscreen();
}

function extset_waitscreen() {
    $('#dialog-area').appendTo('body');
    var maskHeight = $(document).height();
    var maskWidth = $(window).width();
    var dialogTop = ($(window).height()/2) - ($('#dialog-box').height());
    var dialogLeft = (maskWidth/2) - ($('#dialog-box').width()/2);
    $('#dialog-overlay').css({height:maskHeight, width:maskWidth, zIndex:9998}).show();
    $('#dialog-box').css({top:dialogTop, left:dialogLeft, zIndex:9999}).show();
    $('#dialog-message').html();
    scroll(0,0);
}
