<?php
namespace Home\Model;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Service\ProblemService;

class AnswerModel
{

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

    public function saveProblemAnswer($user_id, $eid, $type, $issave = true) {
        switch ($type) {
            case ChooseBaseModel::CHOOSE_PROBLEM_TYPE:
                return $this->saveChooseAnswer($user_id, $eid, $issave);
                break;
            case JudgeBaseModel::JUDGE_PROBLEM_TYPE:
                return $this->saveJudgeAnswer($user_id, $eid, $issave);
                break;
            case FillBaseModel::FILL_PROBLEM_TYPE:
                return $this->saveFillAnswer($user_id, $eid, $issave);
                break;
        }
    }

    private function saveChooseAnswer($user_id, $eid, $issave) {
        $cntchoose = 0;
        $tempsql = "";
        $right = 0;
        $chooseq = $this->getQuestion4ExamByType($eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, $issave);
        foreach ($chooseq as $value) {
            $id = $value['question_id'];
            if (isset($_POST["xzda$id"])) {
                $myanswer = trim($_POST["xzda$id"]);
                if ($cntchoose == 0) {
                    $tempsql = "INSERT INTO `ex_stuanswer` VALUES('$user_id','$eid','1','$id','1','$myanswer')";
                    $cntchoose = 1;
                } else {
                    $tempsql = $tempsql . ",('$user_id','$eid','1','$id','1','$myanswer')";
                }
                if (!$issave && $myanswer == $value['answer']) {
                    $right++;
                }
            }
        }
        if (!empty($tempsql)) {
            $tempsql = $tempsql . " on duplicate key update `answer`=values(`answer`)";
            M()->execute($tempsql);
        }
        return $right;
    }

    private function saveJudgeAnswer($user_id, $eid, $issave) {
        $cntjudge = 0;
        $tempsql = "";
        $right = 0;
        $judgeq = $this->getQuestion4ExamByType($eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE, $issave);
        foreach ($judgeq as $value) {
            $id = $value['question_id'];
            if (isset($_POST["pdda$id"])) {
                $myanswer = trim($_POST["pdda$id"]);
                if ($cntjudge == 0) {
                    $tempsql = "INSERT INTO `ex_stuanswer` VALUES('$user_id','$eid','2','$id','1','$myanswer')";
                    $cntjudge = 1;
                } else {
                    $tempsql = $tempsql . ",('$user_id','$eid','2','$id','1','$myanswer')";
                }
                if (!$issave && $myanswer == $value['answer']) {
                    $right++;
                }
            }
        }
        if (!empty($tempsql)) {
            $tempsql = $tempsql . " on duplicate key update `answer`=values(`answer`)";
            M()->execute($tempsql);
        }
        return $right;
    }

    private function saveFillAnswer($user_id, $eid, $issave) {

        $cntfill = 0;
        $tempsql = "";
        $fillsum = 0;
        $fillq = $this->getQuestion4ExamByType($eid, FillBaseModel::FILL_PROBLEM_TYPE, $issave);
        if (!$issave) {
            $field = array('fillscore', 'prgans', 'prgfill');
            $score = ExamBaseModel::instance()->getExamInfoById($eid, $field);
        }
        foreach ($fillq as $value) {
            $aid = $value['answer_id'];
            $fid = $value['fill_id'];
            $name = $fid . "tkda";
            if (isset($_POST["$name$aid"])) {
                $myanswer = $_POST["$name$aid"];
                $myanswer = test_input($myanswer);
                $myanswer = addslashes($myanswer);
                if ($cntfill == 0) {
                    $tempsql = "INSERT INTO `ex_stuanswer` VALUES('$user_id','$eid','3','$fid','$aid','$myanswer')";
                    $cntfill = 1;
                } else {
                    $tempsql = $tempsql . ",('$user_id','$eid','3','$fid','$aid','$myanswer')";
                }
                if (!$issave) {
                    $rightans = addslashes($value['answer']);
                    if ($myanswer == $rightans && strlen($myanswer) == strlen($rightans)) {
                        if ($value['kind'] == 1) {
                            $fillsum += $score['fillscore'];
                        } else if ($value['kind'] == 2) {
                            $fillsum = $fillsum + $score['prgans'] / $value['answernum'];
                        } else if ($value['kind'] == 3) {
                            $fillsum = $fillsum + $score['prgfill'] / $value['answernum'];
                        }
                    }
                }
            }
        }
        if (!empty($tempsql)) {
            $tempsql = $tempsql . " on duplicate key update `answer`=values(`answer`)";
            M()->execute($tempsql);
        }
        if (!$issave) {
            return $fillsum;
        }
    }

