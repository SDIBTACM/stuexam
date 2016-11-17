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

    protected function getDao() {
        return M($this->getTableName());
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
        return $this->getDao()->select();
    }

    public function getParentNodeByChapterId($chapterId) {
        $where = array(
            'chapter_id' => $chapterId,
            'parent_id' => 0
        );
        return $this->queryAll($where);
    }

    public function getByParentId($parentId, $field = array()) {
        $where = array(
            'parent_id' => $parentId
        );
        return $this->queryAll($where, $field);
    }

    public function getByChapterId($chapterId, $field = array()) {
        $where = array(
            'chapter_id' => $chapterId
        );
        return $this->queryAll($where, $field);
    }
}