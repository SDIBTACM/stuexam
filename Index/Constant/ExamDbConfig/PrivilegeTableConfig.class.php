<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:34
 */

namespace Constant\ExamDbConfig;


class PrivilegeTableConfig
{
    const TABLE_NAME = "ex_privilege";

    public static $TABLE_FIELD = array(
        'user_id' => 'varchar',
        'rightstr' => 'varchar',
        'randnum' => 'int',
        'extrainfo' => 'int'
    );
}