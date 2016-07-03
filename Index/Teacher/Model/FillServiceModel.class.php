<?php
namespace Teacher\Model;

class FillServiceModel
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
        $fillid = I('post.fillid', 0, 'intval');
        $field = array('creator', 'isprivate');
        $tmp = FillBaseModel::instance()->getFillById($fillid, $field);
        if (empty($tmp) || !checkAdmin(4, $tmp['creator'])) {
            return -1;
        } else if ($tmp['isprivate'] == PrivilegeBaseModel::PROBLEM_SYSTEM && !checkAdmin(1)) {
            return -1;
        } else {
            $arr['question'] = test_input($_POST['fill_des']);
            $arr['point'] = implode(",", $_POST['point']);
            if (empty($arr['point'])) {
                return -2;
            }
            $arr['easycount'] = intval($_POST['easycount']);
            $arr['answernum'] = intval($_POST['numanswer']);
            $arr['kind'] = intval($_POST['kind']);
            $arr['isprivate'] = intval($_POST['isprivate']);
            $result = FillBaseModel::instance()->updateFillById($fillid, $arr);
            if ($result !== false) {
                $sql = "DELETE FROM `fill_answer` WHERE `fill_id`=$fillid";
                M()->execute($sql);
                $ins = array();
                for ($i = 1; $i <= $arr['answernum']; $i++) {
                    $answer = test_input($_POST["answer$i"]);
                    $ins[] = array("fill_id" => "$fillid", "answer_id" => "$i", "answer" => "$answer");
                }
                if ($arr['answernum']) {
                    M('fill_answer')->addAll($ins);
                }
                return 1;
            } else {
                return -2;
            }
        }
    }

    public function addFillInfo() {
        $arr['question'] = test_input($_POST['fill_des']);
        $arr['point'] = implode(",", $_POST['point']);
        if (empty($arr['point'])) {
            return false;
        }
        $arr['easycount'] = intval($_POST['easycount']);
        $arr['answernum'] = intval($_POST['numanswer']);
        $arr['kind'] = intval($_POST['kind']);
        $arr['isprivate'] = intval($_POST['isprivate']);
        $arr['addtime'] = date('Y-m-d H:i:s');
        $arr['creator'] = $_SESSION['user_id'];
        $fillid = FillBaseModel::instance()->insertFillInfo($arr);
        if ($fillid) {
            for ($i = 1; $i <= $arr['answernum']; $i++) {
                $answer = test_input($_POST["answer$i"]);
                $arr2['fill_id'] = $fillid;
                $arr2['answer_id'] = $i;
                $arr2['answer'] = $answer;
                M('fill_answer')->add($arr2);
            }
            return true;
        } else {
            return false;
        }
    }

    public function doRejudgeFillByExamIdAndUserId($eid, $userId, $allscore) {
        $fillSum = 0;
        $fillarr = ExamServiceModel::instance()->getUserAnswer($eid, $userId, FillBaseModel::FILL_PROBLEM_TYPE);
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
