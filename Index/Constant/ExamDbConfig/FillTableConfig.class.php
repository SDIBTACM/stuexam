<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:30
 */

namespace Constant\ExamDbConfig;


class FillTableConfig
{
    const TABLE_NAME = "ex_fill";

    public static $TABLE_FIELD = array(
        'fill_id' => 'int',
        'question' => 'text',
        'answernum' => 'tinyint',
        'addtime' => 'datetime',
        'creator' => 'varchar',
        'easycount' => 'tinyint',
        'kind' => 'tinyint',
        'isprivate' => 'tinyint',
        'question_type' => 'tinyint',
        'private_code' => 'varchar',
    );
}
