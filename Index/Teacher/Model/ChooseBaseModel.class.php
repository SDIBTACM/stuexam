<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:00
 */

namespace Teacher\Model;


class ChooseBaseModel extends GeneralModel
{

    const CHOOSE_PROBLEM_TYPE = 1;

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return DbConfigModel::TABLE_CHOOSE;
    }

    protected function getTableFields() {
        return DbConfigModel::$TABLE_CHOOSE_FILEDS;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getChooseById($chooseId, $field = array()) {
        $dao = $this->getDao();
        $where = array(
            'choose_id' => $chooseId
        );
        $res = $dao->field($field)->where($where)->find();
        return $res;
    }

    public function updateChooseById($chooseId, $data) {
        $dao = $this->getDao();
        $where = array(
            'choose_id' => $chooseId
        );
        $res = $dao->where($where)->data($data)->save();
        return $res;
    }

    public function insertChooseInfo($data) {
        $dao = $this->getDao();
        $lastId = $dao->add($data);
        return $lastId;
    }

    public function delChooseById($chooseId) {
        $dao = $this->getDao();
        $where = array(
            'choose_id' => $chooseId
        );
        $res = $dao->where($where)->delete();
        return $res;
    }

    public function getChooseProblems4Exam($eid) {
        $type = self::CHOOSE_PROBLEM_TYPE;
        $sql = "SELECT `ex_choose`.`choose_id`,`question`,`ams`,`bms`,`cms`,`dms`,`answer` FROM `ex_choose`,`exp_question`
		WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_choose`.`choose_id`=`exp_question`.`question_id` ORDER BY `choose_id`";
        $ans = M()->query($sql);
        return $ans;
    }
}