<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/10/25 19:09
 */

namespace Teacher\Model;


use Teacher\DbConfig\ExamDbConfig;

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
        return ExamDbConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return ExamDbConfig::$TABLE_FIELD;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getExamInfoById($examId, $field = array()) {
        $examDao = $this->getDao();
        $where = array(
            'exam_id' => $examId,
            'visible' => 'Y'
        );
        $result = $examDao->field($field)->where($where)->find();
        return $result;
    }

    public function updateExamInfoById($examId, $data) {
        $examDao = $this->getDao();
        $where = array(
            'exam_id' => $examId
        );
        return $examDao->data($data)->where($where)->save();
    }

    public function addExamBaseInfo($data) {
        $examDao = $this->getDao();
        $return = $examDao->add($data);
        return $return;
    }

    public function delExamById($examId) {
        $dao = $this->getDao();
        $where = array(
            'exam_id' => $examId
        );
        $res = $dao->where($where)->delete();
        return $res;
    }
}
