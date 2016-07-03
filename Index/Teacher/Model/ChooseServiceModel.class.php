<?php
namespace Teacher\Model;

class ChooseServiceModel
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
        $chooseid = I('post.chooseid', 0, 'intval');
        $field = array('creator', 'isprivate');
        $tmp = ChooseBaseModel::instance()->getChooseById($chooseid, $field);
        if (empty($tmp) || !checkAdmin(4, $tmp['creator'])) {
            return -1;
        } else if ($tmp['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !checkAdmin(1)) {
            return -1;
        } else {
            $arr['question'] = test_input($_POST['choose_des']);
            $arr['ams'] = test_input($_POST['ams']);
            $arr['bms'] = test_input($_POST['bms']);
            $arr['cms'] = test_input($_POST['cms']);
            $arr['dms'] = test_input($_POST['dms']);
            $arr['point'] = implode(",", $_POST['point']);
            if (empty($arr['point'])) {
                return -2;
            }
            $arr['answer'] = $_POST['answer'];
            $arr['easycount'] = intval($_POST['easycount']);
            $arr['isprivate'] = intval($_POST['isprivate']);
            $result = ChooseBaseModel::instance()->updateChooseById($chooseid, $arr);
            if ($result !== false) {
                return 1;
            } else {
                return -2;
            }
        }
    }

    public function addChooseInfo() {
        $arr['question'] = test_input($_POST['choose_des']);
        $arr['ams'] = test_input($_POST['ams']);
        $arr['bms'] = test_input($_POST['bms']);
        $arr['cms'] = test_input($_POST['cms']);
        $arr['dms'] = test_input($_POST['dms']);
        $arr['answer'] = $_POST['answer'];
        $arr['creator'] = $_SESSION['user_id'];
        $arr['point'] = implode(",", $_POST['point']);
        if (empty($arr['point'])) {
            return false;
        }
        $arr['addtime'] = date('Y-m-d H:i:s');
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $lastId = ChooseBaseModel::instance()->insertChooseInfo($arr);
        return $lastId ? true : false;
    }

    public function doRejudgeChooseByExamIdAndUserId($eid, $userId, $chooseScore) {
        $chooseSum = 0;
        $choosearr = ExamServiceModel::instance()->getUserAnswer($eid, $userId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
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