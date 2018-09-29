<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 12/11/2016 15:12
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\KeyPointTableConfig;

class KeyPointBaseModel extends GeneralModel
{
    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getTableName() {
        return KeyPointTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return KeyPointTableConfig::$TABLE_FIELD;
    }

    protected function getPrimaryId() {
        return 'id';
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function delByParentId($parentId) {
        if ($parentId == 0) return 0;
        $where = array(
            'parent_id' => $parentId
        );
        return $this->getDao()->where($where)->delete();
    }

    public function getAllPoint() {
        return $this->getDao()->order(array("id asc"))->select();
    }

    public function getByIds($ids) {
        if (empty($ids)) {
            return array();
        }
        $where = array(
            'id' => array('in', $ids)
        );
        return $this->queryAll($where);
    }

    public function getParentNodeByChapterId($chapterId) {
        if (intval($chapterId) <= 0) {
            return array();
        }
        $where = array(
            'chapter_id' => intval($chapterId),
            'parent_id' => 0
        );
        return $this->queryAll($where);
    }

    public function getChildrenNodeByParentId($parentId, $field = array()) {
        if (intval($parentId) <= 0) {
            return array();
        }
        $where = array(
            'parent_id' => intval($parentId)
        );
        return $this->queryAll($where, $field);
    }
}
