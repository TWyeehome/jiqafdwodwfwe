<?php
header('Content-type:application/json; charset=utf-8');
/* 串接 Mysql */
require_once('php_connect_mysql.php');
switch (@$_POST['type']) {
    case 'check': // 發行確認
        // 沒登入過
        require_once('cookie_check.php');
        // 檢查必填參數
        if (empty(@$_POST['code'])) {
            $json = ['result' => 'fail', 'message' => '缺參數'];
            exit(json_encode($json));
        };
        // 檢查是否需要發行
        $ticket = $db->query("SELECT used, gift FROM ticket WHERE ticket_code='" . $_POST['code'] . "'");
        if (!$ticket->num_rows) {
            $json = ['result' => 'fail', 'message' => '無需檢查的發行'];
            exit(json_encode($json));
        };
        $row = $ticket->fetch_assoc();
        if ($row['used'] == 'true') {
            $json = [
                'gift' => $row['gift'],
                'result' => 'success',
                'message' => '讀取成功'
            ];
            exit(json_encode($json));
        };
        $json = ['result' => 'fail', 'message' => '需使用過票券才能兌換贈品'];
        exit(json_encode($json));
        break;
    case 'exchange': // 兌換贈品
        // 沒登入過
        require_once('cookie_check.php');
        // 檢查必填參數
        if (empty(@$_POST['code'])) {
            $json = ['result' => 'fail', 'message' => '缺參數'];
            exit(json_encode($json));
        };
        // 檢查是否需要發行
        $ticket = $db->query("SELECT NULL FROM ticket WHERE ticket_type='" . 'ticket_discount' . "' AND ticket_code='" . $_POST['code'] . "' AND used='" . 'true' . "'");
        if (!$ticket->num_rows) {
            $json = ['result' => 'fail', 'message' => '無可兌換的贈品'];
            exit(json_encode($json));
        };
        // 檢查是否已發行
        $ticket = $db->query("SELECT NULL FROM ticket WHERE ticket_type='" . 'ticket_discount' . "' AND ticket_code='" . $_POST['code'] . "' AND used='" . 'true' . "' AND gift!='" . '' . "'");
        if ($ticket->num_rows) {
            $json = ['result' => 'fail', 'message' => '贈品已兌換'];
            exit(json_encode($json));
        };
        // 
        $db->query("UPDATE ticket SET gift='" . date('Y-m-d H:i:s') . "' WHERE ticket_code='" . $_POST['code'] . "'");
        $json = ['gift' => date('Y-m-d H:i:s'), 'result' => 'success', 'message' => '成功兌換贈品'];
        exit(json_encode($json));
        break;
    default:
        $json = ['result' => 'fail', 'message' => '錯誤，查無 API'];
        exit(json_encode($json));
        break;
};
