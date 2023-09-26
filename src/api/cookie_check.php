<?php
if (empty(@$_COOKIE['member_code'])) {
    $json = ['result' => 'fail', 'message' => '沒登入'];
    exit(json_encode($json));
};


$result = $db->query("SELECT COUNT(*) AS member_cookie FROM attend WHERE member_code='" . $_COOKIE['member_code'] . "'");
$row = mysqli_fetch_assoc($result);
$member_cookie = $row['member_cookie'];
if (!$member_cookie) {
    $db->close();
    $json = ['result' => 'fail', 'message' => '無會員'];
    exit(json_encode($json));
};

