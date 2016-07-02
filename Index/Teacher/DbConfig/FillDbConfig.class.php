<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:30
 */

namespace Teacher\DbConfig;


class FillDbConfig
{
    const TABLE_NAME = "ex_fill";

    public static $TABLE_FIELD = array(
        'fill_id' => 'int',
        'question' => 'text',
        'answernum' => 'tinyint',
        'point' => 'varchar',
        'addtime' => 'datetime',
        'creator' => 'varchar',
        'easycount' => 'tinyint',
        'kind' => 'tinyint',
        'isprivate' => 'tinyint'
    );
}