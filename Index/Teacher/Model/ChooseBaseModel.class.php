<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:00
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\ChooseTableConfig;
use Home\Helper\SqlExecuteHelper;

class ChooseBaseModel extends GeneralModel
{

    const CHOOSE_PROBLEM_TYPE = 1;
    const CHOOSE_PROBLEM_NAME = "选择题";

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
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
        return SqlExecuteHelper::Teacher_GetChooseProblem4Exam($eid, self::CHOOSE_PROBLEM_TYPE);
    }

    public function getByPrivateCode($privateCode) {
        if (empty($privateCode)) {
            return array();
        }

        $where = array(
            'private_code' => $privateCode
        );

        return $this->queryOne($where);
    }
}
