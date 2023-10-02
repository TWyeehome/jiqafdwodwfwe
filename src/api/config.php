<?php
switch ($_SERVER['SERVER_NAME']) {
    case 'localhost':
        $test = true; // 測試
        break;
    default:
    $test = false;
};

if ($test == true) {
    // 測試
    $hashKey = 'p2aDaZxWSZfx3weI'; // pwFHCqoQZGmho4w6
    $hashiv = 'GukfyCOrgSc39ok6'; // EkRm7iFT261dpevs
    $merchantid = '3085676'; // 3002607
    $ecpay_url = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';
    $pay_check = 'https://payment-stage.ecpay.com.tw/Cashier/QueryTradeInfo/V5';
    // 發票
    $invoice_merchantid = '2000132';
    $invoice_hashKey = 'ejCk326UnaZWKisg';
    $invoice_hashiv = 'q9jcZX8Ib9LM8wYk';
    $invoice_url = 'https://einvoice-stage.ecpay.com.tw/B2CInvoice/Issue';
    /* 票券 */
    // 發行
    $ticket_issue = 'https://ecticket-stage.ecpay.com.tw/api/Ticket/Issue';
    $ticket_key = '7b53896b742849d3';
    $ticket_hiv = '37a0ad3c6ffa428b';
} else {
    // 正式
    $hashKey = 'NwPdCf1KlRNxEdop';
    $hashiv = '2nLKVbMXw08OYh7b';
    $merchantid = '3373220';
    $ecpay_url = 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5';
    $pay_check = 'https://payment.ecpay.com.tw/Cashier/QueryTradeInfo/V5';
    /* 票券 */
    // 發行
    $ticket_issue = 'https://ecticket.ecpay.com.tw/api/Ticket/Issue';
    $ticket_key = 'f629eece3cca4283';
    $ticket_hiv = '078802cacc3b4c63';
};
