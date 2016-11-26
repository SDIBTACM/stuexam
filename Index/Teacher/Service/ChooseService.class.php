<?php
namespace Teacher\Service;

use Constant\ReqResult\Result;
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
        $field = array('creator', 'isprivate');
        $_chooseInfo = ChooseBaseModel::instance()->getById($chooseid, $field);
        if (empty($_chooseInfo) || !checkAdmin(4, $_chooseInfo['creator'])) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("您没有权限进行此操作!");
        } else if ($_chooseInfo['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !checkAdmin(1)) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("您没有权限进行此操作!");
        } else {
            $arr = ChooseConvert::convertChooseFromPost();
            $result = ChooseBaseModel::instance()->updateById($chooseid, $arr);
            if ($result !== false) {
                $pointIds = I('post.point', array());
                KeyPointService::instance()->saveExamPoint(
                    $pointIds, $chooseid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE
                );
                $reqResult->setMessage("选择题修改成功!");
                $reqResult->setData("choose");
            } else {
                $reqResult->setStatus(false);
                $reqResult->setMessage("选择题修改失败!");
            }
        }
        return $reqResult;
    }

    public function addChooseInfo() {
        $reqResult = new Result();
        $arr = ChooseConvert::convertChooseFromPost();
        $arr['creator'] = $_SESSION['user_id'];
        $arr['addtime'] = date('Y-m-d H:i:s');
        $lastId = ChooseBaseModel::instance()->insertData($arr);
        if ($lastId) {
            $pointIds = I('post.point', array());
            KeyPointService::instance()->saveExamPoint(
                $pointIds, $lastId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE
            );
            $reqResult->setMessage("选择题添加成功!");
            $reqResult->setData("choose");
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("选择题添加失败!");
        }
        return $reqResult;
    }

    public function doRejudgeChooseByExamIdAndUserId($eid, $userId, $chooseScore) {
        $chooseSum = 0;
        $choosearr = ExamService::instance()->getUserAnswer($eid, $userId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $query = "SELECT `choose_id`,`answer` FROM `ex_choose` WHERE `choose_id` IN
		(SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='1')";
        $row = M()->query($query);
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