<?php
header('Content-type:application/json; charset=utf-8');
/* 串接 Mysql */
require_once('php_connect_mysql.php');
// 確認參數
if (empty(@$_POST['type'])) {
    $json = ['result' => 'fail', 'message' => '缺少必要參數'];
    exit(json_encode($json));
};
// 取得 IP
require_once('get_ip.php');
// 隨機產生亂碼
require_once('./random_code.php');
//
switch ($_POST['type']) {
    case 'update_eslite':
        /* 更新誠品會員卡號 */
        // 確認是否登入中
        require_once('cookie_check.php');
        // 
        $times = $db->query("SELECT eslite_update FROM attend WHERE member_code='" . $_POST['memberCode'] . "'");
        if (!$times->num_rows) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '無此會員'];
            exit(json_encode($json));
        };
        $times_row = $times->fetch_assoc();
        if ($times_row['eslite_update'] >= 1) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '誠品會員卡號只能更新一次'];
            exit(json_encode($json));
        };
        // 檢查必要參數
        if (empty(@$_POST['memberCode']) or empty(@$_POST['esliteCode'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺必要參數'];
            exit(json_encode($json));
        };
        // 確認誠品會員卡號
        if ($_POST['esliteCode'] != '') {
            if (strpos($_POST['esliteCode'], ' ') !== false) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號不得包含空格'];
                exit(json_encode($json));
            };
            if (!is_numeric($_POST['esliteCode'])) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['esliteCode']) != 10) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            if (
                substr($_POST['esliteCode'], 0, 3) != '201' and
                substr($_POST['esliteCode'], 0, 3) != '211' and
                substr($_POST['esliteCode'], 0, 3) != '212' and
                substr($_POST['esliteCode'], 0, 3) != '271' and
                substr($_POST['esliteCode'], 0, 3) != '272' and
                substr($_POST['esliteCode'], 0, 3) != '295' and
                substr($_POST['esliteCode'], 0, 3) != '296' and
                substr($_POST['esliteCode'], 0, 3) != '297' and
                substr($_POST['esliteCode'], 0, 3) != '298' and
                substr($_POST['esliteCode'], 0, 3) != '299' and
                substr($_POST['esliteCode'], 0, 3) != '301' and
                substr($_POST['esliteCode'], 0, 3) != '302' and
                substr($_POST['esliteCode'], 0, 3) != '311' and
                substr($_POST['esliteCode'], 0, 3) != '361' and
                substr($_POST['esliteCode'], 0, 3) != '371' and
                substr($_POST['esliteCode'], 0, 3) != '372' and
                substr($_POST['esliteCode'], 0, 3) != '399'
            ) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            // 確認是否有重複誠品卡號
            $check_code = $db->query("SELECT NULL FROM attend WHERE eslite_code='" . $_POST['esliteCode'] . "'");
            if ($check_code->num_rows) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '此誠品會員卡號已被使用'];
                exit(json_encode($json));
            };
        };
        // 
        $check = $db->query("SELECT NULL FROM attend WHERE member_code='" . $_POST['memberCode'] . "' AND eslite_code='" . $_POST['esliteCode'] . "'");
        if ($check->num_rows != 0) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '請勿輸入相同誠品會員卡號'];
            exit(json_encode($json));
        };
        // 更新 
        $db->query("UPDATE attend SET eslite_code='" . $_POST['esliteCode'] . "', eslite_update='" . '1' . "', last_visited='" . date('Y-m-d H:i:s') . "' WHERE member_code='" . $_POST['memberCode'] . "'");
        $json = [
            'result' => 'success' // 誠品會員卡號更新成功
        ];
        $db->close();
        exit(json_encode($json));
        break;
    case 'checkCode':
        /* 檢查驗證碼 */
        // 檢查必要參數
        if (empty(@$_POST['mode'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺必要參數'];
            exit(json_encode($json));
        };
        // 信箱模式
        if ($_POST['mode'] === 'email') {
            // 檢查必要參數
            if (empty(@$_POST['email']) or empty(@$_POST['code'])) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '缺必要參數'];
                exit(json_encode($json));
            };
            // 檢查會員是否存在
            $result = $db->query("SELECT * FROM attend WHERE email='" . $_POST['email'] . "'");
            if ($result->num_rows == 0) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '查無對應之會員'];
                exit(json_encode($json));
            };
            $row = $result->fetch_assoc();
            // 檢查驗證碼
            $check = $db->query("SELECT NULL FROM attend WHERE email='" . $_POST['email'] . "' AND email_code='" . $_POST['code'] . "'");
            if (!$check->num_rows) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '驗證碼錯誤'];
                exit(json_encode($json));
            };
            // 驗證成功
            $db->query("UPDATE attend SET email_pass='" . 'true' . "' WHERE email='" . $_POST['email'] . "'");
            // 未註冊
            if ($row['username'] == '' or $row['email'] == '') {
                $json = [
                    'register' => false,
                    'result' => 'success',
                    'message' => '驗證成功，但尚未註冊完成'
                ];
                exit(json_encode($json));
            };
            // 已註冊
            setcookie('member_code', $row['member_code'], time() + 8640000, '/');
            $json = [
                'register' => true,
                'result' => 'success',
                'message' => '驗證成功'
            ];
            exit(json_encode($json));
        };
        // 手機模式
        if ($_POST['mode'] === 'phone') {
            // 檢查必要參數
            if (empty(@$_POST['number']) or empty(@$_POST['code'])) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '缺必要參數'];
                exit(json_encode($json));
            };
            // 檢查會員是否存在
            $result = $db->query("SELECT * FROM attend WHERE phone_number='" . $_POST['number'] . "'");
            if (!$result->num_rows) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '查無對應之會員'];
                exit(json_encode($json));
            };
            $row = $result->fetch_assoc();
            // 檢查驗證碼
            $check = $db->query("SELECT NULL FROM attend WHERE phone_number='" . $_POST['number'] . "' AND sms_code='" . $_POST['code'] . "'");
            if (!$check->num_rows) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '驗證碼錯誤'];
                exit(json_encode($json));
            };
            // 驗證成功
            $db->query("UPDATE attend SET sms_pass='" . 'true' . "' WHERE phone_number='" . $_POST['number'] . "'");
            // 未註冊
            if ($row['username'] == '' or $row['email'] == '') {
                $db->close();
                $json = [
                    'register' => false,
                    'result' => 'success',
                    'message' => '驗證成功，但尚未註冊完成'
                ];
                exit(json_encode($json));
            };
            // 已註冊
            setcookie('member_code', $row['member_code'], time() + 8640000, '/');
            // output API
            $json = ['register' => true, 'result' => 'success', 'message' => '驗證成功'];
            $db->close();
            exit(json_encode($json));
        };
        break;
    case 'check':
        // 確認是否登入中
        require_once('cookie_check.php');
        /* 確認登入狀態 */
        // 新增拜訪紀錄
        if (!isset($_COOKIE['visited'])) {
            $db->query("INSERT IGNORE INTO visited_log (ip) VALUES ('" . $ip . "')");
            setcookie('visited', 'true', time() + (300), '/'); // cookie 存活 5 分鐘
        };
        //
        $result = $db->query("SELECT id, member_code, qr_prove, username, phone_number, eslite_code FROM attend WHERE 
        member_code='" . $_COOKIE['member_code'] . "' AND (sms_pass='" . 'true' . "' OR email_pass='" . 'true' . "')");
        // 會員不存在
        if (!$result->num_rows) {
            // 刪除登入 cookie
            setcookie('member_code', '', -1, '/');
            $json = ['result' => 'fail', 'message' => '沒有編號為' . $_COOKIE['member_code'] . '的會員'];
            $db->close();
            exit(json_encode($json));
        };
        // 會員存在且登入過
        $row = $result->fetch_assoc();
        $db->query("UPDATE attend SET visited='" . $ip . "', last_visited='" . date('Y-m-d H:i:s') . "' WHERE member_code='" . $_COOKIE['member_code'] . "'");
        // output API
        $json = [
            'id' => $row['id'],
            'member_code' => $row['member_code'],
            'qr_prove' => $row['qr_prove'],
            //'name' => $row['username'],
            //'phone_number' => $row['phone_number'],
            'eslite_code' => $row['eslite_code'], // 誠品會員卡號
            'result' => 'success' // 登入過
        ];
        exit(json_encode($json));
        break;
    case 'profile': // 我的帳戶
        // 確認是否登入中
        require_once('cookie_check.php');
        /* 確認登入狀態 */
        // 新增拜訪紀錄
        if (!isset($_COOKIE['visited'])) {
            $db->query("INSERT IGNORE INTO visited_log (ip) VALUES ('" . $ip . "')");
            setcookie('visited', 'true', time() + (300), '/'); // cookie 存活 5 分鐘
        };
        // 確認會員是否存在
        $result = $db->query("SELECT * FROM attend WHERE member_code='" . $_COOKIE['member_code'] . "' AND (sms_pass='" . 'true' . "' OR email_pass='" . 'true' . "')");
        // 會員不存在
        if (!$result->num_rows) {
            // 刪除登入 cookie
            setcookie('member_code', '', -1, '/');
            $json = ['result' => 'fail', 'message' => '沒有編號為' . $_COOKIE['member_code'] . '的會員'];
            exit(json_encode($json));
        };
        // 會員存在且登入過
        $row = $result->fetch_assoc();
        $db->query("UPDATE attend SET visited='" . $ip . "', last_visited='" . date('Y-m-d H:i:s') . "' WHERE member_code='" . $_COOKIE['member_code'] . "'");
        // output API
        $json = [
            'id' => $row['id'],
            'member_code' => $row['member_code'],
            'qr_prove' => $row['qr_prove'],
            'name' => $row['username'],
            'phone_number' => $row['phone_number'],
            'sms_pass' => $row['sms_pass'],
            'email' => $row['email'],
            'email_pass' => $row['email_pass'],
            'eslite_code' => $row['eslite_code'], // 誠品會員卡號
            'eslite_update' => $row['eslite_update'], // 誠品會員卡號
            'result' => 'success',
            'message' => '登入過'
        ];
        exit(json_encode($json));
        break;
    case 'profile_update': // 更新我的帳戶
        // 確認是否登入中
        require_once('cookie_check.php');
        // 確認會員是否存在
        $result = $db->query("SELECT NULL FROM attend WHERE member_code='" . $_COOKIE['member_code'] . "' AND (sms_pass='" . 'true' . "' OR email_pass='" . 'true' . "')");
        // 會員不存在
        if (!$result->num_rows) {
            // 刪除登入 cookie
            setcookie('member_code', '', -1, '/');
            $json = ['result' => 'fail', 'message' => '沒有編號為' . $_COOKIE['member_code'] . '的會員'];
            exit(json_encode($json));
        };
        if ($_POST['detail'] === 'name') {
            if ($_POST['name'] == '') {
                $db->close();
                $json = ['result' => 'fail', 'message' => '請輸入暱稱'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['name']) < 2) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '暱稱不得少於 2 個字'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['name']) > 5) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '暱稱不得多於 5 個字'];
                exit(json_encode($json));
            };
            $db->query("UPDATE attend SET username='" . preg_replace('/\s+/', '', $_POST['name']) . "' WHERE member_code='" . $_COOKIE['member_code'] . "'");
        };
        if ($_POST['detail'] === 'phone') {
            $check = $db->query("SELECT NULL FROM attend WHERE phone_number='" . $_POST['phone'] . "'");
            if ($check->num_rows) {
                $json = ['result' => 'fail', 'message' => $_POST['phone'] . '已被使用，請重新輸入'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['phone']) > 12) {
                $json = ['result' => 'fail', 'message' => '行動電話錯誤'];
                exit(json_encode($json));
            };
            $db->query("UPDATE attend SET phone_number='" . preg_replace('/\s+/', '', $_POST['phone']) . "' WHERE member_code='" . $_COOKIE['member_code'] . "'");
        };
        if ($_POST['detail'] === 'email') {
            $check = $db->query("SELECT NULL FROM attend WHERE email='" . $_POST['email'] . "'");
            if ($check->num_rows) {
                $db->close();
                $json = ['result' => 'fail', 'message' => $_POST['email'] . '已被使用，請重新輸入'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['email']) > 64) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '電子郵件錯誤'];
                exit(json_encode($json));
            };
            if (preg_match("/^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}$/", $_POST['email'])) {
                $db->query("UPDATE attend SET email='" . preg_replace('/\s+/', '', $_POST['email']) . "' WHERE member_code='" . $_COOKIE['member_code'] . "'");
            } else {
                $json = ['result' => 'fail', 'message' => '電子郵件錯誤'];
                exit(json_encode($json));
            };
        };
        if ($_POST['detail'] == 'eslite') {
            if (strpos($_POST['esliteCode'], ' ') !== false) {
                $json = ['result' => 'fail', 'message' => '誠品會員卡號不得包含空格'];
                exit(json_encode($json));
            };
            if (!is_numeric($_POST['esliteCode'])) {
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['esliteCode']) != 10) {
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            if (
                substr($_POST['esliteCode'], 0, 3) != '201' and
                substr($_POST['esliteCode'], 0, 3) != '211' and
                substr($_POST['esliteCode'], 0, 3) != '212' and
                substr($_POST['esliteCode'], 0, 3) != '271' and
                substr($_POST['esliteCode'], 0, 3) != '272' and
                substr($_POST['esliteCode'], 0, 3) != '295' and
                substr($_POST['esliteCode'], 0, 3) != '296' and
                substr($_POST['esliteCode'], 0, 3) != '297' and
                substr($_POST['esliteCode'], 0, 3) != '298' and
                substr($_POST['esliteCode'], 0, 3) != '299' and
                substr($_POST['esliteCode'], 0, 3) != '301' and
                substr($_POST['esliteCode'], 0, 3) != '302' and
                substr($_POST['esliteCode'], 0, 3) != '311' and
                substr($_POST['esliteCode'], 0, 3) != '361' and
                substr($_POST['esliteCode'], 0, 3) != '371' and
                substr($_POST['esliteCode'], 0, 3) != '372' and
                substr($_POST['esliteCode'], 0, 3) != '399'
            ) {
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            $check = $db->query("SELECT NULL FROM attend WHERE eslite_code='" . $_POST['esliteCode'] . "'");
            if ($check->num_rows) {
                $json = ['result' => 'fail', 'message' => $_POST['esliteCode'] . ' 已被使用，請重新輸入'];
                exit(json_encode($json));
            };
            $db->query("UPDATE attend SET eslite_code='" . $_POST['esliteCode'] . "', eslite_update='" . '1' . "' WHERE member_code='" . $_COOKIE['member_code'] . "'");
        };
        // output API
        $json = ['result' => 'success',   'message' => '成功更新'];
        exit(json_encode($json));
        break;
    case 'send_code':
        if ($_POST['detail'] === 'email') {
            // 產生驗證碼 
            $prove_code = random_number(6);
            /* 測試機 */
            if ($_SERVER['SERVER_NAME'] === 'localhost') {
                // 更新信箱驗證碼
                $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
                // output API
                $json = ['result' => 'success', 'message' => '成功發送'];
                exit(json_encode($json));
            };
            /* 正式機 */
            // 更新驗證碼 
            $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
            $url = 'https://api.elasticemail.com/v2/email/send';
            try {
                $post = [
                    'from' => 'info@amping.io',
                    'fromName' => 'AMPING',
                    'apikey' => '',
                    'subject' => "領取驗證碼 - 《AI靈感大師：澳洲3D光影觸動樂園》",
                    'to' => $_POST['email'],
                    // 'msgBcc' => 'celiahsu@fansi.me',
                    'bodyHtml' => $prove_code . " 為您的《AI靈感大師：澳洲3D光影觸動樂園》驗證碼，此驗證碼有效期為 5 分鐘，逾期無效。",
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
                $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'true' . "')");
                // output API
                $json = ['result' => 'success', 'message' => '信件發送成功'];
                exit(json_encode($json));
            } catch (Exception $ex) {
                // 郵件發送失敗	
                // 新增 email log
                $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'false' . "')");
                // output API
                $json = ['result' => 'fail', 'message' => '信件發送失敗'];
                exit(json_encode($json));
            };
        };
        if ($_POST['detail'] === 'phone') {
            // 產生驗證碼 
            $prove_code = random_number(6);
            // 測試機
            if ($_SERVER['SERVER_NAME'] === 'localhost') {
                // 更新驗證碼
                $db->query("UPDATE attend SET sms_code='" . $prove_code . "' WHERE phone_number='" . $_POST['phone'] . "'");
                // output API
                $json = ['result' => 'success', 'message' => '成功送出'];
                exit(json_encode($json));
            };
            /* 正式機 */
            // 更新驗證碼
            $db->query("UPDATE attend SET sms_code='" . $prove_code . "' WHERE phone_number='" . $_POST['phone'] . "'");
            // 寄送三竹簡訊
            $curl = curl_init();
            // url
            // 企業帳號 (三站)
            // https://smsapi.mitake.com.tw/api/mtk/SmSend?
            // 個人帳號 (二站)
            // http://smsb2c.mitake.com.tw/b2c/mtk/SmSend?
            $url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?';
            $url .= 'CharsetURL=UTF-8';
            // parameters
            $data = 'username=90451526SMS';
            $data .= '&no';
            $data .= '&dstaddr=' . $_POST['phone']; // 收簡訊人的號碼
            $data .= '&smbody=' . $prove_code . ' 為您的《AI靈感大師:澳洲3D光影觸動樂園》驗證碼。';
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
            $db->query("INSERT IGNORE INTO phone_log (phone_number, log) VALUES ('" . $_POST['phone'] . "', '" . $output . "')");
            // output API
            $json = ['log' => $output, 'result' => 'success', 'message' => '成功送出'];
            exit(json_encode($json));
        };
        break;
    case 'check_code':
        if ($_POST['detail'] === 'email') {
            $result = $db->query("SELECT NULL FROM attend WHERE email='" . $_POST['email'] . "' AND email_code='" . $_POST['emailCode'] . "'");
            if (!$result->num_rows) {
                $json = ['result' => 'fail', 'message' => '驗證碼錯誤'];
                exit(json_encode($json));
            };
            $db->query("UPDATE attend SET email_pass='" . 'true' . "' WHERE email='" . $_POST['email'] . "'");
            $json = ['result' => 'success', 'message' => '驗證成功'];
            exit(json_encode($json));
        };
        if ($_POST['detail'] === 'phone') {
            $result = $db->query("SELECT NULL FROM attend WHERE phone_number='" . $_POST['phone'] . "' AND sms_code='" . $_POST['phoneCode'] . "'");
            if (!$result->num_rows) {
                $json = ['result' => 'fail', 'message' => '驗證碼錯誤'];
                exit(json_encode($json));
            };
            $db->query("UPDATE attend SET sms_pass='" . 'true' . "' WHERE phone_number='" . $_POST['phone'] . "'");
            $json = ['result' => 'success', 'message' => '驗證成功'];
            exit(json_encode($json));
        };
        break;
    case 'login':
        /* 登入 */
        // 檢查必要參數
        if (empty(@$_POST['mode'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺必要參數'];
            exit(json_encode($json));
        };
        // 信箱模式
        if ($_POST['mode'] === 'email') {
            // 檢查必要參數
            if (empty(@$_POST['email'])) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '缺必要參數'];
                exit(json_encode($json));
            };
            // 檢查信箱
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '請輸入有效信箱'];
                exit(json_encode($json));
            };
            //
            $result = $db->query("SELECT * FROM attend WHERE email='" . $_POST['email'] . "'");
            if ($result->num_rows == 0) {
                // 新增會員
                $db->query("INSERT IGNORE INTO attend (member_code, qr_prove, qr_prove_b, email) VALUES ('" . random_string(5) . "', '" . random_string(1) . "', '" . random_string(1) . "', '" . $_POST['email']  . "')");
                // 產生信箱驗證碼 
                $prove_code = random_number(6);
                /* 測試機 */
                if ($_SERVER['SERVER_NAME'] === 'localhost') {
                    // 更新信箱驗證碼
                    $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
                    // output API
                    $json = ['result' => 'success', 'message' => '成功取得會員資訊'];
                    exit(json_encode($json));
                };
                /* 正式機 */
                // 更新驗證碼 
                $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
                $url = 'https://api.elasticemail.com/v2/email/send';
                try {
                    $post = [
                        'from' => 'info@amping.io',
                        'fromName' => 'AMPING',
                        'apikey' => '',
                        'subject' => "領取驗證碼 - 《AI靈感大師：澳洲3D光影觸動樂園》",
                        'to' => $_POST['email'],
                        // 'msgBcc' => 'celiahsu@fansi.me',
                        'bodyHtml' => $prove_code . " 為您的《AI靈感大師：澳洲3D光影觸動樂園》註冊驗證碼此驗證碼有效期為5分鐘，逾期無效。",
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
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'true' . "')");
                    // output API
                    $json = ['result' => 'success', 'message' => '信件發送成功'];
                    exit(json_encode($json));
                } catch (Exception $ex) {
                    // 郵件發送失敗	
                    // 新增 email log
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'false' . "')");
                    // output API
                    $json = ['result' => 'fail', 'message' => '信件發送失敗'];
                    exit(json_encode($json));
                };
                /*
                // 更新驗證碼 
                $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
                // 發信
                $email_to = $_POST['email'];
                $title = "baby shark 驗證碼";
                $body = "baby shark 驗證碼為: " . $prove_code;
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=iso-8859-1';
                $headers[] = 'From: info@amping.io';
                // 自動發信函示 
                if (mail($email_to, $title, $body, $headers) == 1) {
                    // 信件發送成功
                    // 新增 email log
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'true' . "')");
                    // output API
                    $json = ['result' => 'success', 'message' => '信件發送成功'];
                } else {
                    // 信件發送失敗
                    // 新增 email log
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'true' . "')");
                    // output API
                    $json = ['result' => 'fail', 'message' => '信件發送失敗'];
                };
                exit(json_encode($json));
                */
            } else {
                $row = $result->fetch_assoc();
                // 產生驗證碼
                $prove_code = random_number(6);
                /* 測試機 */
                if ($_SERVER['SERVER_NAME'] === 'localhost') {
                    // 更新驗證碼
                    $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
                    // output API
                    $json = ['result' => 'success', 'message' => '成功取得會員資訊'];
                    exit(json_encode($json));
                };
                /* 正式機 */
                // 更新驗證碼 
                $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
                $url = 'https://api.elasticemail.com/v2/email/send';
                try {
                    $post = array(
                        'from' => 'info@amping.io',
                        'fromName' => 'AMPING',
                        'apikey' => '',
                        'subject' => "領取驗證碼 - 《AI靈感大師：澳洲3D光影觸動樂園》",
                        'to' => $_POST['email'],
                        // 'msgBcc' => 'celiahsu@fansi.me',
                        'bodyHtml' => $prove_code . " 為您的《AI靈感大師：澳洲3D光影觸動樂園》註冊驗證碼此驗證碼有效期為5分鐘，逾期無效。",
                        // 'bodyText' => 'Text Body',
                        'isTransactional' => false
                    );

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
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'true' . "')");
                    // output API
                    $json = ['result' => 'success', 'message' => '信件發送成功'];
                    exit(json_encode($json));
                } catch (Exception $ex) {
                    // 郵件發送失敗	
                    // 新增 email log
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'false' . "')");
                    // output API
                    $json = ['result' => 'fail', 'message' => '信件發送失敗'];
                    exit(json_encode($json));
                };
                /*
                // 更新驗證碼 
                $db->query("UPDATE attend SET email_code='" . $prove_code . "' WHERE email='" . $_POST['email'] . "'");
                // 發信
                $email_to = $_POST['email'];
                $title = "baby shark 驗證碼";
                $body = "baby shark 驗證碼為: " . $prove_code;
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=iso-8859-1';
                $headers[] = 'From: info@amping.io';
                // 自動發信函示
                if (mail($email_to, $title, $body, $headers) == 1) {
                    // 信件發送成功
                    // 新增 email log
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'true' . "')");
                    // output API
                    $json = ['result' => 'success', 'message' => '信件發送成功'];
                } else {
                    // 信件發送失敗
                    // 新增 email log
                    $db->query("INSERT IGNORE INTO email_log (email, email_code, status) VALUES ('" . $_POST['email'] . "', '" . $prove_code . "', '" . 'true' . "')");
                    // output API
                    $json = ['result' => 'fail', 'message' => '信件發送失敗'];
                };
                exit(json_encode($json));
                */
            };
        };
        // 手機模式
        if ($_POST['mode'] === 'phone') {
            // 檢查必要參數
            if (empty(@$_POST['number'])) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '缺必要參數'];
                exit(json_encode($json));
            };
            //
            $result = $db->query("SELECT * FROM attend WHERE phone_number='" . $_POST['number'] . "'");
            if ($result->num_rows == 0) {
                // 新增會員
                $db->query("INSERT IGNORE INTO attend (member_code, qr_prove, qr_prove_b, phone_number) VALUES ('" . random_string(6) . "', '" . random_string(1) . "', '" . random_string(1) . "', '" . $_POST['number']  . "')");
                // 產生驗證碼
                $prove_code = random_number(6);
                /* 測試機 */
                if ($_SERVER['SERVER_NAME'] === 'localhost') {
                    // 更新驗證碼
                    $db->query("UPDATE attend SET sms_code='" . $prove_code . "' WHERE phone_number='" . $_POST['number'] . "'");
                    // output API
                    $json = ['result' => 'success', 'message' => '成功取得會員資訊'];
                    exit(json_encode($json));
                };
                /* 正式機 */
                // 更新驗證碼
                $db->query("UPDATE attend SET sms_code='" . $prove_code . "' WHERE phone_number='" . $_POST['number'] . "'");
                // 寄送三竹簡訊
                $curl = curl_init();
                // url
                // 企業帳號 (三站)
                // https://smsapi.mitake.com.tw/api/mtk/SmSend?
                // 個人帳號 (二站)
                // http://smsb2c.mitake.com.tw/b2c/mtk/SmSend?
                $url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?';
                $url .= 'CharsetURL=UTF-8';
                // parameters
                $data = 'username=90451526SMS';
                $data .= '&no'; 
                $data .= '&dstaddr=' . $_POST['number']; // 收簡訊人的號碼
                $data .= '&smbody=' . $prove_code . ' 為您的《AI靈感大師:澳洲3D光影觸動樂園》註冊驗證碼。';
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
                $db->query("INSERT IGNORE INTO phone_log (phone_number, log) VALUES ('" . $_POST['number'] . "', '" . $output . "')");
                // output API
                $json = ['log' => $output, 'result' => 'success', 'message' => '成功取得會員資訊'];
                exit(json_encode($json));
            } else {
                $row = $result->fetch_assoc();
                // 測試機
                if ($_SERVER['SERVER_NAME'] === 'localhost') {
                    // 產生驗證碼
                    $prove_code = random_number(6);
                    // 更新驗證碼
                    $db->query("UPDATE attend SET sms_code='" . $prove_code . "' WHERE phone_number='" . $_POST['number'] . "'");
                    // output API
                    $json = ['result' => 'success', 'message' => '成功取得會員資訊'];
                    exit(json_encode($json));
                };
                // 正式機
                $prove_code = random_number(6);
                // 更新驗證碼
                $db->query("UPDATE attend SET sms_code='" . $prove_code . "' WHERE phone_number='" . $_POST['number'] . "'");
                // 寄送三竹簡訊
                $curl = curl_init();
                // 企業帳號 (三站)
                // https://smsapi.mitake.com.tw/api/mtk/SmSend?
                // 個人帳號 (二站)
                // http://smsb2c.mitake.com.tw/b2c/mtk/SmSend?
                $url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?';
                $url .= 'CharsetURL=UTF-8';
                // parameters
                $data = 'username=90451526SMS';
                $data .= '&no';
                $data .= '&dstaddr=' . $_POST['number']; // 收簡訊人的號碼 
                $data .= '&smbody=' . $prove_code . ' 為您的《AI靈感大師:澳洲3D光影觸動樂園》註冊驗證碼。';
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
                $db->query("INSERT IGNORE INTO phone_log (phone_number, log) VALUES ('" . $_POST['number'] . "', '" . $output . "')");
                // 輸出 API
                $json = [
                    'log' => $output,
                    'sms_code' => $prove_code,
                    'result' => 'success',
                    'message' => '成功取得會員資訊'
                ];
                exit(json_encode($json));
            };
        };
        break;
    case 'logout':
        /* 登出 */
        setcookie('member_code', '', -1, '/');
        $json = ['result' => 'success', 'message' => '登出'];
        exit(json_encode($json));
        break;
    case 'register':
        /* 註冊 */
        // 確認參數
        if (empty(@$_POST['number']) or empty(@$_POST['email'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺少必要參數'];
            exit(json_encode($json));
        };
        // 確認誠品會員卡號
        if ($_POST['esliteCode'] != '') {
            if (strpos($_POST['esliteCode'], ' ') !== false) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號不得包含空格'];
                exit(json_encode($json));
            };
            if (!is_numeric($_POST['esliteCode'])) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['esliteCode']) != 10) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            if (
                substr($_POST['esliteCode'], 0, 3) != '201' and
                substr($_POST['esliteCode'], 0, 3) != '211' and
                substr($_POST['esliteCode'], 0, 3) != '212' and
                substr($_POST['esliteCode'], 0, 3) != '271' and
                substr($_POST['esliteCode'], 0, 3) != '272' and
                substr($_POST['esliteCode'], 0, 3) != '295' and
                substr($_POST['esliteCode'], 0, 3) != '296' and
                substr($_POST['esliteCode'], 0, 3) != '297' and
                substr($_POST['esliteCode'], 0, 3) != '298' and
                substr($_POST['esliteCode'], 0, 3) != '299' and
                substr($_POST['esliteCode'], 0, 3) != '301' and
                substr($_POST['esliteCode'], 0, 3) != '302' and
                substr($_POST['esliteCode'], 0, 3) != '311' and
                substr($_POST['esliteCode'], 0, 3) != '361' and
                substr($_POST['esliteCode'], 0, 3) != '371' and
                substr($_POST['esliteCode'], 0, 3) != '372' and
                substr($_POST['esliteCode'], 0, 3) != '399'
            ) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '誠品會員卡號錯誤'];
                exit(json_encode($json));
            };
            // 確認是否有重複誠品卡號
            $check_code = $db->query("SELECT NULL FROM attend WHERE eslite_code='" . $_POST['esliteCode'] . "'");
            if ($check_code->num_rows) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '此誠品會員卡號已被使用'];
                exit(json_encode($json));
            };
        };
        // 確認姓名
        if (@$_POST['name']) {
            if (mb_strlen($_POST['name']) < 2) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '暱稱不得少於 2 個字'];
                exit(json_encode($json));
            };
            if (mb_strlen($_POST['name']) > 5) {
                $db->close();
                $json = ['result' => 'fail', 'message' => '暱稱不得超過 5 個字'];
                exit(json_encode($json));
            };
        };
        // 信箱模式
        if (@$_POST['mode'] === 'email') {
            // 確認會員
            $result = $db->query("SELECT * FROM attend WHERE email='" . $_POST['email'] . "' AND email_pass='" . 'true' . "'");
            // 會員不存在
            if ($result->num_rows == 0) {
                $json = ['result' => 'fail', 'message' => '查無此信箱之會員'];
                exit(json_encode($json));
            };
            // 確認手機是否重複
            $member = $db->query("SELECT * FROM attend WHERE phone_number='" . $_POST['number'] . "'");
            if ($member->num_rows != 0) {
                $member_row = $member->fetch_assoc();
                if ($member_row['username'] == '') {
                    $db->query("DELETE FROM attend WHERE id='" . $member_row['id'] . "'");
                } else {
                    $json = ['result' => 'fail', 'message' => $_POST['number'] . ' 已被使用'];
                    exit(json_encode($json));
                };
            };
            // 
            $row = $result->fetch_assoc();
            $db->query("UPDATE attend SET username='" . preg_replace('/\s+/', '', $_POST['name']) . "', phone_number='" . preg_replace('/\s+/', '', $_POST['number']) . "', email='" . preg_replace('/\s+/', '', $_POST['email']) . "', eslite_code='" . $_POST['esliteCode'] . "', visited='" . $ip . "', last_visited='" . date('Y-m-d H:i:s') . "' WHERE email='" . $_POST['email'] . "'");
        };
        // 手機模式
        if (@$_POST['mode'] === 'phone') {
            // 確認會員
            $result = $db->query("SELECT * FROM attend WHERE phone_number='" . $_POST['number'] . "' AND sms_pass='" . 'true' . "'");
            // 會員不存在
            if (!$result->num_rows) {
                $json = ['result' => 'fail', 'message' => '查無此號碼之會員'];
                exit(json_encode($json));
            };
            // 確認信箱是否重複
            $member = $db->query("SELECT * FROM attend WHERE email='" . $_POST['email'] . "'");
            if ($member->num_rows != 0) {
                $member_row = $member->fetch_assoc();
                if ($member_row['username'] == '') {
                    $db->query("DELETE FROM attend WHERE id='" . $member_row['id'] . "'");
                } else {
                    $json = ['result' => 'fail', 'message' => $_POST['email'] . ' 已被使用'];
                    exit(json_encode($json));
                };
            };
            // 
            $row = $result->fetch_assoc();
            $db->query("UPDATE attend SET username='" . preg_replace('/\s+/', '', $_POST['name']) . "', phone_number='" . preg_replace('/\s+/', '', $_POST['number']) . "', email='" . preg_replace('/\s+/', '', $_POST['email']) . "', eslite_code='" . $_POST['esliteCode'] . "', visited='" . $ip . "', last_visited='" . date('Y-m-d H:i:s') . "' WHERE phone_number='" . $_POST['number'] . "'");
        };
        // 登入
        setcookie('member_code', $row['member_code'], time() + 8640000, '/');
        // $_SESSION['member_code'] = $row['member_code'];
        // 輸出 API 結果
        $json = ['result' => 'success', 'message' => '註冊完成'];
        exit(json_encode($json));
        break;
    case 'update_qr':
        // 確認是否登入中
        require_once('cookie_check.php');
        //
        $result = $db->query("SELECT id, username, email, qr_prove FROM attend WHERE member_code='" . $_COOKIE['member_code'] . "'");
        // 若查無會員
        if (!$result->num_rows) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '查無對應之會員，錯誤 QRCODE'];
            exit(json_encode($json));
        };
        $row = $result->fetch_assoc();
        $random = random_string(1);
        $db->query("UPDATE attend SET qr_prove_b='" . $row['qr_prove'] . "', qr_prove='" . $random . "' WHERE member_code='" . $_COOKIE['member_code'] . "'");
        $json = [
            'id' => $row['id'],
            'qr_prove' => $random,
            'result' => 'success',
            'message' => '更新 QR 成功'
        ];
        $db->close();
        exit(json_encode($json));
        break;
    default:
        $json = ['result' => 'fail', 'message' => '查無此 API'];
        exit(json_encode($json));
};
