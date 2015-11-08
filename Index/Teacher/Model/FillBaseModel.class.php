<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:01
 */

namespace Teacher\Model;


class FillBaseModel extends GeneralModel
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
        return DbConfigModel::TABLE_FILL;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getFillById($fillId, $field = array()) {
        $dao = $this->getDao();
        $where = array(
            'fill_id' => $fillId
        );
        $res = $dao->field($field)->where($where)->find();
        return $res;
    }

    public function updateFillById($fillId, $data) {
        $dao = $this->getDao();
        $where = array(
            'fill_id' => $fillId
        );
        $res = $dao->where($where)->data($data)->save();
        return $res;
    }

    public function insertFillInfo($data) {
        $dao = $this->getDao();
        $lastId = $dao->add($data);
        return $lastId;
    }

    public function delFillById($fillId) {
        $dao = $this->getDao();
        $where = array(
            'fill_id' => $fillId
        );
        $res = $dao->where($where)->delete();
        return $res;
    }

}