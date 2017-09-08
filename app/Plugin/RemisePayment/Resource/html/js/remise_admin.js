$(function(){
{% if arrForm.payment_type == config.remise_payment_credit %}
{%   if arrForm.payment_job == app['config']['state_auth']
     or arrForm.payment_job == app['config']['state_sales']
     or arrForm.payment_job == app['config']['state_capture']
%}
    $('#order_OrderStatus').change(function() {
        if ($('#order_OrderStatus').val() == {{ app['config']['order_cancel'] }}) {
            alert("こちらの注文をキャンセルする場合は、クレジットカード決済のキャンセル処理を行ってください。\nクレジットカード決済のキャンセル処理は自動では行われません。");
        }
    });

    $('#order_Payment').change(function() {
        if ($('#order_Payment').val() != $('#remise-order-payment').val()) {
            alert("お支払方法を変更する場合は、クレジットカード決済のキャンセル処理を行ってください。\nクレジットカード決済のキャンセル処理は自動では行われません。");
        }
    });
{%   endif %}
{% endif %}
});
