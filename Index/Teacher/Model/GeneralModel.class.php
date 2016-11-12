<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:26
 */

namespace Teacher\Model;


abstract class GeneralModel extends BasicBaseModel
{

    public function queryOne($where, $field = array()) {
        if (empty($where)) {
            return null;
        }
        return $this->getDao()->field($field)->where($where)->find();
    }

    public function queryAll($where, $field = array(), $order = array()) {
        if (empty($where)) {
            return array();
        }
        if (empty($order)) {
            return $this->getDao()->field($field)->where($where)->select();
        } else {
            return $this->getDao()->field($field)->where($where)->order($order)->select();
        }
    }

    public function queryData($query, $field = array()) {
        $where = array();
        $dao = $this->getDao();
        $tableFields = $this->getTableFields();
        foreach ($query as $k => $v) {
            if (isset($tableFields[$k])) {
                $where[$k] = $query[$k];
            }
            if ($k == "_logic" || $k == "_complex") {
                $where[$k] = $query[$k];
            }
        }

        $dao = $dao->field($field)->where($where);

        if (!empty($query['group'])) {
            $dao->group($query['group']);
        }

        if (!empty($query['order'])) {
            $dao->order($query['order']);
        }

        if (!empty($query['limit'])) {
            $dao->limit($query['limit']);
        }
        $res = $dao->select();
        return $res;
    }
}