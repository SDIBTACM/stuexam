<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 27/08/2018 21:37
 */

namespace Constant\ExamDbConfig;


class StudentAnswerTableConfig
{
    const TABLE_NAME = "ex_stuanswer";

    public static $TABLE_FIELD = array(
        'user_id' => 'varchar',
        'exam_id' => 'int',
        'type' => 'tinyint',
        'question_id' => 'int',
        'answer_id' => 'int',
        'answer' => 'varchar'
    );
}