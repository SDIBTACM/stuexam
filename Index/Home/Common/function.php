<?php

function makeSequence($myarr, $start, $finish, $stunum) {
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

function getProblemSequence($numProblem, $randnum) {
    $arr = array();
    for ($i = 0; $i < $numProblem;) {
        if ($i + 11 <= $numProblem) {
            $arr = makeSequence($arr, $i, $i + 10, $randnum);
            $i = $i + 11;
        } else {
            $arr = makeSequence($arr, $i, $numProblem - 1, $randnum);
            break;
        }
    }
    return $arr;
}
