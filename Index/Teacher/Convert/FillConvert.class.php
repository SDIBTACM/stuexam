<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/9/16 01:43
 */

namespace Teacher\Convert;


class FillConvert
{
    public static function convertFillFromPost() {
        $arr = array();

        $arr['question'] = test_input($_POST['fill_des']);
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['answernum'] = intval($_POST['numanswer']);
        $arr['kind'] = intval($_POST['kind']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $arr['question_type'] = intval($_POST['questionType']);
        $arr['private_code'] = test_input($_POST['private_code']);

        return $arr;
    }
}