<?php
namespace Teacher\Model;

class ProblemServiceModel
{
    const PROGRAM_PROBLEM_TYPE = 4;
    const PROBLEMANS_TYPE_FILL = 100;

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
        for ($i = 1; $i <= $ansnum; $i++) {
            $programid = test_input($_POST["answer$i"]);
            if (!is_numeric($programid)) {
                return false;
            } else {
                $programid = intval($programid);
                $data = array(
                    'exam_id' => $eid,
                    'type' => ProblemServiceModel::PROGRAM_PROBLEM_TYPE,
                    'question_id' => $programid
                );
                M('exp_question')->data($data)->add();
                M('problem')->where('problem_id=%d', $programid)
                    ->data(array("defunct" => "Y"))->save();
            }
        }
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
        $sql = "SELECT `question_id` as `program_id`,`title`,`description`,`input`,`output`,`sample_input`,`sample_output` FROM `exp_question`,`problem` WHERE `exam_id`='$eid' AND `type`='4' AND `question_id`=`problem_id`";
        $ans = M()->query($sql);
        return $ans;
    }

    public function syncProgramAnswer($userId, $eid, $pid, $answer) {
        $dao = M('ex_stuanswer');
        $where = array(
            'user_id' => $userId,
            'exam_id' => $eid,
            'type'    => ProblemServiceModel::PROGRAM_PROBLEM_TYPE,
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
}
