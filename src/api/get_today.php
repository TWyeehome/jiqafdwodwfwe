<?php
function get_today()
{
    $today = getdate();
    date("Y/m/d H:i");  //日期格式化
    $year = $today["year"]; //年 
    $month = $today["mon"]; //月
    $day = $today["mday"];  //日
    if (strlen($month) == '1') $month = '0' . $month;
    if (strlen($day) == '1') $day = '0' . $day;
    $today = $year . "-" . $month . "-" . $day;
    return $today;
};
