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
        $syear = intval($_POST['syear']) <= 0 ? 1970 : intval($_POST['syear']);
        $eyear = intval($_POST['eyear']) <= 0 ? 2035 : intval($_POST['eyear']);

        $smonth = intval($_POST['smonth']) <= 0 ? 1 : intval($_POST['smonth']);
        $emonth = intval($_POST['emonth']) <= 0 ? 1 : intval($_POST['emonth']);

        $sday = intval($_POST['sday']) <= 0 ? 1 : intval($_POST['sday']);
        $eday = intval($_POST['eday']) <= 0 ? 1 : intval($_POST['eday']);

        $shour = intval($_POST['shour']) <= 0 ? 8 : intval($_POST['shour']);
        $ehour = intval($_POST['ehour']) <= 0 ? 8 : intval($_POST['ehour']);

        $arr['start_time'] = $syear . "-" . $smonth . "-" . $sday . " " . $shour . ":" . intval($_POST['sminute']) . ":00";
        $arr['end_time'] = $eyear. "-" . $emonth . "-" . $eday . " " . $ehour . ":" . intval($_POST['eminute']) . ":00";
        $title = I('post.examname', '');
        if (get_magic_quotes_gpc()) {
            $title = stripslashes($title);
        }
        $arr['title'] = $title;
        $arr['choosescore'] = I('post.xzfs', 0, 'formatToFloatScore');
        $arr['judgescore'] = I('post.pdfs', 0, 'formatToFloatScore');
        $arr['fillscore'] = I('post.tkfs', 0, 'formatToFloatScore');
        $arr['prgans'] = I('post.yxjgfs', 0, 'formatToFloatScore');
        $arr['prgfill'] = I('post.cxtkfs', 0, 'formatToFloatScore');
        $arr['programscore'] = I('post.cxfs', 0, 'formatToFloatScore');
        $arr['isvip'] = I('post.isvip', 'Y');
        $arr['isprivate'] = I('post.isprivate',0, 'intval');

        return $arr;
    }

    /**
     * 生成一个默认的考试配置, 为自动生成试卷使用, 绑定生成的题目
     */
    public static function generateDefaultExamConfig() {
        $arr = array();

        $arr['title'] = "自动生成试卷占位符";
        $arr['start_time'] = "1970-01-01 09:00:00";
        $arr['end_time'] = "1970-01-02 12:00:00";
        $arr['creator'] = $_SESSION['user_id'];
        $arr['choosescore'] = 1;
        $arr['judgescore'] = 1;
        $arr['fillscore'] = 1;
        $arr['prgans'] = 1;
        $arr['prgfill'] = 1;
        $arr['programscore'] = 10;
        $arr['isvip'] = 'Y';
        $arr['visible'] = 'Y';
        $arr['isprivate'] = 1;

        return $arr;
    }
}
