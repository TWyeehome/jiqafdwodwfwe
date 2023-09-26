<?php
function random_string($length = 32, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    if (!is_int($length) || $length < 0) {
        return false;
    };
    $characters_length = strlen($characters) - 1;
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, $characters_length)];
    };
    return $string;
};

function random_number($length = 32, $characters = '0123456789')
{
    if (!is_int($length) || $length < 0) {
        return false;
    };
    $characters_length = strlen($characters) - 1;
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, $characters_length)];
    };
    return $string;
};
