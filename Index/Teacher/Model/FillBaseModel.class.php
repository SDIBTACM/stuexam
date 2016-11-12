<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:01
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\FillTableConfig;

class FillBaseModel extends GeneralModel
{

    const FILL_PROBLEM_TYPE = 3;
    const FILL_PROBLEM_NAME = "填空题";

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
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
        $type = self::FILL_PROBLEM_TYPE;
        $sql = "SELECT `ex_fill`.`fill_id`,`question`,`answernum`,`kind`" .
                " FROM `ex_fill`,`exp_question`" .
		        " WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_fill`.`fill_id`=`exp_question`.`question_id`" .
                " ORDER BY `fill_id`";
        $ans = M()->query($sql);
        return $ans;
    }

    public function getFillAnswerByFillId($fillId) {
        $ans = M('fill_answer')
            ->field('answer_id,answer')
            ->where('fill_id=%d', $fillId)
            ->order('answer_id')
            ->select();
        return $ans;
    }
}