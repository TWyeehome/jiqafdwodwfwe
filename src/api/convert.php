<?php
header('Content-type:application/json; charset=utf-8');
// 檢查 api 必填參數
if (empty(@$_POST['type'])) {
    $json = ['result' => 'fail', 'message' => '缺少 type 參數'];
    exit(json_encode($json));
};
/* 串接 Mysql */
require_once('php_connect_mysql.php');
// 取 ip
require_once('get_ip.php');
//
switch (@$_POST['type']) {
    case 'send':
        // 檢查 api 必填參數
        if (empty(@$_POST['way']) or empty(@$_POST['contact'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺少必要參數'];
            exit(json_encode($json));
        };
        // 檢查聯絡方式是否重複
        $result = $db->query("SELECT COUNT(*) AS contact FROM convert_log WHERE contact='" . $_POST['contact'] . "'");
        $row = mysqli_fetch_assoc($result);
        $contact = $row['contact'];
        if ($contact) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '此聯絡方式已重複，請重新填寫'];
            exit(json_encode($json));
        };
        // 新增
        $db->query("INSERT IGNORE INTO convert_log (way, contact, ip) 
        VALUES ('" . $_POST['way'] . "', '" . $_POST['contact'] . "', '" . $ip . "')");
        // 存入 cookie
        setcookie('convert', $_POST['contact'], time() + 8640000, '/');
        // output API
        $json = ['result' => 'success', 'message' => '成功新增'];
        $db->close();
        exit(json_encode($json));
        break;
    case 'check':
        // 檢查 api 必填參數
        if (empty(@$_POST['contact'])) {
            $db->close();
            $json = ['result' => 'fail', 'message' => '缺少必要參數'];
            exit(json_encode($json));
        };
        $convert = $db->query("SELECT way, contact, created_at FROM convert_log WHERE contact='" . $_POST['contact'] . "'");
        if (!$convert->num_rows) {
            setcookie('convert', '');
            $db->close();
            $json = ['result' => 'fail', 'message' => '查無'];
            exit(json_encode($json));
        };
        $row = $convert->fetch_assoc();
        // 會員存在且登入過
        $json = [
            'way' => $row['way'],
            'contact' => $row['contact'],
            'created_at' => $row['created_at'],
            'result' => 'success',
            'message' => '成功取得'
        ];
        $db->close();
        exit(json_encode($json));
        break;
    default:
        $db->close();
        $json = ['result' => 'fail', 'message' => '錯誤，查無 API'];
        exit(json_encode($json));
        break;
};
