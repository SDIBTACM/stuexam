<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 1/7/16 01:30
 */

namespace Teacher\Model;


use Constant\DbConfig\StudentDbConfig;

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
        return StudentDbConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return StudentDbConfig::$TABLE_FIELD;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function addStudentScore($data) {
        return $this->getDao()->add($data);
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

    public function submitExamPaper($userId, $examId, $scores) {
        $oldData = $this->getStudentScoreInfoByExamAndUserId($examId, $userId);
        if (empty($oldData)) {
            $scores['user_id'] = $userId;
            $scores['exam_id'] = $examId;
            $this->addStudentScore($scores);
        } else {
            $this->updateStudentScore($examId, $userId, $scores);
        }
    }
}