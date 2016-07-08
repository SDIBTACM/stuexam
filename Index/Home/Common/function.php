<?php

function makesx($myarr, $start, $finish, $stunum) {
    $num = $finish - $start + 1;
    $fac = array(1, 1, 2, 6, 24, 120, 720, 5040, 40320, 362880, 3628800, 39916800);
    $rightnum = $stunum % $fac[$num];
    $tmp = array();
    for ($i = 1; $i <= $num; $i++) {
        $tmp[] = $i;
    }
    for ($i = 1; $i <= $num; $i++) {
        $div = intval(floor($rightnum / $fac[$num - $i]));
        $rightnum = $rightnum % $fac[$num - $i];
        $myarr[$start + $i - 1] = $tmp[$div] - 1 + $start;
        array_splice($tmp, $div, 1);
    }
    unset($tmp);
    unset($fac);
    return $myarr;
}
