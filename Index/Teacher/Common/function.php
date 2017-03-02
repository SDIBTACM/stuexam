<?php
function getexamsearch($userId) {

    $isadmin = checkAdmin(1);
    if ($isadmin === false) {
        $sql = "`visible`='Y' AND (`isprivate`=0 or `creator` like '$userId')";
    } else {
        $sql = "`visible`='Y'";
    }

    $creator = I('get.creator', '', 'htmlspecialchars');
    if (!empty($creator)) {
        $sql = $sql . " AND creator = '$creator'";
    }
    return $sql;
}

function getproblemsearch($idKey, $problemType) {
    $chapterId = I('get.chapterId', 0, 'intval');
    $parentId = I('get.parentId', 0, 'intval');
    $pointId = I('get.pointId', 0, 'intval');

    // 查询知识点
    if ($pointId > 0) {
        $sql = "$idKey in (select question_id from ex_question_point where type = $problemType and point_id = $pointId)";
    } else if ($parentId > 0) {
        $sql = "$idKey in (select question_id from ex_question_point where type = $problemType and point_parent_id = $parentId)";
    } else if ($chapterId > 0) {
        $sql = "$idKey in (select question_id from ex_question_point where type = $problemType and chapter_id = $chapterId)";
    } else {
        $sql = "1=1";
    }

    // 查询创建者
    $creator = I('get.creator', '', 'htmlspecialchars');
    $problem = I('get.problem', 0, 'intval');
    if ($problem != 0 && !checkAdmin(1)) {
        /** 如果非管理员查询的是私有或者隐藏提库 则默认查询的是他本身 **/
        $creator = $_SESSION['user_id'];
    }
    if (!empty($creator)) {
        $sql = $sql . " AND creator = '$creator'";
    }

    // 查询版本类型
    $questionType = I('get.questionType', 0, 'intval');
    $sql = $sql . " AND question_type = $questionType";

    // 查询题目类型
    if ($problem < 0 || $problem > 2 || (!checkAdmin(1) && $problem == 2)) {
        $problem = 0;
    }
    $sql = $sql . " AND isprivate = $problem";

    return array(
        'sql' => $sql
    );
}

function set_get_key() {
    $_SESSION['getkey'] = strtoupper(substr(MD5($_SESSION['user_id'] . rand(0, 9999999)), 0, 10));
    return $_SESSION['getkey'];
}

function check_get_key() {
    if ($_SESSION['getkey'] != $_GET['getkey'])
        return false;
    return true;
}

function set_post_key() {
    $_SESSION['postkey'] = strtoupper(substr(MD5($_SESSION['user_id'] . rand(0, 9999999)), 0, 10));
    return $_SESSION['postkey'];
}

function check_post_key() {
    if ($_SESSION['postkey'] != $_POST['postkey'])
        return false;
    return true;
}

function cutstring($str, $length = 0) {
    $len = C('cutlen');
    $length = ($length ?: $len);
    $str = str_replace(array("　", "\t", "\n", "\r"), '', $str);
    if (mb_strlen($str) > $length) {
        return mb_substr($str, 0, $length, "utf-8");
    } else {
        return $str;
    }
}

function SortStuScore($table) {
    $where = array();
    $order = array();
    if (isset($_GET['xsid'])) {
        $xsid = $_GET['xsid'];
        $xsid = addslashes($xsid);
        $where[] = "{$table}.user_id like '%{$xsid}%'";
    }
    if (isset($_GET['xsname'])) {
        $xsname = $_GET['xsname'];
        $xsname = addslashes($xsname);
        $where[] = "{$table}.nick like '%{$xsname}%'";
    }
    if (isset($_GET['sortanum'])) {
        $sortanum = intval($_GET['sortanum']);
        if ($sortanum & 1) $order[] = "choosesum ASC";
        if ($sortanum & 2) $order[] = "judgesum ASC";
        if ($sortanum & 4) $order[] = "fillsum ASC";
        if ($sortanum & 8) $order[] = "programsum ASC";
        if ($sortanum & 16) $order[] = "score ASC";
    }
    if (isset($_GET['sortdnum'])) {
        $sortdnum = intval($_GET['sortdnum']);
        if ($sortdnum & 1) $order[] = "choosesum DESC";
        if ($sortdnum & 2) $order[] = "judgesum DESC";
        if ($sortdnum & 4) $order[] = "fillsum DESC";
        if ($sortdnum & 8) $order[] = "programsum DESC";
        if ($sortdnum & 16) $order[] = "score DESC";
    }
    $order[] = "user_id ASC";
    if (!empty($where[0])) {
        $where = join(' AND ', $where);
        $where = " WHERE " . $where;
    } else {
        $where = join('', $where);
    }
    $order = join(',', $order);
    $order = "ORDER BY " . $order;

    $sqladd = $where . " " . $order;
    return $sqladd;
}