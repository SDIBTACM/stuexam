<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 15/11/18 23:58
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\PrivilegeTableConfig;

class PrivilegeBaseModel extends GeneralModel
{

    const PROBLEM_PUBLIC = 0;  // 公共题库
    const PROBLEM_PRIVATE = 1; // 私有题库
    const PROBLEM_SYSTEM = 2;  // 隐藏题库

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return PrivilegeTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return PrivilegeTableConfig::$TABLE_FIELD;
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

    public function insertPrivileges($privileges) {
        return $this->getDao()->addAll($privileges);
    }

    public function getUsersByExamId($eid, $field = array()) {
        $where = array(
            'rightstr' => "e$eid"
        );
        return $this->queryAll($where, $field);
    }

    public function getPrivilegeByUserIdAndExamId($userId, $eid, $field = array()) {
        $where = array(
            'user_id' => $userId,
            'rightstr' => "e$eid"
        );
        return $this->queryOne($where, $field);
    }

    public function updatePrivilegeByUserIdAndExamId($userId, $eid, $data) {
        $where = array(
            'user_id' => $userId,
            'rightstr' => "e$eid"
        );
        return $this->getDao()->where($where)->data($data)->save();
    }

    /**
     * 获取某场考试下所有参与考试的学生,缺考的除外
     * @param $eid
     * @return mixed
     */
    public function getTakeInExamUsersByExamId($eid) {
        $where = array(
            'rightstr' => "e$eid",
            'extrainfo' => array('neq', 0)
        );
        $field = array('user_id');
        return $this->queryAll($where, $field);
    }
}