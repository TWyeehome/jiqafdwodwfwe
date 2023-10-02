<?php
switch ($_SERVER['SERVER_NAME']) {
    case 'localhost':
        $test = true; // 測試
        break;
    default:
    $test = false;
};

