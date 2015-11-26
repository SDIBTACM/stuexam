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
            $arr['point'] = test_input($_POST['point']);
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
        $arr['point'] = test_input($_POST['point']);
        $arr['answer'] = $_POST['answer'];
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $arr['creator'] = $_SESSION['user_id'];
        $arr['addtime'] = date('Y-m-d H:i:s');
        $lastId = JudgeBaseModel::instance()->insertJudgeInfo($arr);
        return $lastId ? true : false;
    }
}