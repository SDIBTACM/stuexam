<?php
namespace Teacher\Model;

class AdminchooseModel
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

    public function upd_question() {
        $chooseid = I('post.chooseid', 0, 'intval');
        $field = array('creator', 'isprivate');
        $tmp = ChooseBaseModel::instance()->getChooseById($chooseid, $field);
        if (empty($tmp) || !checkAdmin(4, $tmp['creator'])) {
            return -1;
        } else if ($tmp['isprivate'] == 2 && !checkAdmin(1)) {
            return -1;
        } else {
            $arr['question'] = test_input($_POST['choose_des']);
            $arr['ams'] = test_input($_POST['ams']);
            $arr['bms'] = test_input($_POST['bms']);
            $arr['cms'] = test_input($_POST['cms']);
            $arr['dms'] = test_input($_POST['dms']);
            $arr['point'] = test_input($_POST['point']);
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

    public function add_question() {
        $arr['question'] = test_input($_POST['choose_des']);
        $arr['ams'] = test_input($_POST['ams']);
        $arr['bms'] = test_input($_POST['bms']);
        $arr['cms'] = test_input($_POST['cms']);
        $arr['dms'] = test_input($_POST['dms']);
        $arr['answer'] = $_POST['answer'];
        $arr['creator'] = $_SESSION['user_id'];
        $arr['point'] = test_input($_POST['point']);
        $arr['addtime'] = date('Y-m-d H:i:s');
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $lastId = ChooseBaseModel::instance()->insertChooseInfo($arr);
        return $lastId ? true : false;
    }
}