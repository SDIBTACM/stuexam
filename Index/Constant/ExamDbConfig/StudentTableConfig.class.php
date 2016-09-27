<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:36
 */

namespace Constant\ExamDbConfig;


class StudentTableConfig
{
    const TABLE_NAME = "ex_student";

    public static $TABLE_FIELD = array(
        'user_id' => 'varchar',
        'exam_id' => 'int',
        'score' => 'int',
        'choosesum' => 'int',
        'judgesum' => 'int',
        'fillsum' => 'int',
        'programsum' => 'int'
    );
}