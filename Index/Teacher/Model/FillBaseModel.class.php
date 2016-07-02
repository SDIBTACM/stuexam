<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:01
 */

namespace Teacher\Model;


use Teacher\DbConfig\FillDbConfig;

class FillBaseModel extends GeneralModel
{

    const FILL_PROBLEM_TYPE = 3;

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return FillDbConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return FillDbConfig::$TABLE_FIELD;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getFillById($fillId, $field = array()) {
        $where = array(
            'fill_id' => $fillId
        );
        return $this->queryOne($where, $field);
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

    public function getFillProblems4Exam($eid) {
        $type = self::FILL_PROBLEM_TYPE;
        $sql = "SELECT `ex_fill`.`fill_id`,`question`,`answernum`,`kind` FROM `ex_fill`,`exp_question`
		WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_fill`.`fill_id`=`exp_question`.`question_id` ORDER BY `fill_id`";
        $ans = M()->query($sql);
        return $ans;
    }

    public function getFillAnswerByFillId($fillid) {
        $ans = M('fill_answer')
            ->field('answer_id,answer')
            ->where('fill_id=%d', $fillid)
            ->order('answer_id')
            ->select();
        return $ans;
    }
}