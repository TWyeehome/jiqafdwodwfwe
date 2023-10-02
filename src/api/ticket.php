<?php
header('Content-type:application/json; charset=utf-8');
// 檢查 api 必填參數
if (empty(@$_POST['type'])) {
    $json = ['result' => 'fail', 'message' => '缺少 type 參數'];
    exit(json_encode($json));
};
require_once('php_connect_mysql.php'); /* 串接 Mysql */
require_once('cookie_check.php'); // 檢查是否登入過
require_once('get_ip.php'); // 取 ip
require_once('random_code.php'); // 產亂數
require_once('get_today.php'); // 取得今天日期
//
switch (@$_POST['type']) {
    case 'issued_check': // 發行確認
        // 檢查會員是否存在
        $member_check = $db->query("SELECT id FROM attend WHERE member_code='" . $_COOKIE['member_code'] . "'");
        if (!$member_check->num_rows) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '無會員'];
            exit(json_encode($json));
        };
        $member_row = $member_check->fetch_assoc();
        // 檢查是否需要發行
        $issue = $db->query("SELECT * FROM invoice WHERE user_id='" . $member_row['id'] . "' AND ticket_order='" . '' . "' AND trade='" . 'true' . "' AND cancel!='" . 'true' . "'");
        if (!$issue->num_rows) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '無需檢查的發行'];
            exit(json_encode($json));
        } else {
            // 載入設定檔
            require_once('./config.php');
            // 發行
            require_once('./issue_ticket.php');
        };
        break;
    case 'pay_check': // 確認購買狀態
        // 檢查 api 必填參數
        if (empty(@$_POST['memberId'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺參數'];
            exit(json_encode($json));
        };
        // 載入設定檔
        include('./config.php');
        // 載入SDK (路徑可依系統規劃自行調整)
        include('./ECPay.Payment.Integration.php');
        $invoice = $db->query("SELECT * FROM invoice WHERE user_id='" . $_POST['memberId'] . "' AND trade='" . '' . "'");
        if (!$invoice->num_rows) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '無需檢查的購買'];
            exit(json_encode($json));
        };
        // 會員存在且登入過
        $content = [];
        while ($invoice_row = $invoice->fetch_assoc()) {
            // 呼叫綠界 api
            try {
                $obj = new ECPay_AllInOne();
                // 官方文件位置: https://developers.ecpay.com.tw/?p=2890
                // 服務參數
                $obj->ServiceURL = $pay_check; // 服務連結
                $obj->MerchantID = $merchantid;
                $obj->HashKey = $hashKey;
                $obj->HashIV = $hashiv;
                $obj->EncryptType = '1'; // CheckMacValue 加密類型，請固定填入1，使用 SHA256 加密
                $obj->Query['MerchantTradeNo'] = $invoice_row['order_code']; // 訂單產生時傳送給綠界的特店交易編號
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
                        WHERE order_code='" . $invoice_row['order_code'] . "'");
                } else {
                    $db->query("UPDATE invoice SET trade='" . 'false' . "', price='" . 0 . "', ip='" . $ip . "' WHERE order_code='" . $invoice_row['order_code'] . "'");
                };
                array_push($content, $info);
            } catch (Exception $e) {
                array_push($content, '綠界金流 API 錯誤');
            };
        };
        $json = [
            'content' => $content,
            'result' => 'success' // 成功取得所有票券購買結果
        ];
        exit(json_encode($json));
        break;
    case 'get_invoice':
        /* 取得所有票 */
        // 檢查 API 參數
        if (empty(@$_POST['memberCode'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '參數錯誤'];
            exit(json_encode($json));
        };
        // 檢查會員
        $member = $db->query("SELECT id, member_code, sms_pass, email_pass FROM attend WHERE member_code='" . $_POST['memberCode'] . "'");
        if (!$member->num_rows) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '查無對應之會員'];
            exit(json_encode($json));
        };
        $member_row = $member->fetch_assoc();
        if ($member_row['sms_pass'] != 'true' and $member_row['email_pass'] != 'true') {
            // 刪除登入 cookie
            setcookie('member_code', '');
            $json = ['result' => 'fail', 'message' => '會員尚未驗證'];
            $db->close();
            exit(json_encode($json));
        };
        // 發票  
        $invoice = $db->query("SELECT * FROM invoice WHERE user_id='" . $member_row['id'] . "' AND trade='" . 'true' . "' AND cancel!='" . 'true' . "'" . "" . " ORDER BY id DESC");
        $invoice_all = [];
        while ($invoice_row = $invoice->fetch_assoc()) {
            array_push($invoice_all, [
                'id' => $invoice_row['id'],
                'eslite_code' => $invoice_row['eslite_code'], // 誠品會員卡
                'order_code' => $invoice_row['order_code'],
                'ecpay_order' => $invoice_row['ecpay_order'],
                'ticket_test' => $invoice_row['ticket_test'],
                'ticket_discount' => $invoice_row['ticket_discount'],
                'ticket_early' => $invoice_row['ticket_early'],
                'ticket_normal' => $invoice_row['ticket_normal'],
                'ticket_family' => $invoice_row['ticket_family'],
                'coupon' => $invoice_row['coupon'],
                'company_name' => $invoice_row['company_name'],
                'company_id' => $invoice_row['company_id'],
                'price' => $invoice_row['price'],
                'pay_time' => $invoice_row['pay_time'],
            ]);
        };
        // 回傳 api
        $json = [
            'invoice' =>  $invoice_all,
            'result' => 'success' // 讀取成功
        ];
        $db->close();
        exit(json_encode($json));
        break;
    case 'get_ticket':
        /* 取得所有票 */
        // 檢查 API 參數
        if (empty(@$_POST['memberCode']) or empty(@$_POST['ticketType'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺少必要參數'];
            exit(json_encode($json));
        };
        // 檢查會員
        $member = $db->query("SELECT id, member_code, sms_pass, email_pass FROM attend WHERE member_code='" . $_POST['memberCode'] . "'");
        if (!$member->num_rows) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '查無對應之會員'];
            exit(json_encode($json));
        };
        $member_row = $member->fetch_assoc();
        if ($member_row['sms_pass'] != 'true' and $member_row['email_pass'] != 'true') {
            // 刪除登入 cookie
            setcookie('member_code', '');
            $json = ['result' => 'fail', 'message' => '會員尚未驗證'];
            exit(json_encode($json));
        };
        // 檢查 invoice   
        $invoice = $db->query("SELECT id FROM invoice WHERE " . $_POST['ticketType'] . "!='" . 0 . "' AND user_id='" . $member_row['id'] . "' AND trade='" . 'true' . "' AND cancel!='" . 'true' . "'");
        if (!$invoice->num_rows) {
            $json = ['result' => 'fail', 'message' => '無相關票券'];
            exit(json_encode($json));
        };
        $ticket_all = [];
        while ($invoice_row = $invoice->fetch_assoc()) {
            $ticket = $db->query("SELECT id, ticket_type, ticket_code, pay_time FROM ticket WHERE invoice_id='" . $invoice_row['id'] . "' AND user_id='" . $member_row['id'] . "' AND ticket_type='" . $_POST['ticketType'] . "' AND cancel!='" . 'true' . "' ORDER BY id DESC");
            $ticket_detail = [];
            while ($ticket_row = $ticket->fetch_assoc()) {
                array_push($ticket_detail, [
                    'id' => $ticket_row['id'],
                    'ticket_type' => $ticket_row['ticket_type'],
                    'ticket_code' => $ticket_row['ticket_code'],
                    'pay_time' => $ticket_row['pay_time']
                ]);
            };
            array_push($ticket_all, $ticket_detail);
        };
        // 回傳 api
        $json = [
            'content' => $ticket_all,
            'result' => 'success',
            'message' => '讀取成功'
        ];
        exit(json_encode($json));
        break;
    case 'get':
        /* 取得所有票 */
        // 檢查 API 參數
        if (empty(@$_POST['memberCode'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '參數錯誤'];
            exit(json_encode($json));
        };
        // 檢查會員
        $member = $db->query("SELECT id, member_code, sms_pass, email_pass FROM attend WHERE member_code='" . $_POST['memberCode'] . "'");
        if ($member->num_rows == 0) {
            $json = ['result' => 'fail', 'message' => '查無對應之會員'];
            exit(json_encode($json));
        };
        $member_row = $member->fetch_assoc();
        if ($member_row['sms_pass'] != 'true' and $member_row['email_pass'] != 'true') {
            // 刪除登入 cookie
            setcookie('member_code', '');
            $json = ['result' => 'fail', 'message' => '會員尚未驗證'];
            exit(json_encode($json));
        };
        // 檢查被取消的 invoice
        $invoice_cancel = $db->query("SELECT order_code FROM invoice WHERE user_id='" . $member_row['id'] . "' AND trade='" . 'true' . "' AND cancel='" . 'true' . "'");
        while ($cancel_row = $invoice_cancel->fetch_assoc()) {
            $db->query("UPDATE ticket SET cancel='" . 'true' . "' WHERE order_code='" . $cancel_row['order_code'] . "'");
        };
        // 檢查 invoice
        $invoice = $db->query("SELECT * FROM invoice WHERE user_id='" . $member_row['id'] . "' AND trade='" . 'true' . "' AND cancel!='" . 'true' . "'");
        // 測試票、超級早鳥、早鳥、全票、親子套票
        $ticket_type = ['ticket_test', 'ticket_discount', 'ticket_early', 'ticket_normal', 'ticket_family'];
        while ($invoice_row = $invoice->fetch_assoc()) {
            // $db->query("UPDATE ticket SET WHERE order_code='" . $invoice_row['order_code'] . "'");
            for ($x = 0; $x < count($ticket_type); $x++) {
                $ticket = $db->query("SELECT * FROM ticket WHERE ticket_type='" . $ticket_type[$x] . "' AND invoice_id='" . $invoice_row['id'] . "' AND user_id='" . $member_row['id'] . "'");
                if ($ticket->num_rows != $invoice_row[$ticket_type[$x]]) {
                    for ($i = 0; $i < $invoice_row[$ticket_type[$x]] - $ticket->num_rows; $i++) {
                        $db->query("INSERT IGNORE INTO ticket (invoice_id, user_id, ticket_type, ticket_code, order_code, ecpay_order, ip, pay_time) 
                        VALUES (
                            '" . $invoice_row['id'] . "', 
                            '" . $member_row['id'] . "', 
                            '" . $ticket_type[$x] . "', 
                            '" . random_string(7) . "', 
                            '" . $invoice_row['order_code'] . "', 
                            '" . $invoice_row['ecpay_order'] . "', 
                            '" . $ip . "',
                            '" . $invoice_row['pay_time'] . "'
                            )");
                    };
                };
            };
        };
        $result = $db->query("SELECT COUNT(*) AS ticket_total FROM ticket WHERE user_id='" . $member_row['id'] . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_total = $row['ticket_total'];
        // 已兌換
        $result = $db->query("SELECT COUNT(*) AS ticket_test_used FROM ticket WHERE ticket_type='" . 'ticket_test' . "' AND user_id='" . $member_row['id'] . "' AND used='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_test_used = $row['ticket_test_used'];

        $result = $db->query("SELECT COUNT(*) AS ticket_discount_used FROM ticket WHERE ticket_type='" . 'ticket_discount' . "' AND user_id='" . $member_row['id'] . "' AND used='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_discount_used = $row['ticket_discount_used'];

        $result = $db->query("SELECT COUNT(*) AS ticket_early_used FROM ticket WHERE ticket_type='" . 'ticket_early' . "' AND user_id='" . $member_row['id'] . "' AND used='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_early_used = $row['ticket_early_used'];

        $result = $db->query("SELECT COUNT(*) AS ticket_normal_used FROM ticket WHERE ticket_type='" . 'ticket_normal' . "' AND user_id='" . $member_row['id'] . "' AND used='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_normal_used = $row['ticket_normal_used'];

        $result = $db->query("SELECT COUNT(*) AS ticket_family_used FROM ticket WHERE ticket_type='" . 'ticket_family' . "' AND user_id='" . $member_row['id'] . "' AND used='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_family_used = $row['ticket_family_used'];

        // 尚未兌換
        $result = $db->query("SELECT COUNT(*) AS ticket_test_unused FROM ticket WHERE ticket_type='" . 'ticket_test' . "' AND user_id='" . $member_row['id'] . "' AND used!='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_test_unused = $row['ticket_test_unused'];

        $result = $db->query("SELECT COUNT(*) AS ticket_discount_unused FROM ticket WHERE ticket_type='" . 'ticket_discount' . "' AND user_id='" . $member_row['id'] . "' AND used!='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_discount_unused = $row['ticket_discount_unused'];

        $result = $db->query("SELECT COUNT(*) AS ticket_early_unused FROM ticket WHERE ticket_type='" . 'ticket_early' . "' AND user_id='" . $member_row['id'] . "' AND used!='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_early_unused = $row['ticket_early_unused'];

        $result = $db->query("SELECT COUNT(*) AS ticket_normal_unused FROM ticket WHERE ticket_type='" . 'ticket_normal' . "' AND user_id='" . $member_row['id'] . "' AND used!='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_normal_unused = $row['ticket_normal_unused'];

        $result = $db->query("SELECT COUNT(*) AS ticket_family_unused FROM ticket WHERE ticket_type='" . 'ticket_family' . "' AND user_id='" . $member_row['id'] . "' AND used!='" . 'true' . "' AND cancel!='" . 'true' . "'");
        $row = mysqli_fetch_assoc($result);
        $ticket_family_unused = $row['ticket_family_unused'];
        // 所有票券
        $filter = "";
        if (@$_POST['filter']) {
            if (@$_POST['filter'] === 'all') {
                $filter = "";
            } else {
                $filter = " AND ticket_type='" . $_POST['filter'] . "'";
            };
        };
        $ticket = $db->query("SELECT * FROM ticket WHERE cancel!='" . 'true' . "' AND user_id='" . $member_row['id'] . "'" . $filter . " ORDER BY id DESC");
        $ticket_all = [];
        while ($ticket_row = $ticket->fetch_assoc()) {
            array_push($ticket_all, [
                'id' => $ticket_row['id'],
                'ticket_type' => $ticket_row['ticket_type'],
                'ticket_code' => $ticket_row['ticket_code'],
                'used' => $ticket_row['used'],
                'used_time' => $ticket_row['used_time'],
                'pay_time' => $ticket_row['pay_time'],
                'gift' => $ticket_row['gift'],
            ]);
        };
        // 回傳 api
        $json = [
            'content' =>  $ticket_all,
            'ticket_total' =>  $ticket_total,
            'ticket_test' => ['used' => $ticket_test_used, 'unused' => $ticket_test_unused],
            'ticket_early' => ['used' => $ticket_early_used, 'unused' => $ticket_early_unused],
            'ticket_normal' => ['used' => $ticket_normal_used, 'unused' => $ticket_normal_unused],
            'ticket_discount' => ['used' => $ticket_discount_used, 'unused' => $ticket_discount_unused],
            'ticket_family' => ['used' => $ticket_family_used, 'unused' => $ticket_family_unused],
            'result' => 'success',
            'message' => '讀取成功'
        ];
        exit(json_encode($json));
        break;
    case 'buy':
        // 檢查參數
        if (empty(@$_POST['memberCode'])) {
            $json = ['result' => 'fail', 'message' => '缺乏必要參數'];
            exit(json_encode($json));
        };
        // 測試票檢查 
        /*if ($_POST['ticketTest'] > 0) {
            $json = ['result' => 'fail', 'message' => '不得購入測試票'];
            exit(json_encode($json));
        };*/
        // 檢查折扣碼
        if (@$_POST['coupon']) {
            $coupon = $db->query("SELECT * FROM coupon WHERE code='" . $_POST['coupon'] . "'");
            if (!$coupon->num_rows) {
                $json = ['result' => 'fail', 'message' => '此折扣碼無效'];
                exit(json_encode($json));
            };
            $coupon_row = $coupon->fetch_assoc();
            // 檢查折扣碼是否過期
            if ($coupon_row['end_time']) {
                if (get_today() > $coupon_row['end_time']) {
                    $json = ['result' => 'fail', 'message' => '折扣碼過期'];
                    exit(json_encode($json));
                };
            };
        };
        // 檢查是否有購票
        if (@$_POST['ticketTest'] + @$_POST['ticketEarly'] + @$_POST['ticketNormal'] + @$_POST['ticketDiscount'] + @$_POST['ticketFamily'] == 0) {
            $json = ['result' => 'fail', 'message' => '請選擇購買票種'];
            exit(json_encode($json));
        };
        // 檢查價格
        if (
            @$_POST['ticketTest'] < 0 or // 測試票
            @$_POST['ticketDiscount'] < 0 or // 超級早鳥票
            @$_POST['ticketEarly'] < 0 or // 早鳥票
            @$_POST['ticketNormal'] < 0 or // 全票
            @$_POST['ticketFamily'] < 0 // 親子套票
        ) {
            $json = ['result' => 'fail', 'message' => '價格錯誤'];
            exit(json_encode($json));
        };
        // 超級早鳥票檢查 
        if ($_POST['ticketDiscount']) {
            if ($_POST['ticketEarly'] != 0 or $_POST['ticketNormal'] != 0 or $_POST['ticketFamily'] != 0) {
                $json = ['result' => 'fail', 'message' => '超級早鳥票不得與其它票種併買'];
                exit(json_encode($json));
            };
        };
        // 早鳥票檢查 
        if ($_POST['ticketEarly']) {
            if ($_POST['ticketDiscount'] != 0 or $_POST['ticketFamily'] != 0 or $_POST['ticketNormal'] != 0) {
                $json = ['result' => 'fail', 'message' => '早鳥票不得與其它票種併買'];
                exit(json_encode($json));
            };
        };
        // 檢查會員 
        $result = $db->query("SELECT id, eslite_code FROM attend WHERE member_code='" . $_POST['memberCode'] . "' AND (sms_pass='" . 'true' . "' OR email_pass='" . 'true' . "')");
        if (!$result->num_rows) {
            $json = ['result' => 'fail', 'message' => '查無對應之有效會員'];
            exit(json_encode($json));
        };
        // 取得會員資訊
        $row = $result->fetch_assoc();
        // invoice
        $trade_no = 'es' . date('Y') . date('m') . date('d') . date('H') . date('i') . random_string(4);
        $db->query("INSERT IGNORE INTO invoice (user_id, eslite_code, order_code, ticket_test, ticket_discount, ticket_early, ticket_normal, ticket_family, coupon, company_name, company_id, ip) 
        VALUES (
            '" . $row['id'] . "', 
            '" . $row['eslite_code'] . "', 
            '" . $trade_no . "',
            '" . $_POST['ticketTest'] . "',
            '" . $_POST['ticketDiscount'] . "', 
            '" . $_POST['ticketEarly'] . "', 
            '" . $_POST['ticketNormal'] . "', 
            '" . $_POST['ticketFamily'] . "', 
            '" . @$_POST['coupon'] . "', 
            '" . @$_POST['companyName'] . "',
            '" . @$_POST['companyId'] . "', 
            '" . $ip . "'
            )");
        // output API
        $json = ['trade_no' => $trade_no, 'result' => 'success', 'message' => '成功購買'];
        exit(json_encode($json));
        break;
    case 'coupon_check':
        // 檢查是否為誠品會員
        $member = $db->query("SELECT NULL FROM attend WHERE member_code='" . $_COOKIE['member_code'] . "' AND eslite_code!='" . '' . "'");
        // 是誠品會員
        if ($member->num_rows) {
            // 沒有折扣碼
            if ((@$_POST['code']) == '') {
                $db->close();
                $json = [
                    'coupon' => 'eslite_member',
                    'coupon_name' => '誠品會員',
                    'coupon_discount' => '0.9',
                    'result' => 'success',
                    'message' => '使用誠品會員折扣'
                ];
                exit(json_encode($json));
            };
            // 有折扣碼
            if (@$_POST['code']) {
                // 檢查折扣碼是否存在
                $result = $db->query("SELECT code, password, name, discount, end_time FROM coupon WHERE code='" . $_POST['code'] . "'");
                if (!$result->num_rows) {
                    // 若折扣碼不存在
                    $db->close();
                    $json = [
                        'coupon' => 'eslite_member',
                        'coupon_name' => '誠品會員',
                        'coupon_discount' => '0.9',
                        'result' => 'success',
                        'message' => '使用誠品會員折扣'
                    ];
                    exit(json_encode($json));
                };
                $row = $result->fetch_assoc();
                // 檢查折扣碼是否過期
                if ($row['end_time']) {
                    if (get_today() > $row['end_time']) {
                        $db->close();
                        $json = [
                            'coupon' => 'eslite_member',
                            'coupon_name' => '誠品會員',
                            'coupon_discount' => '0.9',
                            'result' => 'success',
                            'message' => '使用誠品會員折扣'
                        ];
                        exit(json_encode($json));
                    };
                };
                // 折扣碼折扣比誠品會員多
                if ($row['discount'] < '0.9') {
                    $json = [
                        'coupon' => $row['code'],
                        'pw' => $row['password'],
                        'coupon_name' => $row['name'],
                        'coupon_discount' => $row['discount'],
                        'result' => 'success',
                        'message' => '此折扣碼存在'
                    ];
                    $db->close();
                    exit(json_encode($json));
                } else {
                    // 折扣碼折扣沒比誠品會員多
                    $db->close();
                    $json = [
                        'coupon' => 'eslite_member',
                        'coupon_name' => '誠品會員',
                        'coupon_discount' => '0.9',
                        'result' => 'success',
                        'message' => '使用誠品會員折扣'
                    ];
                    exit(json_encode($json));
                };
            };
        };
        // 非誠品會員
        if (!$member->num_rows) {
            // 檢查參數
            if ((@$_POST['code']) == '') {
                $json = ['result' => 'fail', 'message' => '缺少 code 參數'];
                exit(json_encode($json));
            };
            // 檢查折扣碼是否存在
            $result = $db->query("SELECT code, password, name, discount, end_time FROM coupon WHERE code='" . $_POST['code'] . "'");
            if (!$result->num_rows) {
                $json = ['result' => 'fail', 'message' => '折扣碼無效'];
                exit(json_encode($json));
            };
            $row = $result->fetch_assoc();
            // 檢查折扣碼是否過期
            if ($row['end_time']) {
                if (get_today() > $row['end_time']) {
                    $json = ['result' => 'fail', 'message' => '折扣碼過期'];
                    exit(json_encode($json));
                };
            };
            // output API
            $json = [
                'coupon' => $row['code'],
                'pw' => $row['password'],
                'coupon_name' => $row['name'],
                'coupon_discount' => $row['discount'],
                'result' => 'success',
                'message' => '此折扣碼存在'
            ];
            $db->close();
            exit(json_encode($json));
        };
        break;
    case 'coupon_clicked':
        // 新增折扣碼點擊次數
        $db->query("INSERT IGNORE INTO coupon_log (code, ip) VALUES ('" . $_POST['code'] . "', '" . $ip . "')");
        // output API
        $db->close();
        $json = ['result' => 'success', 'message' => '此折扣碼存在'];
        exit(json_encode($json));
        break;
    default:
        $db->close();
        $json = ['result' => 'fail', 'message' => '錯誤，查無 API'];
        exit(json_encode($json));
        break;
};
