<?php
header('Content-type:application/json; charset=utf-8');

/* 串接 Mysql */
require_once('php_connect_mysql.php');

if (empty(@$_POST['user_id']) or empty(@$_POST['ticked_id'])) {
	$json = array('result' => 'fail', 'message' => '缺乏必要參數');
	exit(json_encode($json));
};

// 確認是否有此票
$ticket_check = $db->query("SELECT id FROM ticket WHERE id='" . $_POST['ticked_id'] . "'");
if ($ticket_check->num_rows == 0) {
	$db->close();
	$json = ['result' => 'fail', 'message' => '查無此票券'];
	exit(json_encode($json));
};
// 確認是否有此會員
$user_check = $db->query("SELECT phone_number FROM attend WHERE id='" . $_POST['user_id'] . "'");
if ($user_check->num_rows == 0) {
	$db->close();
	$json = ['result' => 'fail', 'message' => '查無此會員'];
	exit(json_encode($json));
};
$user_check_row = $user_check->fetch_assoc();
// 確認此人是否有此票
$get_check = $db->query("SELECT id, user_id FROM ticket WHERE id='" . $_POST['ticked_id'] . "' AND user_id='" . @$_POST['user_id'] . "'");
if ($get_check->num_rows == 0) {
	$db->close();
	$json = ['result' => 'fail', 'message' => 'QRCODE 無效，請重新刷新'];
	exit(json_encode($json));
};
// 確認 QRCODE 防偽
/*$prove_check = $db->query("SELECT id FROM attend WHERE id='" . $_POST['user_id'] . "'");
if ($prove_check->num_rows == 0) {
	$db->close();
	$json = ['result' => 'fail', 'message' => 'QRCODE 無效，請重新刷新'];
	exit(json_encode($json));
};*/
// 票務是否被掃過
$used_check = $db->query("SELECT enter_time FROM ticket WHERE id='" . $_POST['ticked_id'] . "' AND used='" . 'true' . "'");
$used_check_row = $used_check->fetch_assoc();
// 
$user_clean = $db->query("SELECT price FROM ticket WHERE id='" . $_POST['ticked_id'] . "'");
$user_clean_row = $user_clean->fetch_assoc();
if ($used_check->num_rows == 0) {
	// 更新票使用狀況
	$db->query("UPDATE ticket SET used='" . 'true' . "', enter_time='" . date('Y-m-d H:i:s') . "' WHERE id='" . $_POST['ticked_id'] . "'");
	$json = [
		'result' => 'success',
		'message' => '掃描成功',
		'price' => $user_clean_row['price'],
		'phone' => $user_check_row['phone_number'],
	];
	exit(json_encode($json));
} else {
	$json = [
		'result' => 'fail',
		'message' => '該票已被使用<br>使用過的號碼: ' . $user_check_row['phone_number'] . '<br>使用時間點: ' . $used_check_row['enter_time']
	];
	exit(json_encode($json));
};
