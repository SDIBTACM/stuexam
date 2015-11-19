<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 15/11/18 23:58
 */

namespace Teacher\Model;


class PrivilegeBaseModel extends GeneralModel
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
        return DbConfigModel::TABLE_EX_PRIVILEGE;
    }

    protected function getTableFields() {
        return DbConfigModel::$TABLE_EX_PRIVILEGE_FILEDS;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function insertPrivilege($privilege) {
        $dao = $this->getDao();
        return $dao->add($privilege);
    }

    public function insertPrivileges($privileges) {
        $dao = $this->getDao();
        return $dao->addAll($privileges);
    }

    public function getUsersByExamId($eid, $field = array()) {
        $dao = $this->getDao();
        $where = array(
            'rightstr' => "e$eid"
        );
        $users = $dao->field($field)->where($where)->select();
        return $users;
    }

    public function getPrivilegeByUserIdAndExamId($userId, $eid, $field = array()) {
        $dao = $this->getDao();
        $where = array(
            'user_id' => $userId,
            'rightstr' => "e$eid"
        );
        $privilege = $dao->field($field)->where($where)->find();
        return $privilege;
    }

    public function updatePrivilegeByUserIdAndExamId($userId, $eid, $data) {
        $dao = $this->getDao();
        $where = array(
            'user_id' => $userId,
            'rightstr' => "e$eid"
        );
        return $dao->where($where)->data($data)->save();
    }

    public function getTakeInExamUsersByExamId($eid) {
        $dao = $this->getDao();
        $where = array(
            'rightstr' => "e$eid",
            'extrainfo' => array('neq', 0)
        );
        $field = array('user_id');
        return $dao->field($field)->where($where)->select();
    }
}