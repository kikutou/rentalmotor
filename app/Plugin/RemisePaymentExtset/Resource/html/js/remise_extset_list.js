$(function() {
    $('#credit-check-all').click(function() {
        var checkall = $('#credit-check-all').prop('checked');
        if (checkall) {
            $('input[id^=credit-check-]').prop('checked', true);
        } else {
            $('input[id^=credit-check-]').prop('checked', false);
        }
    });

    $('#selectCheckCreditSalesSubmit').click(function(event) {
        event.preventDefault();
        var href = $(this).attr('href');

        var chkCount = 0;
        var orderIds = '';
        $('input[id^=credit-check-]').each(function() {
            if ($(this).prop("id") != "credit-check-all") {
                if ($(this).prop("checked") == true) {
                    chkCount++;
                    if (orderIds.length != 0) orderIds += ",";
                    orderIds += $(this).val();
                }
            }
        });
        if (chkCount == 0) {
            alert("チェックボックスが選択されていません");
            return false;
        }

        var msg = "選択された" + chkCount + "件の売上処理を行います。よろしいですか？";
        if ($('#point-plugin').val() == "1") {
            msg += "\n\n【注意事項】"
                 + "\n売上を行うことで「入金済み」となりますが、ポイントは確定されません。"
                 + "\nポイントの確定タイミングを「入金済み」にされている場合は、ポイントの確定タイミングを変更するか、編集画面で再度更新処理をお願いいたします。";
        }
        if (!confirm(msg)) return false;

        extset_waitscreen();
        $('#credit-orders').val(orderIds);
        $('#remise_form').submit();
    });
});

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
