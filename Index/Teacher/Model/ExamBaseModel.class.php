<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/10/25 19:09
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\ExamTableConfig;

class ExamBaseModel extends GeneralModel
{

    const EXAM_NOT_START = 0;
    const EXAM_RUNNING = 1;
    const EXAM_END = -1;

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return ExamTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return ExamTableConfig::$TABLE_FIELD;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getExamInfoById($examId, $field = array()) {
        $where = array(
            'exam_id' => $examId,
            'visible' => 'Y'
        );
        return $this->getDao()->field($field)->where($where)->find();
    }

    public function updateExamInfoById($examId, $data) {
        $where = array(
            'exam_id' => $examId
        );
        return $this->getDao()->data($data)->where($where)->save();
    }

    public function addExamBaseInfo($data) {
        return $this->getDao()->add($data);
    }

    public function delExamById($examId) {
        $where = array(
            'exam_id' => $examId
        );
        return $this->getDao()->where($where)->delete();
    }
}
