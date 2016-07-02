<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:31
 */

namespace Teacher\DbConfig;


class JudgeDbConfig
{
    const TABLE_NAME = "ex_judge";

    public static $TABLE_FIELD = array(
        'judge_id' => 'int',
        'question' => 'text',
        'answer' => 'char',
        'point' => 'varchar',
        'addtime' => 'datetime',
        'creator' => 'varchar',
        'easycount' => 'tinyint',
        'isprivate' => 'tinyint'
    );
}