<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 13/11/2016 20:17
 */

namespace Constant\ExamDbConfig;


class QuestionPointTableConfig
{
    const TABLE_NAME = "ex_question_point";

    public static $TABLE_FIELD = array(
        'id' => 'int',
        'question_id' => 'int',
        'type' => 'tinyint',
        'chapter_id' => 'tinyint',
        'point_id' => 'int',
        'point_parent_id' => 'int'
    );
}