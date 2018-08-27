<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 15/11/26 22:56
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\QuestionTableConfig;

class QuestionBaseModel extends GeneralModel
{
    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getTableName() {
        return QuestionTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return QuestionTableConfig::$TABLE_FIELD;
    }

    protected function getPrimaryId() {
        return 'exp_qid';
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function insertQuestions($data) {
        return $this->getDao()->addAll($data);
    }

    public function getQuestionByExamId($eid, $field = array()) {
        $where = array(
            'exam_id' => $eid
        );
        return $this->queryAll($where, $field);
    }

    public function getQuestionIds4ExamByType($eid, $type) {
        $where = array(
            'exam_id' => $eid,
            'type' => $type
        );
        $field = array('question_id');
        return $this->queryAll($where, $field, array('exp_qid asc'));
    }

    public function getQuestionCntByType($eid, $type) {
        $where = array(
            'exam_id' => $eid,
            'type' => $type
        );
        $field = array('question_id');
        return $this->getDao()->field($field)->where($where)->count();
    }

    public function delQuestionByType($questionId, $type) {
        $where = array(
            'question_id' => $questionId,
            'type' => $type
        );
        return $this->getDao()->where($where)->delete();
    }
}