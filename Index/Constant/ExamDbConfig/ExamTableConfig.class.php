<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/3/16 02:30
 */

namespace Constant\ExamDbConfig;


class ExamTableConfig
{
    const TABLE_NAME = "exam";

    public static $TABLE_FIELD = array(
        'exam_id' => 'int',
        'title' => 'varchar',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'creator' => 'varchar',
        'choosescore' => 'tinyint',
        'judgescore' => 'tinyint',
        'fillscore' => 'tinyint',
        'prgans' => 'tinyint',
        'prgfill' => 'tinyint',
        'programscore' => 'tinyint',
        'isvip' => 'char',
        'visible' => 'char',
        'isprivate' => 'tinyint'
    );
}