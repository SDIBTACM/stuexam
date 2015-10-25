<?php
namespace Teacher\Controller;

use Teacher\Model\ExamModel;
use Think\Controller;

class TemplateController extends \Home\Controller\TemplateController
{
    public function _initialize() {
        parent::_initialize();
        if (!checkAdmin(3)) {
            $this->error('请先登陆管理员账号！');
        }
    }

    protected function isowner($eid) {
        $field = array('creator');
        $res = ExamModel::instance()->getExamInfoById(intval($eid), $field);
        if (empty($res) || !checkAdmin(4, $res['creator'])) {
            return false;
        }
        return true;
    }

    protected function isallow($eid, $isReturn = false) {
        $now = time();
        $field = array('creator, start_time, end_time');
        $res = ExamModel::instance()->getExamInfoById(intval($eid), $field);

        $hasPrivilege = 0;
        if ($now > strtotime($res['end_time']) && isset($_SESSION['contest_creator'])) {
            $hasPrivilege = 1;
        }

        if (!checkAdmin(5, $res['creator'], $hasPrivilege)) {
            $this->error('You have no privilege of this exam');
        }
        if ($isReturn) {
            return $res;
        }
    }

    protected function candel($pvt, $crt) {
        if (!checkAdmin(4, $crt)) {
            return false;
        } else if ($pvt == 2 && !checkAdmin(1)) {
            return false;
        }
        return true;
    }

    protected function checkadded($eid, $type, $id) {
        $cnt = M('exp_question')
            ->where('exam_id=%d and type=%d and question_id=%d', $eid, $type, $id)
            ->count();
        return $cnt;
    }
}