<?php

namespace Teacher\Service;

use Basic\Log;
use Constant\ReqResult\Result;
use Home\Helper\PrivilegeHelper;
use Teacher\Convert\ExamConvert;
use Teacher\Convert\GenerateExamConvert;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;

class ExamService {

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

    public function updateExamInfo() {
        $reqResult = new Result();
        $examId = intval($_POST['examid']);
        $_examInfo = ExamBaseModel::instance()->getById($examId, array('creator'));
        if (empty($_examInfo) || !PrivilegeHelper::isExamOwner($_examInfo['creator'])) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("You have no privilege to modify it!");
            Log::info("user id: {} exam id: {}, require: change exam info, result: FAIL, reason: no privilege", $_SESSION['user_id'], $examId);
            return $reqResult;
        }

        $data = ExamConvert::convertExamDataFromPost();
        $res = ExamBaseModel::instance()->updateById($examId, $data);
        if ($res !== false) {
            $reqResult->setMessage("考试修改成功!");
            $reqResult->setData("Quiz");
            Log::info("user id: {} exam id: {}, require: change exam info, result: success", $_SESSION['user_id'], $examId);
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("考试修改失败!");
            Log::warn("user id: {} exam id: {}, require: change exam info, result: FAIL, sqldate: {}, sqlresult: {}",
                $_SESSION['user_id'], $examId, $data, $res);
        }
        return $reqResult;
    }

    public function addExamInfo() {
        $reqResult = new Result();
        $data = ExamConvert::convertExamDataFromPost();
        $data['creator'] = $_SESSION['user_id'];

        $return = ExamBaseModel::instance()->insertData($data);

        if ($return) {
            $reqResult->setMessage("考试添加成功!");
            $reqResult->setData("Quiz");
            Log::info("user id: {}, require: add exam, result: success", $_SESSION['user_id']);
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("考试添加失败!");
            Log::warn("user id: {}, require: add exam, result: FAIL, sqldate: {}, sqlresult: {}", $_SESSION['user_id'], $data, $return);
        }
        return $reqResult;
    }

    public function addUsers2Exam($eid, $ulist) {
        if ($eid <= 0) {
            return false;
        }
        $_where = array('rightstr' => "e$eid");
        $_fields = array('user_id', 'extrainfo');
        $userPrivilegeList = PrivilegeBaseModel::instance()->queryAll($_where, $_fields);
        $userIdMap = array();
        foreach ($userPrivilegeList as $_privilege) {
            $userIdMap[$_privilege['user_id']] = $_privilege['extrainfo'];
        }

        M('ex_privilege')->where("rightstr='e$eid'")->delete();

        $_pieces = explode("\n", $ulist);
        if (count($_pieces) > 0 && strlen($_pieces[0]) > 0) {
            for ($i = 0; $i < count($_pieces); $i++) {
                $_pieces[$i] = trim($_pieces[$i]);
            }
        } else {
            $_pieces = array("0");
        }
        $realUsers = M("users")->field('user_id')->where(array('user_id' => array('in', $_pieces)))->select();
        $pieces = array();
        foreach ($realUsers as $r) {
            $pieces[] = $r['user_id'];
        }
        if (count($pieces) == 0) return true;

        $flag = true;
        $query = "";
        foreach ($pieces as $piece) {
            $randnum = rand(1, 39916800);
            $extraInfo = 0;
            if (isset($userIdMap[$piece])) {
                $extraInfo = $userIdMap[$piece];
            }
            if ($flag) {
                $flag = false;
                $query = "INSERT INTO `ex_privilege`(`user_id`,`rightstr`,`randnum`, `extrainfo`) VALUES('" . trim($piece) . "','e$eid','$randnum', $extraInfo)";
            } else {
                $query = $query . ",('" . trim($piece) . "','e$eid','$randnum', $extraInfo)";
            }
        }
        M()->execute($query);
        return true;
    }

    public function getUserAnswer($eid, $users, $type) {
        $arr = array();
        if ($type == ChooseBaseModel::CHOOSE_PROBLEM_TYPE || $type == JudgeBaseModel::JUDGE_PROBLEM_TYPE) {
            $row = M('ex_stuanswer')->field('question_id,answer')
                ->where("exam_id=%d and type=%d and user_id='%s'", $eid, $type, $users)
                ->select();
            if ($row) {
                foreach ($row as $key => $value) {
                    $arr[$value['question_id']] = $value['answer'];
                }
                unset($row);
            }
        } else if ($type == FillBaseModel::FILL_PROBLEM_TYPE) {
            $row = M('ex_stuanswer')->field('question_id,answer_id,answer')
                ->where("exam_id=%d and type=%d and user_id='%s'", $eid, $type, $users)
                ->select();
            if ($row) {
                foreach ($row as $key => $value) {
                    $arr[$value['question_id']][$value['answer_id']] = $value['answer'];
                }
                unset($row);
            }
        }
        return $arr;
    }

    public function autoGenerateExam() {

        $convertResult = GenerateExamConvert::generateProblem();

        if (! $convertResult instanceof Result) {
            return Result::errorResult("生成试卷发生错误,类型不匹配");
        }

        if (!$convertResult->getStatus()) {
            return $convertResult;
        }

        $problemMap = $convertResult->getData();
        if (empty($problemMap)) {
            return Result::errorResult("生成的题目列表为空");
        }

        $where = array('title' => "自动生成试卷占位符", 'visible' => 'Y');
        $exam = ExamBaseModel::instance()->queryOne($where, array('exam_id'));
        if (empty($exam)) {
            $examConfig = ExamConvert::generateDefaultExamConfig();
            $examId = ExamBaseModel::instance()->insertData($examConfig);
        } else {
            $examId = $exam['exam_id'];
            // 删除这个考试关联的所有信息
            $where = array('exam_id' => $examId);
            // 删除绑定的题目
            M('exp_question')->where($where)->delete();
            //M('ex_stuanswer')->where($where)->delete();
            //M('ex_student')->where($where)->delete();
            sleep(1);
        }

        if ($examId <= 0) {
            return Result::errorResult("考试生成失败, 请重试");
        }

        $total = array(0, 0, 0, 0, 0);
        $failCount = 0;
        $failDetail = array(
            0 => array('count' => 0, 'message' => ''),
            1 => array('count' => 0, 'message' => ''),
            2 => array('count' => 0, 'message' => ''),
            3 => array('count' => 0, 'message' => '')
        );

        foreach ($problemMap as $problemType => $problemList) {
            $total[$problemType] = count($problemList);
            if ($problemType == ProblemService::PROGRAM_PROBLEM_TYPE) {
                $this->generateProgram($problemList, $examId);
            } else {
                $result = $this->generateQuestion($problemType, $problemList, $examId);
                if ($result instanceof Result) {
                    if (!$result->getStatus()) {
                        $failCount += $result->getData();
                        $failDetail[$problemType]['count'] = $result->getData();
                        $failDetail[$problemType]['message'] = $result->getMessage();
                    }
                }
            }
        }

        $data = array(
            'total' => $total,
            'failCount' => $failCount,
            'failDetail' => $failDetail,
            'examId' => $examId,
            'examUrl' => U("Teacher/Quiz/index", array('eid' => $examId))
        );

        return Result::successResultWithData($data);
    }

    private function generateQuestion($problemType, $problemList, $examId) {
        $failCount = 0;
        $message = "";
        foreach ($problemList as $privateCode) {
            // 验证 code 是否存在
            $res = ProblemService::instance()->getByPrivateCode($problemType, $privateCode);
            if (empty($res)) {
                $failCount++;
                $message .= "$privateCode: 编号不存在\n";
            } else {
                if ($problemType == ChooseBaseModel::CHOOSE_PROBLEM_TYPE) {
                    $questionId = $res['choose_id'];
                } else if ($problemType == JudgeBaseModel::JUDGE_PROBLEM_TYPE) {
                    $questionId = $res['judge_id'];
                } else {
                    $questionId = $res['fill_id'];
                }

                $data = array(
                    'exam_id' => $examId,
                    'question_id' => $questionId,
                    'type' => $problemType
                );
                $insertFlag = M('exp_question')->add($data);
                if (!$insertFlag) {
                    $failCount++;
                    $message .= "$privateCode: 添加到考券时失败, 需要人为添加\n";
                }
            }
        }
        if ($failCount > 0) {
            $result = new Result();
            $result->setStatus(false);
            $result->setMessage($message);
            $result->setData($failCount);
            return $result;
        } else {
            return Result::successResult();
        }
    }

    private function generateProgram($problemList, $examId) {
        ProblemService::instance()->addProgram2Exam($examId, $problemList);
    }
}
