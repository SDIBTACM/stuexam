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

    public function getByChapterId($chapterId, $field = array()) {
        $where = array(
            'chapter_id' => $chapterId
        );
        return $this->queryAll($where, $field);
    }
}