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

        $arr['question'] = encodeInputWithImage($_POST['choose_des']);
        $arr['ams'] = encodeInputWithImage($_POST['ams']);
        $arr['bms'] = encodeInputWithImage($_POST['bms']);
        $arr['cms'] = encodeInputWithImage($_POST['cms']);
        $arr['dms'] = encodeInputWithImage($_POST['dms']);
        $arr['answer'] = $_POST['answer'];
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $arr['question_type'] = intval($_POST['questionType']);
        $arr['private_code'] = test_input($_POST['private_code']);

        return $arr;
    }
}
