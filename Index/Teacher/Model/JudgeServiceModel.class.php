<?php
namespace Teacher\Model;

class JudgeServiceModel
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
        $judgeid = I('post.judgeid', 0, 'intval');
        $field = array('creator', 'isprivate');
        $tmp = JudgeBaseModel::instance()->getJudgeById($judgeid, $field);
        if (empty($tmp) || !checkAdmin(4, $tmp['creator'])) {
            return -1;
        } else if ($tmp['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !checkAdmin(1)) {
            return -1;
        } else {
            $arr['question'] = test_input($_POST['judge_des']);
            $arr['answer'] = $_POST['answer'];
            $arr['point'] = implode(",", $_POST['point']);
            if (empty($arr['point'])) {
                return -2;
            }
            $arr['easycount'] = intval($_POST['easycount']);
            $arr['isprivate'] = intval($_POST['isprivate']);
            $result = JudgeBaseModel::instance()->updateJudgeById($judgeid, $arr);
            if ($result !== false) {
                return 1;
            } else {
                return -2;
            }
        }
    }

    public function addJudgeInfo() {
        $arr['question'] = test_input($_POST['judge_des']);
        $arr['point'] = implode(",", $_POST['point']);
        if (empty($arr['point'])) {
            return false;
        }
        $arr['answer'] = $_POST['answer'];
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $arr['creator'] = $_SESSION['user_id'];
        $arr['addtime'] = date('Y-m-d H:i:s');
        $lastId = JudgeBaseModel::instance()->insertJudgeInfo($arr);
        return $lastId ? true : false;
    }

    public function doRejudgeJudgeByExamIdAndUserId($eid, $userId, $judgeScore) {
        $judgeSum = 0;
        $judgearr = ExamServiceModel::instance()->getUserAnswer($eid, $userId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);;
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