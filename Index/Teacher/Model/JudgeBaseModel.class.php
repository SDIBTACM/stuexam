<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:00
 */

namespace Teacher\Model;


use Constant\DbConfig\JudgeDbConfig;

class JudgeBaseModel extends GeneralModel
{

    const JUDGE_PROBLEM_TYPE = 2;
    const JUDGE_PROBLEM_NAME = "判断题";

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return JudgeDbConfig::TABLE_NAME;
    }

    protected function getTableFields() {
        return JudgeDbConfig::$TABLE_FIELD;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getJudgeById($judgeId, $field = array()) {
        $where = array(
            'judge_id' => $judgeId
        );
        return $this->queryOne($where, $field);
    }

    public function updateJudgeById($judgeId, $data) {
        $where = array(
            'judge_id' => $judgeId
        );
        return $this->getDao()->where($where)->data($data)->save();
    }

    public function insertJudgeInfo($data) {
        return $this->getDao()->add($data);
    }

    public function delJudgeById($judgeId) {
        $where = array(
            'judge_id' => $judgeId
        );
        return $this->getDao()->where($where)->delete();
    }

    /**
     * 获取某场考试下的判断题的题目和答案
     * @param $eid
     * @return mixed
     */
    public function getJudgeProblems4Exam($eid) {
        $type = self::JUDGE_PROBLEM_TYPE;
        $sql = "SELECT `ex_judge`.`judge_id`,`question`,`answer` FROM `ex_judge`,`exp_question`
		WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_judge`.`judge_id`=`exp_question`.`question_id` ORDER BY `judge_id`";
        $ans = M()->query($sql);
        return $ans;
    }
}