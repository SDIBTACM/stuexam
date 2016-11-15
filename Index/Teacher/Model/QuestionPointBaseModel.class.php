<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 13/11/2016 20:16
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\QuestionPointTableConfig;

class QuestionPointBaseModel extends GeneralModel
{
    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return QuestionPointTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return QuestionPointTableConfig::$TABLE_FIELD;
    }

    protected function getPrimaryId() {
        return 'id';
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function insertDataList($dataList) {
        if (empty($dataList)) return 0;
        return $this->getDao()->addAll($dataList);
    }

    public function delByQuestion($questionId, $type) {
        $where = array(
            'question_id' => $questionId,
            'type' => $type
        );
        return $this->getDao()->where($where)->delete();
    }

    public function getQuestionPoint($questionId, $type) {
        $where = array(
            'question_id' => $questionId,
            'type' => $type
        );
        return $this->queryAll($where, array('chapter_id', 'point_id'));
    }

    public function getQuestionsPoint($questionIds, $type) {
        if (empty($questionIds)) return array();

        $where = array(
            'question_id' => array('in', $questionIds),
            'type' => $type
        );
        return $this->queryAll($where, array(), array('id asc'));
    }

    public function delPoint($pointId) {
        if ($pointId == 0) return 1;
        $where = array('point_id' => $pointId);
        $this->getDao()->where($where)->delete();
        $where = array('point_parent_id' => $pointId);
        return $this->getDao()->where($where)->delete();
    }
}