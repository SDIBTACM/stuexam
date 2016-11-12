<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 1/7/16 01:30
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\StudentTableConfig;

class StudentBaseModel extends GeneralModel
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
        return StudentTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return StudentTableConfig::$TABLE_FIELD;
    }

    protected function getPrimaryId() {
        return null;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function updateStudentScore($examId, $userId, $data) {
        $where = array(
            'exam_id' => $examId,
            'user_id' => $userId
        );
        return $this->getDao()->data($data)->where($where)->save();
    }

    public function getStudentScoreInfoByExamAndUserId($examId, $userId) {
        $where = array(
            'exam_id' => $examId,
            'user_id' => $userId
        );
        return $this->queryOne($where);
    }
}