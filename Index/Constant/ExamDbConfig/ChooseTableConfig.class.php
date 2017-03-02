<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:30
 */

namespace Constant\ExamDbConfig;


class ChooseTableConfig
{
    const TABLE_NAME = "ex_choose";

    public static $TABLE_FIELD = array(
        'choose_id' => 'int',
        'question' => 'text',
        'ams' => 'varchar',
        'bms' => 'varchar',
        'cms' => 'varchar',
        'dms' => 'varchar',
        'answer' => 'char',
        'addtime' => 'datetime',
        'creator' => 'varchar',
        'easycount' => 'tinyint',
        'isprivate' => 'tinyint',
        'question_type' => 'tinyint',
    );
}