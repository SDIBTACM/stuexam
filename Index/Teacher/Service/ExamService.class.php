<?php
namespace Teacher\Service;

use Constant\ReqResult\Result;
use Teacher\Convert\ExamConvert;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\KeyPointBaseModel;

class ExamService
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

    public function updateExamInfo() {
        $reqResult = new Result();
        $examId = intval($_POST['examid']);
        $_examInfo = ExamBaseModel::instance()->getExamInfoById($examId, array('creator'));
        if (empty($_examInfo) || !checkAdmin(4, $_examInfo['creator'])) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("You have no privilege to modify it!");
            return $reqResult;
        }

        $data = ExamConvert::convertExamDataFromPost();
        $res = ExamBaseModel::instance()->updateById($examId, $data);
        if ($res !== false) {
            $reqResult->setMessage("考试修改成功!");
            $reqResult->setData("index");
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("考试修改失败!");
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
            $reqResult->setData("index");
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("考试添加失败!");
        }
        return $reqResult;
    }

    public function addUsers2Exam($eid, $ulist) {
        if ($eid <= 0) {
            return false;
        }
        $userPrivilegeList = M('ex_privilege')->field('user_id, extrainfo')->where("rightstr='e$eid'")->select();
        $userIdMap = array();
        foreach ($userPrivilegeList as $_privilege) {
            $userIdMap[$_privilege['user_id']] = $_privilege['extrainfo'];
        }
        $_pieces = explode("\n", $ulist);
        if (count($_pieces) > 0 && strlen($_pieces[0]) > 0) {
            for ($i = 0; $i < count($_pieces); $i++) {
                $_pieces[$i] = trim($_pieces[$i]);
            }
        }
        $pieces = array_intersect_key($_pieces, array_unique(array_map('strtolower', $_pieces)));
        if (count($pieces) == 0) {
            return false;
        }
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
        M('ex_privilege')->where("rightstr='e$eid'")->delete();
        M()->execute($query);
        return true;
    }

    public function getBaseScoreByExamId($eid) {
        $field = array(
            'choosescore',
            'judgescore',
            'fillscore',
            'prgans',
            'prgfill',
            'programscore'
        );
        $allScore = ExamBaseModel::instance()->getExamInfoById($eid, $field);
        return $allScore;
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
}
