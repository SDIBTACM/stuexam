<?php
namespace Teacher\Model;

class ProblemServiceModel
{
    const PROBLEMANS_TYPE_FILL    = 4;
    const EXAMPROBLEM_TYPE_PROGRAM = 5;

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
                $arr['exam_id'] = $eid;
                $arr['type'] = 4;
                $arr['question_id'] = $programid;
                M('exp_question')->data($arr)->add();
                M('problem')->where('problem_id=%d', $programid)
                    ->data(array("defunct" => "Y"))->save();
            }
        }
        return true;
    }

    public function getProblemsAndAnswer4Exam($eid, $type) {
        switch ($type) {
            case ChooseBaseModel::CHOOSE_PROBLEM_TYPE:
                return ChooseBaseModel::instance()->getChooseProblems4Exam($eid);
                break;

            case JudgeBaseModel::JUDGE_PROBLEM_TYPE:
                return JudgeBaseModel::instance()->getJudgeProblems4Exam($eid);
                break;

            case FillBaseModel::FILL_PROBLEM_TYPE:
                return FillBaseModel::instance()->getFillProblems4Exam($eid);
                break;

            case self::PROBLEMANS_TYPE_FILL:
                return FillBaseModel::instance()->getFillAnswerByFillId($eid);
                break;

            case self::EXAMPROBLEM_TYPE_PROGRAM:
                return $this->getProgramProblems4Exam($eid);
                break;
        }
    }

    public function getProgramProblems4Exam($eid) {
        $sql = "SELECT `question_id` as `program_id`,`title`,`description`,`input`,`output`,`sample_input`,`sample_output` FROM `exp_question`,`problem` WHERE `exam_id`='$eid' AND `type`='4' AND `question_id`=`problem_id`";
        $ans = M()->query($sql);
        return $ans;
    }
}
