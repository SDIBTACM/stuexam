<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/10/25 19:09
 */

namespace Teacher\Model;


class ExamModel extends GeneralModel
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
        return DbConfigModel::TABLE_EXAM;
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

    public function getExamInfoByQuery($query, $field = array()) {
        $where = array();
        $dao = $this->getDao();

        if (!empty($query['exam_id'])) {
            $where['exam_id'] = $query['exam_id'];
        }
        if (!empty($query['isprivate'])) {
            $where['isprivate'] = $query['isprivate'];
        }
        if (!empty($query['isvip'])) {
            $where['isvip'] = $query['isvip'];
        }

        $dao = $dao->field($field)->where($where);

        if (!empty($query['order']) && is_array($query['order'])) {
            $dao->order($query['order']);
        }

        if (!empty($query['limit'])) {
            $dao->limit($query['limit']);
        }

        $res = $dao->select();
        return $res;
    }
}
