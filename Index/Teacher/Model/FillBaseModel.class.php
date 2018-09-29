<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:01
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\FillTableConfig;
use Home\Helper\SqlExecuteHelper;

class FillBaseModel extends GeneralModel
{

    const FILL_PROBLEM_TYPE = 3;
    const FILL_PROBLEM_NAME = "填空题";

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getTableName() {
        return FillTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return FillTableConfig::$TABLE_FIELD;
    }

    protected function getPrimaryId() {
        return 'fill_id';
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getFillProblems4Exam($eid) {
        return SqlExecuteHelper::Teacher_GetFillProblem4Exam($eid, self::FILL_PROBLEM_TYPE);
    }

    public function getFillAnswerByFillId($fillId) {
        $ans = M('fill_answer')
            ->field('answer_id,answer')
            ->where('fill_id=%d', $fillId)
            ->order('answer_id')
            ->select();
        return $ans;
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
