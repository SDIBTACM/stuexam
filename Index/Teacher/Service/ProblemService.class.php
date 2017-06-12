<?php
namespace Teacher\Service;

use Home\Model\AnswerModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;
use Think\Log;

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

    public function addProgram2Exam($eid, $problemIds) {
        $len = count($problemIds);
        $sql = "DELETE FROM `exp_question` WHERE `exam_id`={$eid} AND `type`='4'";
        M()->execute($sql);
        $dataList = array();
        for ($i = 0; $i < $len; $i++) {
            $programId = $problemIds[$i];
            $data = array(
                'exam_id' => $eid,
                'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
                'question_id' => $programId
            );
            $dataList[] = $data;
            M('problem')->where('problem_id=%d', $programId)
                ->data(array("defunct" => "Y"))->save();
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

    public function syncProgramAnswer($userId, $eid, $pid, $answer, $passRate) {
        Log::record("userId: $userId, eid: $eid, pid: $pid, answer:$answer, passRate:$passRate");
        $dao = M('ex_stuanswer');
        $where = array(
            'user_id' => $userId,
            'exam_id' => $eid,
            'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
            'question_id' => $pid,
            'answer_id' => 1
        );

        $field = array('answer');
        $res = $dao->field($field)->where($where)->find();
        // 如果沒有保存
        if (empty($res)) {
            Log::record("empty record need to add");
            if ($answer != 4) {
                $where['answer'] = strval($passRate);
            } else {
                $where['answer'] = strval($answer);
            }
            return $dao->add($where);
        } else {
            Log::record("need to update");
            $_ans = $res['answer'];
            if (strcmp($_ans, "4") != 0) {
                $data = array();
                if ($answer == 4) {
                    $data['answer'] = "4";
                } else if ($passRate > doubleval($_ans)) {
                    $data['answer'] = strval($passRate);
                }
                if (!empty($data)) {
                    return $dao->where($where)->data($data)->save();
                }
            }
        }
        return 1;
    }

    public function doRejudgeProgramByExamIdAndUserId($eid, $userId, $programScore, $start_timeC, $end_timeC) {
        $row_cnt = AnswerModel::instance()->getRightProgramCount($userId, $eid, $start_timeC, $end_timeC);
        $programsum = round($row_cnt * $programScore);
        //$program over
        return $programsum;
    }
}
