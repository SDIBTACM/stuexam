<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:35
 */

namespace Constant\ExamDbConfig;


class QuestionTableConfig
{
    const TABLE_NAME = "exp_question";

    public static $TABLE_FIELD = array(
        'exp_qid' => 'int',
        'exam_id' => 'int',
        'question_id' => 'int',
        'type' => 'tinyint'
    );
}