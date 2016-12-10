<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 20:34
 */

namespace Community\Model;

use Teacher\Model\GeneralModel;

class CollectionModel extends GeneralModel
{
    private static $_instance = null;

    const nodeCollectionType = 1;
    const topicCollectionType = 2;

    private function __construct() {
    }

    private function __clone() {
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    protected function getTableName() {
        return "collection";
    }

    protected function getTableFields() {
        // TODO: Implement getTableFields() method.
    }

    protected function getPrimaryId() {
        // TODO: Implement getPrimaryId() method.
    }

    public function isCollected($uid, $followId, $type) {
        $where = array(
            'uid' => $uid,
            'follow_id' => $followId,
            'type' => $type
        );
        $flag = $this->queryOne($where, array('id'));
        if (empty($flag)) {
            return false;
        } else {
            return true;
        }
    }

    public function collect($uid, $followId, $type) {
        $data = array(
            'uid' => $uid,
            'follow_id' => $followId,
            'type' => $type
        );
        return $this->insertData($data);
    }

    public function cancelCollect($uid, $followId, $type) {
        $where = array(
            'uid' => $uid,
            'follow_id' => $followId,
            'type' => $type
        );
        $this->getDao()->where($where)->delete();
    }
}