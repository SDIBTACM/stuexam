<?php
namespace Teacher\Service;

use Basic\Log;
use Constant\ReqResult\Result;
use Home\Helper\PrivilegeHelper;
use Home\Helper\SqlExecuteHelper;
use Teacher\Convert\ChooseConvert;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\PrivilegeBaseModel;

class ChooseService
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

    public function updateChooseInfo() {
        $reqResult = new Result();
        $chooseid = I('post.chooseid', 0, 'intval');
        $field = array('creator', 'isprivate', 'private_code');
        $_chooseInfo = ChooseBaseModel::instance()->getById($chooseid, $field);
        if (empty($_chooseInfo) || !PrivilegeHelper::isExamOwner($_chooseInfo['creator'])) {
            return Result::errorResult("您没有权限进行此操作!");
        }

        if ($_chooseInfo['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !PrivilegeHelper::isSuperAdmin()) {
            return Result::errorResult("您没有权限进行此操作!");
        }

        $arr = ChooseConvert::convertChooseFromPost();

        // 如果 code 发生变化
        if (strcmp($arr['private_code'], $_chooseInfo['private_code'])) {
            $privateCodeCheck = ChooseBaseModel::instance()->getByPrivateCode(
                $arr['private_code']
            );
            if (!empty($privateCodeCheck)) {
                return Result::errorResult("该私有编号已经有题目设置, 不能重复设置");
            }
        }

        $result = ChooseBaseModel::instance()->updateById($chooseid, $arr);
        if ($result !== false) {
            $pointIds = I('post.point', array());
            KeyPointService::instance()->saveExamPoint(
                $pointIds, $chooseid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE
            );
            $reqResult->setMessage("选择题修改成功!");
            $reqResult->setData("choose");
            Log::info("user id:{} choose id: {}, require: change choose info, result: success",
                $_SESSION['user_id'], $chooseid);
            return $reqResult;
        } else {
            Log::warn("user id: {} exam id: {}, require: change choose info, result: FAIL, sqldate: {}, sqlresult: {}",
                $_SESSION['user_id'], $chooseid, $arr, $result);
            return Result::errorResult("选择题修改失败!");
        }
    }

    public function addChooseInfo() {
        $reqResult = new Result();
        $arr = ChooseConvert::convertChooseFromPost();
        $arr['creator'] = $_SESSION['user_id'];
        $arr['addtime'] = date('Y-m-d H:i:s');

        $privateCodeCheck = ChooseBaseModel::instance()->getByPrivateCode(
            $arr['private_code']
        );
        if (!empty($privateCodeCheck)) {
            return Result::errorResult("该私有编号已经有题目设置, 不能重复设置");
        }

        $lastId = ChooseBaseModel::instance()->insertData($arr);
        if ($lastId <= 0) {
            Log::warn("user id:{}, require: add choose, result: FAIL, sqldate: {}, sqlresult: {}",
                $_SESSION['user_id'], $arr, $lastId);
            return Result::errorResult("选择题添加失败!");
        }

        $pointIds = I('post.point', array());
        KeyPointService::instance()->saveExamPoint(
            $pointIds, $lastId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE
        );
        $reqResult->setMessage("选择题添加成功!");
        $reqResult->setData("choose");
        Log::info("user id:{}, require: addd choose, result: success", $_SESSION['user_id']);
        return $reqResult;
    }

    public function doRejudgeChooseByExamIdAndUserId($eid, $userId, $chooseScore) {
        $chooseSum = 0;
        $choosearr = ExamService::instance()->getUserAnswer($eid, $userId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $row = SqlExecuteHelper::Teacher_GetChooseAnswer4Exam($eid);
        if ($row) {
            foreach ($row as $key => $value) {
                if (isset($choosearr[$value['choose_id']])) {
                    $myanswer = $choosearr[$value['choose_id']];
                    if ($myanswer == $value['answer'])
                        $chooseSum += $chooseScore;
                }
            }
        }
        return $chooseSum;
        //choose over
    }
}
