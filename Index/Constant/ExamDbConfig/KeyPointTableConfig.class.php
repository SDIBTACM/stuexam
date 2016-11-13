<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 12/11/2016 15:14
 */

namespace Constant\ExamDbConfig;


class KeyPointTableConfig
{
    const TABLE_NAME = "ex_key_point";

    public static $TABLE_FIELD = array(
        'id' => 'int',
        'chapter_id' => 'tinyint',
        'name' => 'varchar',
        'parent_id' => 'int'
    );
}