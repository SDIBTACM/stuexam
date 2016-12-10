<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 9/28/16 21:29
 */

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

//判断数据是否为null
function checkNull($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if ($value && !is_array($value)) {
                return false;
            }
            if (!checkNull($value)) {
                return false;
            }
        }
        return true;
    } else {
        if (!$data) {
            return true;
        } else {
            return false;
        }
    }
}

//验证节点
function nodeValidate($node) {
    $nodes = M('node')->getField('node_name', true);
    if (!in_array($node, $nodes)) {
        return false;
    }
    return true;
}