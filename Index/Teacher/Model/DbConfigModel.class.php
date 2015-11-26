<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:31
 */

namespace Teacher\Model;


class DbConfigModel
{

    const TABLE_CHOOSE = 'ex_choose';
    public static $TABLE_CHOOSE_FILEDS = array(
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

    const TABLE_JUDGE = 'ex_judge';
    public static $TABLE_JUDGE_FILEDS = array(
        'judge_id' => 'int',
        'question' => 'text',
        'answer' => 'char',
        'point' => 'varchar',
        'addtime' => 'datetime',
        'creator' => 'varchar',
        'easycount' => 'tinyint',
        'isprivate' => 'tinyint'
    );

    const TABLE_FILL = 'ex_fill';
    public static $TABLE_FILL_FILEDS = array(
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

    const TABLE_EXAM = 'exam';
    public static $TABLE_EXAM_FILEDS = array(
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

    const TABLE_EX_PRIVILEGE = 'ex_privilege';
    public static $TABLE_EX_PRIVILEGE_FILEDS = array(
        'user_id' => 'varchar',
        'rightstr' => 'varchar',
        'randnum' => 'int',
        'extrainfo' => 'int'
    );

    const TABLE_EXP_QUESTION = 'exp_question';
    public static $TABLE_EXP_QUESTION_FILEDS = array(
        'exam_id' => 'int',
        'question_id' => 'int',
        'type' => 'tinyint'
    );
}