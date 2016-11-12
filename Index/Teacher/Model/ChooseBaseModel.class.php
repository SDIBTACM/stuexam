<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:00
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\ChooseTableConfig;

class ChooseBaseModel extends GeneralModel
{

    const CHOOSE_PROBLEM_TYPE = 1;
    const CHOOSE_PROBLEM_NAME = "选择题";

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return ChooseTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return ChooseTableConfig::$TABLE_FIELD;
    }

    protected function getPrimaryId() {
        return 'choose_id';
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 获取某场考试下的选择题的所有题目和答案
     * @param $eid
     * @return mixed
     */
    public function getChooseProblems4Exam($eid) {
        $type = self::CHOOSE_PROBLEM_TYPE;
        $sql = "SELECT `ex_choose`.`choose_id`,`question`,`ams`,`bms`,`cms`,`dms`,`answer`".
                " FROM `ex_choose`,`exp_question`".
		        " WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_choose`.`choose_id`=`exp_question`.`question_id`".
                " ORDER BY `choose_id`";
        $ans = M()->query($sql);
        return $ans;
    }
}