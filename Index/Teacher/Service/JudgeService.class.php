<?php
namespace Teacher\Service;

use Basic\Log;
use Constant\ReqResult\Result;
use Home\Helper\PrivilegeHelper;
use Home\Helper\SessionHelper;
use Home\Helper\SqlExecuteHelper;
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
        $field = array('creator', 'isprivate', 'private_code');
        $tmp = JudgeBaseModel::instance()->getById($judgeid, $field);
        if (empty($tmp) || !PrivilegeHelper::isExamOwner($tmp['creator'])) {
            Log::info("user id:{} judge id: {}, require: change judge info, result: FAIL, reason: no privilege", SessionHelper::getUserId(), $judgeid);
            return Result::errorResult("您没有权限进行此操作!");
        }

        if ($tmp['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !PrivilegeHelper::isSuperAdmin()) {
            Log::info("user id: {} judge id: {}, require: change judge info, result: FAIL, reason: no privilege", SessionHelper::getUserId(), $judgeid);
            return Result::errorResult("您没有权限进行此操作!");
        }
        $arr = JudgeConvert::convertJudgeFromPost();

        // 如果 code 发生变化
        if (strcmp($arr['private_code'], $tmp['private_code'])) {
            $privateCodeCheck = JudgeBaseModel::instance()->getByPrivateCode(
                $arr['private_code']
            );
            if (!empty($privateCodeCheck)) {
                return Result::errorResult("该私有编号已经有题目设置, 不能重复设置");
            }
        }

        $result = JudgeBaseModel::instance()->updateById($judgeid, $arr);
        if ($result !== false) {
            $pointIds = I('post.point', array());
            KeyPointService::instance()->saveExamPoint(
                $pointIds, $judgeid, JudgeBaseModel::JUDGE_PROBLEM_TYPE
            );
            $reqResult->setMessage("判断题修改成功!");
            $reqResult->setData("judge");
            Log::info("user id: {} judge id: {}, require: change judge info, result: success", SessionHelper::getUserId(), $judgeid);
        } else {
            Log::warn("user id: {} judge id: {}, require: change judge info, result: FAIL, sqldate: {}, sqlresult: {}", SessionHelper::getUserId(), $judgeid, $arr, $result);
            return Result::errorResult("判断题修改失败!");
        }
        return $reqResult;
    }

    public function addJudgeInfo() {
        $reqResult = new Result();
        $arr = JudgeConvert::convertJudgeFromPost();
        $arr['creator'] = SessionHelper::getUserId();
        $arr['addtime'] = date('Y-m-d H:i:s');

        $privateCodeCheck = JudgeBaseModel::instance()->getByPrivateCode(
            $arr['private_code']
        );
        if (!empty($privateCodeCheck)) {
            return Result::errorResult("该私有编号已经有题目设置, 不能重复设置");
        }

        $lastId = JudgeBaseModel::instance()->insertData($arr);
        if ($lastId) {
            $pointIds = I('post.point', array());
            KeyPointService::instance()->saveExamPoint(
                $pointIds, $lastId, JudgeBaseModel::JUDGE_PROBLEM_TYPE
            );
            $reqResult->setMessage("判断题添加成功!");
            $reqResult->setData("judge");
            Log::info("user id: {}, require: add judge, result: success", SessionHelper::getUserId());
        } else {
            Log::warn("user id: {}, require: add judge, result: FAIL, sqldate: {}, sqlresult: {}", SessionHelper::getUserId(), $arr, $lastId);
            return Result::errorResult("判断题修改失败!");
        }
        return $reqResult;
    }

    public function doRejudgeJudgeByExamIdAndUserId($eid, $userId, $judgeScore) {
        $judgeSum = 0;
        $userScoreDetail = $this->getUserJudgeScoreDetailInExam($eid, $userId, $judgeScore);
        foreach ($userScoreDetail as $value) {
            $judgeSum += $value;
        }
        return $judgeSum;
    }

    public function getUserJudgeScoreDetailInExam($eid, $userId, $judgeScore) {
        $judgeAnswerForUser = ExamService::instance()->getUserAnswer($eid, $userId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeAnswerForExam = SqlExecuteHelper::Teacher_GetJudgeAnswer4Exam($eid);
        $result = array();
        if ($judgeAnswerForExam) {
            foreach ($judgeAnswerForExam as $key => $value) {
                $judgeId = $value['judge_id'];
                $result[$judgeId] = (isset($judgeAnswerForUser[$judgeId])
                    && $judgeAnswerForUser[$judgeId] == $value['answer']) ? $judgeScore : 0;
            }
        }
        return $result;
    }
}
