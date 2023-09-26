<?php
/* 串接 Mysql */
require_once('./api/php_connect_mysql.php');
// 產生亂碼
function random_string($length = 32, $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    if (!is_int($length) || $length < 0) {
        return false;
    };
    $characters_length = strlen($characters) - 1;
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, $characters_length)];
    };
    return $string;
};
// 一般產生訂單 (全功能) 範例
// 載入設定檔
require_once('./api/config.php');
// 載入 SDK (路徑可依系統規劃自行調整)
require_once('./api/ECPay.Payment.Integration.php');
// 
try {
    // 測試機
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        $return_url = 'http://localhost/eslite/modernguru/pay_done.php?trade_no=';
    } else if ($_SERVER['SERVER_NAME'] === 'shareablecorp.com') {
        // 正式機
        $return_url = 'https://shareablecorp.com/eslite/modernguru/pay_done.php?trade_no=';
    } else {
        // 正式機
        $return_url = 'https://amping.io/eslite/modernguru/pay_done.php?trade_no=';
    };
    /* 官方文件: https://www.ecpay.com.tw/CascadeFAQ/CascadeFAQ_Qa?nID=3044 */
    $obj = new ECPay_AllInOne();
    // 測式: https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5
    // 正式: https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5
    // 服務位置
    $obj->ServiceURL = $ecpay_url;
    $obj->HashKey = $hashKey;
    $obj->HashIV = $hashiv;
    $obj->MerchantID = $merchantid; // ECPay 提供的商店代號                     
    $obj->EncryptType = '1'; // CheckMacValue 加密類型，請固定填入 1，使用 SHA256 加密                                              
    // 訂單編號
    $MerchantTradeNo = @$_GET['trade_no']; // 'bs' . date('Y') . date('m') . date('d') . date('H') . date('i') . random_string(3); // 項目_時間_亂碼 PS: 字串長度不得超過 20 
    // 消費者付款完成後，綠界科技會以 Server POST (背景接收) 方式傳送付款結果參數到商家的 Server
    $obj->Send['ReturnURL']  = $return_url . @$_GET['trade_no'];
    // 付款完成後倒轉的網址
    $obj->Send['OrderResultURL'] = $return_url . @$_GET['trade_no'];
    // 訂單編號  
    $obj->Send['MerchantTradeNo'] = $MerchantTradeNo;
    // 交易時間                         
    $obj->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');
    // 票券價格
    $test_price = 20; // 測試票
    $discount_price = 300; // 超級早鳥票
    $early_price = 300; // 早鳥票
    $normal_price = 360; // 全票
    $family_price = 600; // 親子套票
    // 若有填誠品會員卡號
    if (@$_GET['esliteCode']) {
        // 綠界備註做紀錄
        $obj->Send['CustomField4'] = $_GET['esliteCode'];
    };
    // 檢查折扣碼
    $coupon_used = 0;
    if (@$_GET['coupon']) {
        // 綠戒備註做紀錄
        $obj->Send['CustomField3'] = $_GET['coupon'];
        // 
        $coupon = $db->query("SELECT code, discount FROM coupon WHERE code='" . $_GET['coupon'] . "'");
        if (!$coupon->num_rows) {
            $coupon_used = 0;
        } else {
            $coupon_row = $coupon->fetch_assoc();
            $coupon_used = 1;
            // 9 折
            if ($coupon_row['discount'] == 0.9) {
                $test_price = 18; // 測試票
                $normal_price = 324; // 全票
            };
            // 85 折
            if ($coupon_row['discount'] == 0.85) {
                $test_price = 17; // 測試票
                $normal_price = 306; // 全票
            };
            // 8 折
            if ($coupon_row['discount'] == 0.8) {
                $test_price = 16; // 測試票
                $normal_price = 288; // 全票
            };
        };
    };
    // 交易類型
    $obj->Send['PaymentType'] = 'aio';
    $obj->Send['ClientBackURL'] = 'https://amping.io/eslite/modernguru/index.php'; // Client 端返回特店的按鈕連結
    $obj->Send['StoreID'] = 'eslite'; // 店鋪代號
    // 若有填公司抬頭
    $notes = '';
    if (@$_GET['companyName'] != '' and @$_GET['companyId'] != '') {
        $notes = '抬頭: ' . @$_GET['companyName'] . ', 統編: ' .  @$_GET['companyId'];
        $obj->Send['CustomField1'] = @$_GET['companyName'];
        $obj->Send['CustomField2'] =  @$_GET['companyId'];
    };
    $obj->Send['Remark'] = '誠品_澳洲3D'; // 備註
    // 交易總金額 5~199999 info: https://www.ecpay.com.tw/Announcement/DetailAnnouncement?nID=4211  
    $obj->Send['TotalAmount'] =
        (intval(@$_GET['ticketTest']) * $test_price + // 測試票
            intval(@$_GET['ticketDiscount']) * $discount_price + // 超級早鳥票
            intval(@$_GET['ticketEarly']) * $early_price + // 早鳥票
            intval(@$_GET['ticketNormal']) * $normal_price + // 全票
            intval(@$_GET['ticketFamily']) * $family_price // 親子套票
        );
    // 交易描述                         
    if (!$_SERVER['SERVER_NAME'] === 'localhost') {
        $obj->Send['TradeDesc'] = '測試用途';
    } else {
        $obj->Send['TradeDesc'] = '正式用途';
    };
    // 付款方式:全功能                      
    $obj->Send['ChoosePayment'] = 'Credit'; // https://developers.ecpay.com.tw/?p=2862                
    // 訂單的商品資料
    array_push(
        $obj->Send['Items'],
        /*[
            // 品名
            'Name' => '測試票',
            // 單價
            'Price' => (int)'20',
            // 計價單位
            'Currency' => '元',
            // 數量
            'Quantity' => (int) @$_GET['ticketTest'],
            'URL' => 'https://amping.io/eslite/modernguru/index.php'
        ],*/
        [
            // 品名
            'Name' => '超級早鳥票',
            // 單價
            'Price' => (int)'300',
            // 計價單位
            'Currency' => '元',
            // 數量
            'Quantity' => (int) @$_GET['ticketDiscount'],
            'URL' => 'https://amping.io/eslite/modernguru/index.php'
        ],
        [
            // 品名
            'Name' => '早鳥票',
            // 單價
            'Price' => (int)'300', // 250
            // 計價單位
            'Currency' => '元',
            // 數量
            'Quantity' => (int) @$_GET['ticketEarly'],
            'URL' => 'https://amping.io/eslite/modernguru/index.php'
        ],
        [
            // 品名
            'Name' => '全票',
            // 單價
            'Price' => (int)'360',
            // 計價單位
            'Currency' => '元',
            // 數量
            'Quantity' => (int) @$_GET['ticketNormal'],
            'URL' => 'https://amping.io/eslite/modernguru/index.php'
        ],
        [
            // 品名
            'Name' => '親子套票',
            // 單價
            'Price' => (int)'600',
            // 計價單位
            'Currency' => '元',
            // 數量
            'Quantity' => (int) @$_GET['ticketFamily'],
            'URL' => 'https://amping.io/eslite/modernguru/index.php'
        ]
    );
    // 折扣碼
    if ($coupon_used) {
        if ($coupon_row['discount'] != 1) {
            $discount = 0;
            if ($coupon_row['discount'] == 0.95) {
                $discount = 95;
            };
            if ($coupon_row['discount'] == 0.9) {
                $discount = 9;
            };
            if ($coupon_row['discount'] == 0.85) {
                $discount = 85;
            };
            if ($coupon_row['discount'] == 0.8) {
                $discount = 8;
            };
            array_push(
                $obj->Send['Items'],
                [
                    // 品名
                    'Name' => '折扣碼【' . $coupon_row['code'] . '】',
                    // 單價
                    'Price' => (int)$discount,
                    // 計價單位
                    'Currency' => '折',
                    // 數量
                    'Quantity' => (int)1,
                    'URL' => 'https://amping.io/eslite/modernguru/index.php'
                ]
            );
        };
    };
    // 產生訂單 (auto submit 至 ECPay)
    $obj->CheckOut();
} catch (Exception $e) {
    echo $e->getMessage();
};