    private function getQuestion4ExamByType($eid, $type, $issave = true) {
        if ($issave) {
            if ($type == FillBaseModel::FILL_PROBLEM_TYPE) {
                $query = "SELECT `fill_id`,`answer_id` FROM `fill_answer` WHERE `fill_id` IN
				( SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='$type')";
                $arr = M()->query($query);
            } else {
                $arr = QuestionBaseModel::instance()->getQuestionIds4ExamByType($eid, $type);
            }
        } else {
            if ($type == ChooseBaseModel::CHOOSE_PROBLEM_TYPE) {
                $sql = "SELECT `question_id`,`answer` FROM `ex_choose`,`exp_question` WHERE `exam_id`='$eid' AND `type`='1' AND `choose_id`=`question_id`";
            } else if ($type == JudgeBaseModel::JUDGE_PROBLEM_TYPE) {
                $sql = "SELECT `question_id`,`answer` FROM `ex_judge`,`exp_question` WHERE `exam_id`='$eid' AND `type`='2' AND `judge_id`=`question_id`";
            } else {
                $sql = "SELECT `fill_answer`.`fill_id`,`answer_id`,`answer`,`answernum`,`kind` FROM `fill_answer`,`ex_fill` WHERE `fill_answer`.`fill_id`=`ex_fill`.`fill_id` AND `fill_answer`.`fill_id` IN ( SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='3')";
            }
            $arr = M()->query($sql);
        }
        return $arr;
    }

    public function getRightProgramCount($user_id, $eid, $start_timeC, $end_timeC) {
        $questionArr = QuestionBaseModel::instance()->getQuestionIds4ExamByType($eid, ProblemService::PROGRAM_PROBLEM_TYPE);
        $questionIds = array();
        foreach($questionArr as $_q) {
            $questionIds[] = $_q['question_id'];
        }
        if (empty($questionIds)) {
            return 0;
        }
        $questionIdStr = implode('\',\'', $questionIds);
        $questionIdStr = '\'' . $questionIdStr . '\'';

        $count = 0;

        // oj的pass_rate对于正确的时候不准, 添加这个作为容错处理
        $rightProgramQuery = "select distinct(problem_id) as problem_id from solution where problem_id in ($questionIdStr) and " .
            "user_id='$user_id' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'";
        $rightIdAns = M()->query($rightProgramQuery);
        $rightIds = array();
        foreach ($rightIdAns as $p) {
            $rightIds[] = $p['problem_id'];
            $count = $count + 1;
        }

        $otherIds = array_diff($questionIds, $rightIds);
        if (empty($otherIds)) {
            return $count;
        }
        $otherIdStr = implode('\',\'', $otherIds);
        $questionIdStr = '\'' . $otherIdStr . '\'';

        $query = "select max(pass_rate) as rate from solution where problem_id in ($questionIdStr) and " .
                "user_id='$user_id' and in_date>'$start_timeC' and in_date<'$end_timeC' group by problem_id";
        $data = M()->query($query);

        foreach ($data as $d) {
            if ($d['rate'] >= 0.98) {
                $count = $count + 1;
            } else {
                $count = $count + $d['rate'];
            }
        }
        return $count;
    }
}
