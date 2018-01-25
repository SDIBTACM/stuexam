<?php
namespace Home\Model;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Service\ProblemService;

use Basic\Log;

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

    public function saveProblemAnswer($user_id, $eid, $type) {
        switch ($type) {
            case ChooseBaseModel::CHOOSE_PROBLEM_TYPE:
                $this->saveChooseAnswer($user_id, $eid);
                break;
            case JudgeBaseModel::JUDGE_PROBLEM_TYPE:
                $this->saveJudgeAnswer($user_id, $eid);
                break;
            case FillBaseModel::FILL_PROBLEM_TYPE:
                $this->saveFillAnswer($user_id, $eid);
                break;
        }
    }

    private function saveChooseAnswer($user_id, $eid) {
        $cntChoose = 0;
        $tempSql = "";
        $chooseQ = $this->getQuestion4ExamByType($eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        foreach ($chooseQ as $value) {
            $id = $value['question_id'];
            if (isset($_POST["xzda$id"])) {
                $myAnswer = trim($_POST["xzda$id"]);
                if ($cntChoose == 0) {
                    $tempSql = "INSERT INTO `ex_stuanswer` VALUES('$user_id','$eid','1','$id','1','$myAnswer')";
                    $cntChoose = 1;
                } else {
                    $tempSql = $tempSql . ",('$user_id','$eid','1','$id','1','$myAnswer')";
                }
            }
        }
        if (!empty($tempSql)) {
            $tempSql = $tempSql . " on duplicate key update `answer`=values(`answer`)";
            $status = M()->execute($tempSql);
        } else {
            $status = false;
        }

        if ($status === false) {
            Log::warn("userid: {}, examid: {}, require: save choose answer, result: FAIL! sqldata: {}, sqlresult: {}", $user_id, $eid, $tempSql, $status);
        } else {
            Log::info("userid: {}, examid: {}, require: save choose answer, result: success", $user_id, $eid, $status);
        }
    }

    private function saveJudgeAnswer($user_id, $eid) {
        $cntJudge = 0;
        $tempSql = "";
        $judgeQ = $this->getQuestion4ExamByType($eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        foreach ($judgeQ as $value) {
            $id = $value['question_id'];
            if (isset($_POST["pdda$id"])) {
                $myAnswer = trim($_POST["pdda$id"]);
                if ($cntJudge == 0) {
                    $tempSql = "INSERT INTO `ex_stuanswer` VALUES('$user_id','$eid','2','$id','1','$myAnswer')";
                    $cntJudge = 1;
                } else {
                    $tempSql = $tempSql . ",('$user_id','$eid','2','$id','1','$myAnswer')";
                }
            }
        }
        if (!empty($tempSql)) {
            $tempSql = $tempSql . " on duplicate key update `answer`=values(`answer`)";
            $status = M()->execute($tempSql);
        } else {
            $status = false;
        }

        if ($status === false) {
            Log::warn("userid: {}, examid: {}, require: save judge answer, result: FAIL! sqldata: {}, sqlresult: {}", $user_id, $eid, $tempSql, $status);
        } else {
            Log::info("userid: {}, examid: {}, require: save judge answer, result: success", $user_id, $eid, $status);
        }
    }

    private function saveFillAnswer($user_id, $eid) {
        $cntFill = 0;
        $tempSql = "";
        $fillQ = $this->getQuestion4ExamByType($eid, FillBaseModel::FILL_PROBLEM_TYPE);
        foreach ($fillQ as $value) {
            $aid = $value['answer_id'];
            $fid = $value['fill_id'];
            $name = $fid . "tkda";
            if (isset($_POST["$name$aid"])) {
                $myAnswer = $_POST["$name$aid"];
                $myAnswer = test_input($myAnswer);
                $myAnswer = addslashes($myAnswer);
                if ($cntFill == 0) {
                    $tempSql = "INSERT INTO `ex_stuanswer` VALUES('$user_id','$eid','3','$fid','$aid','$myAnswer')";
                    $cntFill = 1;
                } else {
                    $tempSql = $tempSql . ",('$user_id','$eid','3','$fid','$aid','$myAnswer')";
                }
            }
        }
        if (!empty($tempSql)) {
            $tempSql = $tempSql . " on duplicate key update `answer`=values(`answer`)";
            $status = M()->execute($tempSql);
        } else {
            $status = false;
        }

        if ($status === false) {
            Log::warn("userid: {}, examid: {}, require: save fill answer, result: FAIL! sqldata: {}, sqlresult: {}", $user_id, $eid, $tempSql, $status);
        } else {
            Log::info("userid: {}, examid: {}, require: save fill answer, result: success", $user_id, $eid, $status);
        }
    }

    private function getQuestion4ExamByType($eid, $type) {
        if ($type == FillBaseModel::FILL_PROBLEM_TYPE) {
            $query = "SELECT `fill_id`,`answer_id` FROM `fill_answer` WHERE `fill_id` IN
				( SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='$type')";
            $arr = M()->query($query);
        } else {
            $arr = QuestionBaseModel::instance()->getQuestionIds4ExamByType($eid, $type);
        }
        return $arr;
    }

    public function getExamProgramStatus($user_id, $eid, $start_timeC, $end_timeC) {
        $status = array();
        // 获取所有的考试编程题
        $questionArr = QuestionBaseModel::instance()->getQuestionIds4ExamByType($eid, ProblemService::PROGRAM_PROBLEM_TYPE);
        $questionIds = array();
        foreach ($questionArr as $_q) {
            $questionIds[] = $_q['question_id'];
        }
        if (empty($questionIds)) {
            return $status;
        }

        $questionIdStr = implode('\',\'', $questionIds);
        $questionIdStr = '\'' . $questionIdStr . '\'';

        // oj的pass_rate对于正确的时候不准, 添加这个作为容错处理, 获取所有已经对了的题目
        $rightProgramQuery = "select distinct(problem_id) as problem_id from solution where problem_id in ($questionIdStr) and " .
            "user_id='$user_id' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'";
        $rightIdAns = M()->query($rightProgramQuery);
        $rightIds = array();
        foreach ($rightIdAns as $p) {
            $rightIds[] = $p['problem_id'];
            $status[$p['problem_id']] = 1;
        }

        // 过滤到所有对了的题目, 查询其他题目的状态
        $otherIds = array_diff($questionIds, $rightIds);
        if (empty($otherIds)) {
            return $status;
        }

        $otherIdStr = implode('\',\'', $otherIds);
        $questionIdStr = '\'' . $otherIdStr . '\'';
        $query = "select problem_id, max(pass_rate) as rate from solution where problem_id in ($questionIdStr) and " .
            "user_id='$user_id' and in_date>'$start_timeC' and in_date<'$end_timeC' group by problem_id";
        $data = M()->query($query);

        foreach ($data as $d) {
            if ($d['rate'] >= 0.98) {
                $status[$d['problem_id']] = 1;
            } else {
                $status[$d['problem_id']] = $d['rate'];
            }
        }
        return $status;
    }

    public function getRightProgramCount($user_id, $eid, $start_timeC, $end_timeC) {
        $programStatus = $this->getExamProgramStatus($user_id, $eid, $start_timeC, $end_timeC);
        $count = 0;
        if (empty($programStatus)) {
            return $count;
        }
        foreach ($programStatus as $value) {
            $count += $value;
        }
        return $count;
    }
}
