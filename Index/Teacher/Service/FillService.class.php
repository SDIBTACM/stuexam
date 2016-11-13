<?php
namespace Teacher\Service;

use Constant\ReqResult\Result;
use Teacher\Convert\FillConvert;

use Teacher\Model\FillBaseModel;
use Teacher\Model\PrivilegeBaseModel;

class FillService
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

    public function updateFillInfo() {
        $reqResult = new Result();
        $fillId = I('post.fillid', 0, 'intval');
        $field = array('creator', 'isprivate');
        $_fillInfo = FillBaseModel::instance()->getById($fillId, $field);
        if (empty($_fillInfo) || !checkAdmin(4, $_fillInfo['creator'])) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("您没有权限进行此操作!");
        } else if ($_fillInfo['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !checkAdmin(1)) {
            $reqResult->setStatus(false);
            $reqResult->setMessage("您没有权限进行此操作!");
        } else {
            $arr = FillConvert::convertFillFromPost();
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
            } else {
                $reqResult->setStatus(false);
                $reqResult->setMessage("填空题修改失败!");
            }
        }
        return $reqResult;
    }

    public function addFillInfo() {
        $reqResult = new Result();
        $arr = FillConvert::convertFillFromPost();
        $arr['addtime'] = date('Y-m-d H:i:s');
        $arr['creator'] = $_SESSION['user_id'];
        $fillId = FillBaseModel::instance()->insertData($arr);
        if ($fillId) {
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
        } else {
            $reqResult->setStatus(false);
            $reqResult->setMessage("填空题添加失败!");
        }
        return $reqResult;
    }

    public function doRejudgeFillByExamIdAndUserId($eid, $userId, $allscore) {
        $fillSum = 0;
        $fillarr = ExamService::instance()->getUserAnswer($eid, $userId, FillBaseModel::FILL_PROBLEM_TYPE);
        $query = "SELECT `fill_answer`.`fill_id`,`answer_id`,`answer`,`answernum`,`kind` FROM `fill_answer`,`ex_fill` WHERE
		`fill_answer`.`fill_id`=`ex_fill`.`fill_id` AND `fill_answer`.`fill_id` IN ( SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='3')";
        $row = M()->query($query);
        if ($row) {
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
                            $fillSum = $fillSum + $allscore['prgans'] / $value['answernum'];
                        } else if ($value['kind'] == 3) {
                            $fillSum = $fillSum + $allscore['prgfill'] / $value['answernum'];
                        }
                    }
                }
            }
        }
        //fillover
        return $fillSum;
    }
}
