<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:00
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\JudgeTableConfig;
use Home\Helper\SqlExecuteHelper;

class JudgeBaseModel extends GeneralModel
{

    const JUDGE_PROBLEM_TYPE = 2;
    const JUDGE_PROBLEM_NAME = "判断题";

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getTableName() {
        return JudgeTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return JudgeTableConfig::$TABLE_FIELD;
    }

    protected function getPrimaryId() {
        return 'judge_id';
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 获取某场考试下的判断题的题目和答案
     * @param $eid
     * @return mixed
     */
    public function getJudgeProblems4Exam($eid) {
        return SqlExecuteHelper::Teacher_GetJudgeProblem4Exam($eid, self::JUDGE_PROBLEM_TYPE);
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
