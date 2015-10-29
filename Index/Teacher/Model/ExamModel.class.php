<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/10/25 19:09
 */

namespace Teacher\Model;


class ExamModel {

    private static $_instance = null;

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

    public function getExamInfoById($eid, $field = array()) {
        $examDao = M('exam');
        $where = array(
            'exam_id' => $eid,
            'visible' => 'Y'
            );
        $result = $examDao->field($field)->where($where)->find();
        return $result;
    }

    public function updateExamInfoById($eid, $data) {
        $examDao = M('exam');
        $where = array('exam_id' => $eid);
        return $examDao->data($data)->where($where)->save();
    }

    public function addExamBaseInfo($data) {
        $examDao = M('exam');
        $return = $examDao->add($data);
        return $return;
    }
}
