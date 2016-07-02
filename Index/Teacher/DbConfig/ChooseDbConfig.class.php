<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:30
 */

namespace Teacher\DbConfig;


class ChooseDbConfig
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
        'point' => 'varchar',
        'addtime' => 'datetime',
        'creator' => 'varchar',
        'easycount' => 'tinyint',
        'isprivate' => 'tinyint'
    );
}