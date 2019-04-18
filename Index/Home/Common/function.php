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

/**
 * 用于比较ip 是否属于某个IP的子网
 *
 * @param string $src 待比较IP
 * @param string $beCompare
 * @param int $mask 掩码位数
 *
 * @return bool
 */
function compareIpWithSubnetMask($src, $beCompare, $mask = 0) {
    if ($mask == 0) {
        return true;
    }

    if (filter_var($src, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
        filter_var($beCompare, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $mask <= 32) { // ipv4

        $srcBin = sprintf("%032s",base_convert(bin2hex(inet_pton($src)), 16, 2));
        $beCompareBin = sprintf("%032s",base_convert(bin2hex(inet_pton($beCompare)), 16, 2));

        return (strncmp($srcBin, $beCompareBin, $mask) == 0);

    } else if (filter_var($src, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) &&
        filter_var($beCompare, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && $mask <= 128) { // ipv6

        $bytesAddr = unpack('n*', @inet_pton($src));
        $bytesTest = unpack('n*', @inet_pton($beCompare));

        if (!$bytesAddr || !$bytesTest) {
            return false;
        }

        for ($i = 1, $ceil = ceil($mask / 16); $i <= $ceil; ++$i) {
            $left = $mask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask2 = ~(0xffff >> $left) & 0xffff;
            if (($bytesAddr[$i] & $mask2) != ($bytesTest[$i] & $mask2)) {
                return false;
            }
        }

        return true;

    } else { // ?
        return false;
    }
}
