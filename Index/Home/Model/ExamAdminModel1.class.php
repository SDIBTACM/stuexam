<?php
namespace Home\Model;

use Teacher\Model\ExamBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\StudentBaseModel;

class ExamAdminModel
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

    /**
     * 判断用户是否有权限参加此考试,判断包括:
     * 1.是否在权限列表 0
     * 2.考试是否存在或可见 -1
     * 3.如果是vip考试,是否在不同机器上登陆过  -2
     * 4.可选。是否已经交卷 -3
     * @param  number $eid 比赛编号
     * @param  string $user_id 用户ID]
     * @param  boolean $havetaken 是否判断已经参加考试过
     * @return number|array        返回数字表示没有权限，否则有
     */
    public function checkExamPrivilege($eid, $user_id, $havetaken = false) {
        $hasPrivilege = $this->getPrivilege($user_id, $eid);
        if (!(checkAdmin(2) || $hasPrivilege)) {
            return 0;
        }

        $field = array('title', 'start_time', 'end_time', 'isvip', 'visible');
        $row = ExamBaseModel::instance()->getExamInfoById($eid, $field);
        if (empty($row)) {
            return -1;
        }

        if (C('OJ_VIP_CONTEST')) {
            if ($row['isvip'] == 'Y') {
                $today = date('Y-m-d');
                $ip1 = $_SERVER['REMOTE_ADDR'];
                $sql = "SELECT `user_id` FROM `loginlog` WHERE `user_id`='$user_id' AND `time`>='$today' AND ip<>'$ip1' AND
				 `user_id` NOT IN( SELECT `user_id` FROM `privilege` WHERE `rightstr`='administrator' or `rightstr`='contest_creator') ORDER BY `time` DESC limit 0,1";
                $tmprow = M()->query($sql);
                if ($tmprow) {
                    return -2;
                }
            }
        }

        if ($havetaken) {
            $where = array(
                'user_id' => $user_id,
                'exam_id' => $eid
            );
            $field = array('score');
            $score = StudentBaseModel::instance()->queryOne($where, $field);
            if (!is_null($score['score']) && $score['score'] >= 0) {
                return -3;
            }
        }

        return $row;
    }

    /**
     * 判断比赛是否正在进行
     * @param  date $starttime 比赛开始时间
     * @param  date $endtime 比赛结束时间
     * @return number          -1=>已经结束 0=>未开始 1=>正在进行
     */
    public function getExamRunningStatus($starttime, $endtime) {
        $start_timeC = strtotime($starttime);
        $end_timeC = strtotime($endtime);
        $now = time();
        if ($now < $start_timeC) {
            return ExamBaseModel::EXAM_NOT_START;
        } else if ($now > $end_timeC) {
            return ExamBaseModel::EXAM_END;
        } else {
            return ExamBaseModel::EXAM_RUNNING;
        }
    }

    /**
     * 获取题目的打乱顺序
     * @param  number $eid 考试编号
     * @param  number $type 题目类型
     * @param  number $randnum 学生的随机码
     * @return array           打乱的顺序数组
     */
    public function getProblemSequence($eid, $type, $randnum) {
        $arr = array();
        $numproblem = M('exp_question')
            ->where('exam_id=%d and type=%d', $eid, $type)
            ->count('question_id');
        for ($i = 0; $i < $numproblem;) {
            if ($i + 11 <= $numproblem) {
                $arr = makesx($arr, $i, $i + 10, $randnum);
                $i = $i + 11;
            } else {
                $arr = makesx($arr, $i, $numproblem - 1, $randnum);
                break;
            }
        }
        return $arr;
    }

    /**
     * 判断用户是否在权限列表
     * @param  string $userId 用户ID
     * @param  number $eid 比赛编号
     * @return number        是否存在
     */
    private function getPrivilege($userId, $eid) {
        $res = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($userId, $eid);
        return !empty($res);
    }
}