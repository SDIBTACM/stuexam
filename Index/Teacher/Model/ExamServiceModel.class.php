<?php
namespace Teacher\Model;

use Constant\ReqResult\Result;
use Teacher\Convert\ExamConvert;

class ExamServiceModel
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
        $res = ExamBaseModel::instance()->updateExamInfoById($examId, $data);
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

        $return = ExamBaseModel::instance()->addExamBaseInfo($data);

        if ($return) {
            $reqResult->setMessage("考试添加成功!");
            $reqResult->setData("index");
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("考试添加失败!");
        }
        return $reqResult;
    }

    public function addUsers2Exam($eid) {
        if ($eid > 0) {
            M('ex_privilege')->where("rightstr='e$eid'")->delete();
            $pieces = explode("\n", trim($_POST['ulist']));
            if (count($pieces) > 0 && strlen($pieces[0]) > 0) {
                for ($i = 0; $i < count($pieces); $i++) {
                    $pieces[$i] = trim($pieces[$i]);
                }
            }
            $pieces = array_unique($pieces);
            if (count($pieces) > 0 && strlen($pieces[0]) > 0) {
                $randnum = rand(1, 39916800);
                $query = "INSERT INTO `ex_privilege`(`user_id`,`rightstr`,`randnum`) VALUES('" . trim($pieces[0]) . "','e$eid','$randnum')";
                for ($i = 1; $i < count($pieces); $i++) {
                    $randnum = rand(1, 39916800);
                    if (isset($pieces[$i])) {
                        $query = $query . ",('" . trim($pieces[$i]) . "','e$eid','$randnum')";
                    }
                }
                M()->execute($query);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
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
