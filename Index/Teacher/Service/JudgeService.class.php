<?php
namespace Teacher\Service;

use Basic\Log;
use Constant\ReqResult\Result;
use Home\Helper\PrivilegeHelper;
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
            Log::info("user id:{} judge id: {}, require: change judge info, result: FAIL, reason: no privilege", $_SESSION['user_id'], $judgeid);
            return Result::errorResult("您没有权限进行此操作!");
        }

        if ($tmp['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !PrivilegeHelper::isSuperAdmin()) {
            Log::info("user id: {} judge id: {}, require: change judge info, result: FAIL, reason: no privilege", $_SESSION['user_id'], $judgeid);
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
            Log::info("user id: {} judge id: {}, require: change judge info, result: success", $_SESSION['user_id'], $judgeid);
        } else {
            Log::warn("user id: {} judge id: {}, require: change judge info, result: FAIL, sqldate: {}, sqlresult: {}", $_SESSION['user_id'], $judgeid, $arr, $result);
            return Result::errorResult("判断题修改失败!");
        }
        return $reqResult;
    }

    public function addJudgeInfo() {
        $reqResult = new Result();
        $arr = JudgeConvert::convertJudgeFromPost();
        $arr['creator'] = $_SESSION['user_id'];
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
            Log::info("user id: {}, require: add judge, result: success", $_SESSION['user_id']);
        } else {
            Log::warn("user id: {}, require: add judge, result: FAIL, sqldate: {}, sqlresult: {}", $_SESSION['user_id'], $arr, $lastId);
            return Result::errorResult("判断题修改失败!");
        }
        return $reqResult;
    }

    public function doRejudgeJudgeByExamIdAndUserId($eid, $userId, $judgeScore) {
        $judgeSum = 0;
        $judgearr = ExamService::instance()->getUserAnswer($eid, $userId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);;
        $row = SqlExecuteHelper::Teacher_GetJudgeAnswer4Exam($eid);
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
