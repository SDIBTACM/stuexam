<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/9/16 01:43
 */

namespace Teacher\Convert;


class JudgeConvert
{
    public static function convertJudgeFromPost() {
        $arr = array();

        $arr['question'] = test_input($_POST['judge_des']);
        $arr['answer'] = $_POST['answer'];
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $arr['question_type'] = intval($_POST['questionType']);
        $arr['private_num'] = intval($_POST['private_num']) == 0 ? null : intval($_POST['private_num']);

        return $arr;
    }
}