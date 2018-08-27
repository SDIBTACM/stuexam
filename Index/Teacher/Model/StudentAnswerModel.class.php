<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 27/08/2018 21:39
 */

namespace Teacher\Model;


use Constant\ExamDbConfig\StudentAnswerTableConfig;

class StudentAnswerModel extends GeneralModel
{

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getTableName() {
        return StudentAnswerTableConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return StudentAnswerTableConfig::$TABLE_FIELD;
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

    public function delAnswerByQuestionAndType($questionId, $type) {
        $where = array(
            'question_id' => $questionId,
            'type' => $type
        );
        return $this->getDao()->where($where)->delete();
    }
}