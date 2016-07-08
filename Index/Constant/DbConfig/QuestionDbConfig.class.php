<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:35
 */

namespace Constant\DbConfig;


class QuestionDbConfig
{
    const TABLE_NAME = "exp_question";

    public static $TABLE_FIELD = array(
        'exam_id' => 'int',
        'question_id' => 'int',
        'type' => 'tinyint'
    );
}