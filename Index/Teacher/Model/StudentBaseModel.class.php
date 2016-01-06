<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 1/7/16 01:30
 */

namespace Teacher\Model;


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
        return DbConfigModel::TABLE_EX_STUDENT;
    }

    protected function getTableFields() {
        return DbConfigModel::$TABLE_EX_STUDENT_FILEDS;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function addStudentScore($data) {
        $dao = $this->getDao();
        $return = $dao->add($data);
        return $return;
    }

    public function updateStudentScore($examId, $userId, $data) {
        $dao = $this->getDao();
        $where = array(
            'exam_id' => $examId,
            'user_id' => $userId
        );
        return $dao->data($data)->where($where)->save();
    }

    public function getStudentScoreInfoByExamAndUserId($examId, $userId) {
        $where = array(
            'exam_id' => $examId,
            'user_id' => $userId
        );
        $dao = $this->getDao();
        $res = $dao->where($where)->find();
        return $res;
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