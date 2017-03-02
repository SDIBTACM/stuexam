<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/9/16 01:42
 */

namespace Teacher\Convert;


class ChooseConvert
{
    public static function convertChooseFromPost() {
        $arr = array();

        $arr['question'] = test_input($_POST['choose_des']);
        $arr['ams'] = test_input($_POST['ams']);
        $arr['bms'] = test_input($_POST['bms']);
        $arr['cms'] = test_input($_POST['cms']);
        $arr['dms'] = test_input($_POST['dms']);
        $arr['answer'] = $_POST['answer'];
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $arr['question_type'] = intval($_POST['questionType']);

        return $arr;
    }
}