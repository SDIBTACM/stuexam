<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 12/11/2016 15:46
 */

namespace Teacher\Model;


abstract class BasicBaseModel
{

    abstract protected function getTableName();

    abstract protected function getTableFields();

    abstract protected function getPrimaryId();

    protected function getDao() {
        return M($this->getTableName());
    }

    public function insertData($data) {
        if (empty($data)) return 0;
        return $this->getDao()->add($data);
    }

    public function delById($id) {
        $key = $this->getPrimaryId();
        if ($key == null) {
            return 0;
        }
        $where = array(
            $key => $id
        );
        return $this->getDao()->where($where)->limit('1')->delete();
    }

    public function getById($id, $field = array()) {
        $key = $this->getPrimaryId();
        if ($key == null || empty($id)) {
            return null;
        }
        $where = array(
            $key => $id
        );
        return $this->getDao()->field($field)->where($where)->find();
    }

    public function updateById($id, $data) {
        $key = $this->getPrimaryId();
        if ($key == null || empty($id) || empty($data)) {
            return null;
        }
        $where = array(
            $key => $id
        );
        return $this->getDao()->where($where)->data($data)->save();
    }
}