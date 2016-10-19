<?php
namespace Teacher\Service;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;

class ProblemService
{
    const PROGRAM_PROBLEM_TYPE = 4;
    const PROBLEMANS_TYPE_FILL = 100;
    const PROGRAM_PROBLEM_NAME = "编程题";

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

    public function addProgram2Exam($eid) {
        $ansnum = I('post.numanswer', 0, 'intval');
        $sql = "DELETE FROM `exp_question` WHERE `exam_id`={$eid} AND `type`='4'";
        M()->execute($sql);
        $dataList = array();
        for ($i = 1; $i <= $ansnum; $i++) {
            $programid = test_input($_POST["answer$i"]);
            if (!is_numeric($programid)) {
                return false;
            } else {
                $programid = intval($programid);
                $data = array(
                    'exam_id' => $eid,
                    'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
                    'question_id' => $programid
                );
                $dataList[] = $data;
                M('problem')->where('problem_id=%d', $programid)
                    ->data(array("defunct" => "Y"))->save();
            }
        }
        M('exp_question')->addAll($dataList);
        return true;
    }

    public function getProblemsAndAnswer4Exam($eid, $problemType) {
        switch ($problemType) {
            case ChooseBaseModel::CHOOSE_PROBLEM_TYPE:
                return ChooseBaseModel::instance()->getChooseProblems4Exam($eid);
                break;

            case JudgeBaseModel::JUDGE_PROBLEM_TYPE:
                return JudgeBaseModel::instance()->getJudgeProblems4Exam($eid);
                break;

            case FillBaseModel::FILL_PROBLEM_TYPE:
                return FillBaseModel::instance()->getFillProblems4Exam($eid);
                break;

            case self::PROGRAM_PROBLEM_TYPE:
                return $this->getProgramProblems4Exam($eid);
                break;

            case self::PROBLEMANS_TYPE_FILL:
                return FillBaseModel::instance()->getFillAnswerByFillId($eid);
                break;
        }
    }

    public function getProgramProblems4Exam($eid) {
        $sql = "SELECT `question_id` as `program_id`,`title`,`description`,`input`,`output`,`sample_input`,`sample_output` FROM `exp_question`,`problem` WHERE `exam_id`='$eid' AND `type`='4' AND `question_id`=`problem_id` order by exp_qid asc";
        $ans = M()->query($sql);
        return $ans;
    }

    public function syncProgramAnswer($userId, $eid, $pid, $answer) {
        $dao = M('ex_stuanswer');
        $where = array(
            'user_id' => $userId,
            'exam_id' => $eid,
            'type'    => ProblemService::PROGRAM_PROBLEM_TYPE,
            'question_id' => $pid,
            'answer_id' => 1
        );
        $field = array('answer');
        $res = $dao->field($field)->where($where)->find();
        if (empty($res)) {
            $where['answer'] = $answer;
            $dao->add($where);
        } else {
        }
    }

    public function doRejudgeProgramByExamIdAndUserId($eid, $userId, $programScore, $start_timeC, $end_timeC) {
        $query = "SELECT distinct `question_id`,`result` FROM `exp_question`,`solution` WHERE `exam_id`='$eid' AND `type`='4' AND `result`='4'
		AND `in_date`>'$start_timeC' AND `in_date`<'$end_timeC' AND `user_id`='" . $userId . "' AND `exp_question`.`question_id`=`solution`.`problem_id`";
        $row = M()->query($query);
        $row_cnt = count($row);
        $programsum = $row_cnt * $programScore;
        //$program over
        return $programsum;
    }
}
