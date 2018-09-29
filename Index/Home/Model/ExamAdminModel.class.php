<?php
namespace Home\Model;

use Constant\ReqResult\Result;
use Home\Helper\PrivilegeHelper;
use Home\Helper\SqlExecuteHelper;
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
     * @param  number $eid 比赛编号
     * @param  string $user_id 用户ID]
     * @param  boolean $judgeHaveTaken 是否判断已经参加考试过
     * @return Result  如果成功返回该考试的信息
     */

    public function checkExamPrivilege($eid, $user_id, $judgeHaveTaken = false) {
        $hasPrivilege = $this->getPrivilege($user_id, $eid);
        if (!(PrivilegeHelper::isCreator() || $hasPrivilege)) {
            return Result::errorResult("You have no privilege!");
        }

        $row = ExamBaseModel::instance()->getById($eid);
        if (empty($row)) {
            return Result::errorResult("No Such Exam!");
        }

        if (C('OJ_VIP_CONTEST') && $row['isvip'] == 'Y') {
            $today = date('Y-m-d');
            $ip1 = $_SERVER['REMOTE_ADDR'];
            $tmpRow = SqlExecuteHelper::Home_GetUserLoginLog($user_id, $today, $ip1);
            if ($tmpRow) {
                return Result::errorResult("Do not login in diff machine,Please Contact administrator");
            }
        }

        if ($judgeHaveTaken) {
            $where = array(
                'user_id' => $user_id,
                'exam_id' => $eid
            );
            $field = array('score');
            $score = StudentBaseModel::instance()->queryOne($where, $field);
            if (!is_null($score['score']) && $score['score'] >= 0) {
                return Result::errorResult("You have taken part in it");
            }
        }

        return Result::successResultWithData($row);

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
     * 判断用户是否在权限列表
     * @param  string $userId 用户ID
     * @param  number $eid 比赛编号
     * @return boolean    是否存在
     */
    private function getPrivilege($userId, $eid) {
        $res = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($userId, $eid);
        return !empty($res);
    }
}
