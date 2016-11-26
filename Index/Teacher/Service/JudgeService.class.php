<?php
namespace Teacher\Service;

use Constant\ReqResult\Result;
use Teacher\Convert\JudgeConvert;

use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;

class JudgeService
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

    public function updateJudgeInfo() {
        $reqResult = new Result();
        $judgeid = I('post.judgeid', 0, 'intval');
        $field = array('creator', 'isprivate');
        $tmp = JudgeBaseModel::instance()->getById($judgeid, $field);
        if (empty($tmp) || !checkAdmin(4, $tmp['creator'])) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("您没有权限进行此操作!");
        } else if ($tmp['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !checkAdmin(1)) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("您没有权限进行此操作!");
        } else {
            $arr = JudgeConvert::convertJudgeFromPost();
            $result = JudgeBaseModel::instance()->updateById($judgeid, $arr);
            if ($result !== false) {
                $pointIds = I('post.point', array());
                KeyPointService::instance()->saveExamPoint(
                    $pointIds, $judgeid, JudgeBaseModel::JUDGE_PROBLEM_TYPE
                );
                $reqResult->setMessage("判断题修改成功!");
                $reqResult->setData("judge");
            } else {
                $reqResult->setStatus(false);
                $reqResult->setMessage("判断题修改失败!");
            }
        }
        return $reqResult;
    }

    public function addJudgeInfo() {
        $reqResult = new Result();
        $arr = JudgeConvert::convertJudgeFromPost();
        $arr['creator'] = $_SESSION['user_id'];
        $arr['addtime'] = date('Y-m-d H:i:s');
        $lastId = JudgeBaseModel::instance()->insertData($arr);
        if ($lastId) {
            $pointIds = I('post.point', array());
            KeyPointService::instance()->saveExamPoint(
                $pointIds, $lastId, JudgeBaseModel::JUDGE_PROBLEM_TYPE
            );
            $reqResult->setMessage("判断题添加成功!");
            $reqResult->setData("judge");
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("判断题添加失败!");
        }
        return $reqResult;
    }

    public function doRejudgeJudgeByExamIdAndUserId($eid, $userId, $judgeScore) {
        $judgeSum = 0;
        $judgearr = ExamService::instance()->getUserAnswer($eid, $userId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);;
        $query = "SELECT `judge_id`,`answer` FROM `ex_judge` WHERE `judge_id` IN
		(SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='2')";
        $row = M()->query($query);
        if ($row) {
            foreach ($row as $key => $value) {
                if (isset($judgearr[$value['judge_id']])) {
                    $myanswer = $judgearr[$value['judge_id']];
                    if ($myanswer == $value['answer'])
                        $judgeSum += $judgeScore;
                }
            }
        }
        return $judgeSum;
    }
}