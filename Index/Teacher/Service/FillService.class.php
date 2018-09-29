<?php

namespace Teacher\Service;

use Basic\Log;
use Constant\ReqResult\Result;
use Home\Helper\PrivilegeHelper;
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
            Log::info("user id: {} fill id: {}, require: change fill info, result: FAIL, reason: no privilege", $_SESSION['user_id'], $fillId);
            return Result::errorResult("您没有权限进行此操作!");
        }
        if ($_fillInfo['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !PrivilegeHelper::isSuperAdmin()) {
            Log::info("user id: {} fill id: {}, require: change fill info, result: FAIL, reason: no privilege", $_SESSION['user_id'], $fillId);
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
            Log::info("user id: {} fill id: {}, require: change fill info, result: success", $_SESSION['user_id'], $fillId);
        } else {
            Log::warn("user id: {} fill id: {}, require: change fill info, result: FAIL, sqldate: {}, sqlresult: {}", $_SESSION['user_id'], $fillId, $arr, $result);
            return Result::errorResult("填空题修改失败!");
        }
        return $reqResult;
    }

    public function addFillInfo() {
        $reqResult = new Result();
        $arr = FillConvert::convertFillFromPost();
        $arr['addtime'] = date('Y-m-d H:i:s');
        $arr['creator'] = $_SESSION['user_id'];

        $privateCodeCheck = FillBaseModel::instance()->getByPrivateCode(
            $arr['private_code']
        );
        if (!empty($privateCodeCheck)) {
            return Result::errorResult("该私有编号已经有题目设置, 不能重复设置");
        }

        $fillId = FillBaseModel::instance()->insertData($arr);
        if ($fillId <= 0) {
            Log::warn("user id: {}, require: add fill, result: FAIL, sqldate: {}, sqlresult: {}", $_SESSION['user_id'],
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
        Log::info("user id: {}, require: add fill, result: success", $_SESSION['user_id']);
        return $reqResult;
    }

    public function doRejudgeFillByExamIdAndUserId($eid, $userId, $allscore) {
        $fillSum = 0;
        $fillarr = ExamService::instance()->getUserAnswer($eid, $userId, FillBaseModel::FILL_PROBLEM_TYPE);
        $row = SqlExecuteHelper::Teacher_GetFillAnswer4Exam($eid);
        if (empty($row)) {
            return 0;
        }

        foreach ($row as $key => $value) {
            if (isset($fillarr[$value['fill_id']][$value['answer_id']])
                && (!empty($fillarr[$value['fill_id']][$value['answer_id']])
                    || $fillarr[$value['fill_id']][$value['answer_id']] == "0")
            ) {

                $myanswer = trim($fillarr[$value['fill_id']][$value['answer_id']]);
                $rightans = trim($value['answer']);
                if ($myanswer == $rightans && strlen($myanswer) == strlen($rightans)) {
                    if ($value['kind'] == 1) {
                        $fillSum += $allscore['fillscore'];
                    } else if ($value['kind'] == 2) {
                        $fillSum = $fillSum + formatToFloatScore($allscore['prgans'] / $value['answernum']);
                    } else if ($value['kind'] == 3) {
                        $fillSum = $fillSum + formatToFloatScore($allscore['prgfill'] / $value['answernum']);
                    }
                }
            }
        }
        //fillover
        return $fillSum;
    }
}
