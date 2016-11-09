<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 9/28/16 21:29
 */

function formatTime($unixTime) {
    $showTime = date('Y', $unixTime) . "年" . date('n', $unixTime) . "月" . date('j', $unixTime) . "日";

    if (date('Y', $unixTime) == date('Y')) {
        $showTime = date('n', $unixTime) . "月" . date('j', $unixTime) . "日 " . date('H:i', $unixTime);

        if (date('n.j', $unixTime) == date('n.j')) {
            $timeDifference = time() - $unixTime + 1;

            if ($timeDifference < 30) {
                return "刚刚";
            }
            if ($timeDifference >= 30 && $timeDifference < 60) {
                return $timeDifference . "秒前";
            }
            if ($timeDifference >= 60 && $timeDifference < 3600) {
                return floor($timeDifference / 60) . "分钟前";
            }
            return date('H:i', $unixTime);
        }
        if (date('n.j', ($unixTime + 86400)) == date('n.j')) {
            return "昨天 " . date('H:i', $unixTime);
        }
    }
    return $showTime;
}

function getVal($value, $default = '') {
    return isset($value) ? $value : $default;
}

function myMultiSort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC) {
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
    return $arrays;
}