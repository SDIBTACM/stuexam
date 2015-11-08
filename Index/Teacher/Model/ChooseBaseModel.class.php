<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:00
 */

namespace Teacher\Model;


class ChooseBaseModel extends GeneralModel
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
        return DbConfigModel::TABLE_CHOOSE;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getChooseById($chooseId, $field = array()) {
        $dao = $this->getDao();
        $where = array(
            'choose_id' => $chooseId
        );
        $res = $dao->field($field)->where($where)->find();
        return $res;
    }

    public function updateChooseById($chooseId, $data) {
        $dao = $this->getDao();
        $where = array(
            'choose_id' => $chooseId
        );
        $res = $dao->where($where)->data($data)->save();
        return $res;
    }

    public function insertChooseInfo($data) {
        $dao = $this->getDao();
        $lastId = $dao->add($data);
        return $lastId;
    }

    public function delChooseById($chooseId) {
        $dao = $this->getDao();
        $where = array(
            'choose_id' => $chooseId
        );
        $res = $dao->where($where)->delete();
        return $res;
    }
}