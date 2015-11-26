<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 15/11/26 22:56
 */

namespace Teacher\Model;


class QuestionBaseModel extends GeneralModel
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
        return DbConfigModel::TABLE_EXP_QUESTION;
    }

    protected function getTableFields() {
        return DbConfigModel::$TABLE_EXP_QUESTION_FILEDS;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getQuestionIds4ExamByType($eid, $type) {
        $questionDao = $this->getDao();
        $where = array(
            'exam_id' => $eid,
            'type' => $type
        );
        $field = array('question_id');
        $res = $questionDao->field($field)->where($where)->select();
        return $res;
    }
}