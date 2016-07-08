<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/9/16 01:42
 */

namespace Teacher\Convert;


class ExamConvert
{
    public static function convertExamDataFromPost() {
        $arr = array();

        $arr['start_time'] = intval($_POST['syear']) . "-" . intval($_POST['smonth']) . "-" . intval($_POST['sday']) . " " . intval($_POST['shour']) . ":" . intval($_POST['sminute']) . ":00";
        $arr['end_time'] = intval($_POST['eyear']) . "-" . intval($_POST['emonth']) . "-" . intval($_POST['eday']) . " " . intval($_POST['ehour']) . ":" . intval($_POST['eminute']) . ":00";
        $title = I('post.examname', '');
        if (get_magic_quotes_gpc()) {
            $title = stripslashes($title);
        }
        $arr['title'] = $title;
        $arr['choosescore'] = I('post.xzfs', 0, 'intval');
        $arr['judgescore'] = I('post.pdfs', 0, 'intval');
        $arr['fillscore'] = I('post.tkfs', 0, 'intval');
        $arr['prgans'] = I('post.yxjgfs', 0, 'intval');
        $arr['prgfill'] = I('post.cxtkfs', 0, 'intval');
        $arr['programscore'] = I('post.cxfs', 0, 'intval');
        $arr['isvip'] = I('post.isvip', 'Y');
        $arr['isprivate'] = I('post.isprivate',0, 'intval');

        return $arr;
    }
}