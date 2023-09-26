<?php
/* 綠界查詢訂單範例 */
/* 串接 Mysql */
require_once('./php_connect_mysql.php');
/* 取得 ip 位置 */
require_once('./get_ip.php');
include('./config.php'); // 載入設定檔
include('./ECPay.Payment.Integration.php'); // 載入SDK (路徑可依系統規劃自行調整)
// 檢查是否有此訂單
$result = $db->query("SELECT COUNT(*) AS have FROM invoice WHERE order_code='" . $_POST['tradeNo'] . "'");
$row = mysqli_fetch_assoc($result);
$have = $row['have'];
if (!$have) {
    $json = ['result' => 'fail', 'message' => '查無此訂單'];
    exit(json_encode($json));
};
// call API
try {
    $obj = new ECPay_AllInOne();
    // 官方文件位置: https://developers.ecpay.com.tw/?p=2890
    // 服務參數
    // 正式: https://payment.ecpay.com.tw/Cashier/QueryTradeInfo/V5
    // 測試: https://payment-stage.ecpay.com.tw/Cashier/QueryTradeInfo/V5
    $obj->ServiceURL = $pay_check; //服務位置
    $obj->MerchantID = $merchantid;
    $obj->HashKey = $hashKey;
    $obj->HashIV = $hashiv;
    $obj->EncryptType = '1'; // CheckMacValue 加密類型，請固定填入1，使用 SHA256 加密
    $obj->Query['MerchantTradeNo'] = @$_POST['tradeNo']; // 訂單產生時傳送給綠界的特店交易編號
    $obj->Query['TimeStamp'] = time(); // 驗證時間
    // 查詢訂單
    $info = $obj->QueryTradeInfo();
    // 顯示訂單資訊
    if ($info['TradeStatus'] == 1) {
        $db->query("UPDATE invoice SET 
        ecpay_order='" . $info['TradeNo'] . "',
        price='" . $info['TradeAmt'] . "', 
        trade='" . 'true' . "', 
        trade_time='" . $info['TradeDate'] . "', 
        pay_time='" . $info['PaymentDate'] . "', 
        ip='" . $ip . "' 
        WHERE order_code='" . $_POST['tradeNo'] . "'");
        // 通知信
        $invoice = $db->query("SELECT user_id FROM invoice WHERE order_code='" . $_POST['tradeNo'] . "'");
        $invoice_row = $invoice->fetch_assoc();
        $attend = $db->query("SELECT phone_number, sms_pass, email, email_pass FROM attend WHERE id='" . $invoice_row['user_id'] . "'");
        $attend_row = $attend->fetch_assoc();
        // 購票成功通知
        if ($_SERVER['SERVER_NAME'] != 'localhost') {
            // 發簡訊 (PS: 成本考量 23230814 開會決議先取消手機簡訊通知)
            /*if ($attend_row['sms_pass'] == 'true' and $attend_row['email_pass'] != 'true') {
                $curl = curl_init();
                $url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?';
                $url .= 'CharsetURL=UTF-8';
                // parameters
                $data = 'username=90451526SMS';
                $data .= '&password=share';
                $data .= '&dstaddr=' . $attend_row['phone_number']; // 收簡訊人的號碼
                $data .= '&smbody=' . '感謝您購買 "AI靈感大師:澳洲3D光影觸動樂園" ! 詳情及門票連結: https://amping.io/eslite/modernguru/index.php';
                // 設定curl網址
                curl_setopt($curl, CURLOPT_URL, $url);
                // 設定Header
                curl_setopt(
                    $curl,
                    CURLOPT_HTTPHEADER,
                    array("Content-type: application/x-www-form-urlencoded")
                );
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                // 執行
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 不顯示 curl_init 結果
                $output = curl_exec($curl);
                curl_close($curl);
                // 新增寄簡訊的 log msgid, phone_number, status_code, log
                $db->query("INSERT IGNORE INTO phone_log (phone_number, log) VALUES ('" . $attend_row['phone_number'] . "', '" . $output . "')");
            };*/
            // 發信
            //if ($attend_row['email_pass'] == 'true') {
            $url = 'https://api.elasticemail.com/v2/email/send';
            $content =
                '<p>親愛的顧客您好:</p>' .
                '<p>感謝您購買《AI靈感大師：澳洲3D光影觸動樂園》票券，本通知信僅為訂購紀錄，不得作為入場</p>' .
                '<p>憑證，查看完整訂單及票券(QRcode)請至票匣頁面確認(WALLET)，詳情:</p>' .
                '<p>https://amping.io/eslite/modernguru/index.php</p>' .
                '<p>【防詐騙提醒】</p>' .
                '<p>工作人員不會主動以電話、簡訊及Email等方式通知您誤刷分期，或要求您到ATM進行任何操</p>' .
                '<p>作。若您接到可疑電話，切勿聽信或提供任何資料，請立即掛斷並來信客服信箱</p>' .
                '<p>hello@amping.io 告訴我們，謝謝您的留意。</p>' .
                '<p>※本通知信為系統自動寄出，請勿直接回覆。</p><br>' .
                '<p>【購票注意事項】</p>' .
                '<p>•超級早鳥票：憑票現場可兌換獨家夜光海報乙張（隨機贈出）。</p>' .
                '<p>•所有票種憑票現場可加購獨家夜光海報（150元/張）。</p>' .
                '<p>•購票金額不累計誠品會員之會籍金額/點數。</p>' .
                '<p>•一人一票憑票入場（限一次使用），有效期限為展覽期間，展覽結束後恕不退換。</p>' .
                '<p>•票券如遺失、汙損、打洞、燒毀、翻拍或無法辨識等情形，視為無效票券，恕不接受退換補發，並無法持任何憑證要求入場。</p>' .
                '<p>•請勿購買來路不明之門票，主辦單位有權禁止任何經變造或偽造之票券持有者入場。</p>' .
                '<p>•免票資格：未滿 １ 歲幼童並由成人持票陪同入場。</p>' .
                '<p>•親子套票：限一位成人陪同一位兒童入場，不開放單獨入場，兒童身高限120cm(含)以下。</p>' .
                '<p>•購票即代表同意主辦單位、藝術團隊、策展團隊使用其肖像權。</p><br>' .
                '<p>【觀展注意事項】</p>' .
                '<p>•6歲(含)以下與120cm(含)以下兒童須成人陪同觀展。</p>' .
                '<p>•請維護自身及孩童安全，並嚴格遵守展區參觀規則。</p>' .
                '<p>•展區內禁止攜帶娃娃車、危險物品、長柄雨傘及寵物入內。</p>' .
                '<p>•展區內禁止飲食、抽菸、嚼食口香糖或檳榔，如經勸導後無意改善，主辦單位有權要求違規者離場。</p>' .
                '<p>•展區內禁止拍打展品、嬉戲追逐，如有損壞事宜，須照價賠償。</p>' .
                '<p>•展區內可拍照及錄影，但不得使用腳架及自拍棒。</p>' .
                '<p>•部分展區因安全考量，將另行規範體驗年齡與注意事項，展場規定依現場或官方粉絲團公告為主；上述事項若有未盡事宜，主辦單位保留修改、變更或取消活動及注意事項之解釋權利。</p>';
            try {
                $post = [
                    'from' => 'info@amping.io',
                    'fromName' => 'AMPING',
                    'apikey' => 'CC9B93B4F674BDDFB44AF903E607B86D0C0E110B07B1756448E915DACDF61B5043740BD2B3FCB948ED091F84CA7AF1BC',
                    'subject' => '感謝您購買《AI靈感大師：澳洲3D光影觸動樂園》票券!',
                    'to' => $attend_row['email'],
                    // 'msgBcc' => 'celiahsu@fansi.me', 
                    'bodyHtml' => $content,
                    // 'bodyText' => 'Text Body',
                    'isTransactional' => false
                ];
                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $post,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ));
                $output = curl_exec($ch);
                curl_close($ch);
                // 郵件發送成功
                // 新增 email log
                $db->query("INSERT IGNORE INTO email_log (email, status) VALUES ('" . $attend_row['email'] . "', '" . 'true' . "')");
            } catch (Exception $ex) {
                // 郵件發送失敗	
                // 新增 email log
                $db->query("INSERT IGNORE INTO email_log (email, status) VALUES ('" . $attend_row['email'] . "', '" . 'false' . "')");
            };
            //};
        };
        exit(json_encode($info));
    } else {
        $db->query("UPDATE invoice SET trade='" . 'false' . "', price='" . 0 . "', ip='" . $ip . "' WHERE order_code='" . $_POST['tradeNo'] . "'");
        exit(json_encode($info));
    };
    // HandlingCharge: 手續費
    // TradeAmt: 交易金額
    // TradeDate: 訂單成立時間
    // TradeStatus: 交易狀態  
} catch (Exception $e) {
    echo $e->getMessage();
};
