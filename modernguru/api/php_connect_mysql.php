<?php
if ($_SERVER['SERVER_NAME'] === 'localhost') {
	// 測試機
	$server_name = "";
	$user_name = "";
	$password = "";
	$db_name = "";
} else {
	// azure
	$server_name = "";
	$user_name = "";
	$password = "";
	$db_name = "";
};

$db = new mysqli($server_name, $user_name, $password, $db_name);

/* 顯示錯誤訊息 */
echo $db->error;

if ($db->connect_error) {
	echo ('資料庫連線錯誤:' . $db->error);
};

/* 設定國際碼(中文才不會變成亂碼) */
$db->query('SET NAMES UTF8');
/* 設定資料庫時區為台灣的時區 */
$db->query('SET time_zone = "+8:00"');
//
date_default_timezone_set('Asia/Taipei');
// 30 天
ini_set('session.gc_maxlifetime', 2592000);
ini_set( "session.cookie_lifetime", 2592000);
session_set_cookie_params(2592000);
session_start();