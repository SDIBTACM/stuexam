<?php

namespace Teacher\Service;

use Basic\Log;
use Constant\ReqResult\Result;
use Home\Helper\PrivilegeHelper;
use Home\Helper\SessionHelper;
use Home\Helper\SqlExecuteHelper;
use Teacher\Convert\FillConvert;
use Teacher\Model\FillBaseModel;
use Teacher\Model\PrivilegeBaseModel;


class FillService {

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

    public function updateFillInfo() {
        $reqResult = new Result();
        $fillId = I('post.fillid', 0, 'intval');
        $field = array('creator', 'isprivate', 'private_code');
        $_fillInfo = FillBaseModel::instance()->getById($fillId, $field);
        if (empty($_fillInfo) || !PrivilegeHelper::isExamOwner($_fillInfo['creator'])) {
            Log::info("user id: {} fill id: {}, require: change fill info, result: FAIL, reason: no privilege", SessionHelper::getUserId(), $fillId);
            return Result::errorResult("您没有权限进行此操作!");
        }
        if ($_fillInfo['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !PrivilegeHelper::isSuperAdmin()) {
            Log::info("user id: {} fill id: {}, require: change fill info, result: FAIL, reason: no privilege", SessionHelper::getUserId(), $fillId);
            return Result::errorResult("您没有权限进行此操作!");
        }
        $arr = FillConvert::convertFillFromPost();

        // 如果 code 发生变化
        if (strcmp($arr['private_code'], $_fillInfo['private_code'])) {
            $privateCodeCheck = FillBaseModel::instance()->getByPrivateCode(
                $arr['private_code']
            );
            if (!empty($privateCodeCheck)) {
                return Result::errorResult("该私有编号已经有题目设置, 不能重复设置");
            }
        }

        $result = FillBaseModel::instance()->updateById($fillId, $arr);
        if ($result !== false) {
            $sql = "DELETE FROM `fill_answer` WHERE `fill_id`=$fillId";
            M()->execute($sql);
            $ins = array();
            for ($i = 1; $i <= $arr['answernum']; $i++) {
                $answer = test_input($_POST["answer$i"]);
                $ins[] = array("fill_id" => "$fillId", "answer_id" => "$i", "answer" => "$answer");
            }
            if ($arr['answernum']) {
                M('fill_answer')->addAll($ins);
            }

            $pointIds = I('post.point', array());
            KeyPointService::instance()->saveExamPoint(
                $pointIds, $fillId, FillBaseModel::FILL_PROBLEM_TYPE
            );
            $reqResult->setMessage("填空题修改成功!");
            $reqResult->setData("fill");
            Log::info("user id: {} fill id: {}, require: change fill info, result: success", SessionHelper::getUserId(), $fillId);
        } else {
            Log::warn("user id: {} fill id: {}, require: change fill info, result: FAIL, sqldate: {}, sqlresult: {}", SessionHelper::getUserId(), $fillId, $arr, $result);
            return Result::errorResult("填空题修改失败!");
        }
        return $reqResult;
    }

    public function addFillInfo() {
        $reqResult = new Result();
        $arr = FillConvert::convertFillFromPost();
        $arr['addtime'] = date('Y-m-d H:i:s');
        $arr['creator'] = SessionHelper::getUserId();

        $privateCodeCheck = FillBaseModel::instance()->getByPrivateCode(
            $arr['private_code']
        );
        if (!empty($privateCodeCheck)) {
            return Result::errorResult("该私有编号已经有题目设置, 不能重复设置");
        }

        $fillId = FillBaseModel::instance()->insertData($arr);
        if ($fillId <= 0) {
            Log::warn("user id: {}, require: add fill, result: FAIL, sqldate: {}, sqlresult: {}", SessionHelper::getUserId(),
                $arr, $fillId);
            return Result::errorResult("填空题添加失败!");
        }

        for ($i = 1; $i <= $arr['answernum']; $i++) {
            $answer = test_input($_POST["answer$i"]);
            $arr2['fill_id'] = $fillId;
            $arr2['answer_id'] = $i;
            $arr2['answer'] = $answer;
            M('fill_answer')->add($arr2);
        }

        $pointIds = I('post.point', array());
        KeyPointService::instance()->saveExamPoint(
            $pointIds, $fillId, FillBaseModel::FILL_PROBLEM_TYPE
        );
        $reqResult->setMessage("填空题添加成功!");
        $reqResult->setData("fill");
        Log::info("user id: {}, require: add fill, result: success", SessionHelper::getUserId());
        return $reqResult;
    }

    public function doRejudgeFillByExamIdAndUserId($eid, $userId, $allscore) {
        $fillSum = 0;
        $userScoreDetail = $this->getUserFillScoreDetailInExam($eid, $userId, $allscore);
        foreach ($userScoreDetail as $value) {
            $fillSum += $value;
        }
        return $fillSum;
    }

    public function getUserFillScoreDetailInExam($eid, $userId, $allScore) {
        $result = array();
        $fillAnswerForUser = ExamService::instance()->getUserAnswer($eid, $userId, FillBaseModel::FILL_PROBLEM_TYPE);
        $fillAnswerForExam = SqlExecuteHelper::Teacher_GetFillAnswer4Exam($eid);

        if (empty($fillAnswerForExam)) {
            return $result;
        }
        foreach ($fillAnswerForExam as $key => $value) {
            $fillId = $value['fill_id'];
            $answerId = $value['answer_id'];
            if (!isset($result[$fillId])) {
                $result[$fillId] = 0;
            }

            if (isset($fillAnswerForUser[$fillId][$answerId])
                && (!empty($fillAnswerForUser[$fillId][$answerId]) || $fillAnswerForUser[$fillId][$answerId] == "0")) {
                $userAnswer = trim($fillAnswerForUser[$fillId][$answerId]);
                $rightAnswer = trim($value['answer']);
                if ($userAnswer == $rightAnswer && strlen($userAnswer) == strlen($rightAnswer)) {
                    if ($value['kind'] == 1) {
                        $result[$fillId] += $allScore['fillscore'];
                    } else if ($value['kind'] == 2) {
                        $result[$fillId] += formatToFloatScore($allScore['prgans'] / $value['answernum']);
                    } else if ($value['kind'] == 3) {
                        $result[$fillId] += formatToFloatScore($allScore['prgfill'] / $value['answernum']);
                    }
                }
            }
        }
        return $result;
    }
}
