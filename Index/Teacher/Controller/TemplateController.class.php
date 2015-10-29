<?php
namespace Teacher\Controller;

use Teacher\Model\ExamModel;
use Think\Controller;

class TemplateController extends \Home\Controller\TemplateController
{
    public function _initialize() {
        parent::_initialize();
        if (!$this->isTeacher()) {
            $this->error('请先登陆管理员账号！');
        }
    }

    protected function isowner($eid) {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $field = array('creator');
        $res = ExamModel::instance()->getExamInfoById(intval($eid), $field);
        if (empty($res) || $res['creator'] != $this->userInfo['user_id']) {
            return false;
        } else {
            return true;
        }
    }

    protected function isOwnerByUserId($userId) {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return ($userId == $this->userInfo['user_id']);
    }

    protected function isCanWatchInfo($eid, $isReturn = false) {
        $field = array('creator','isPrivate');
        $res = ExamModel::instance()->getExamInfoById(intval($eid), $field);

        $hasPrivilege = 0;
        if ($res['isPrivate'] == 0 && $this->isCreator()) {
            $hasPrivilege = 1;
        }

        if (!($this->isSuperAdmin() || $this->isOwnerByUserId($res['creator']) || $hasPrivilege !=0 )) {
            $this->error('You have no privilege of this exam');
        }
        if ($isReturn) {
            return $res;
        }
    }

    protected function isProblemCanDelete($pvt, $crt) {
        if ($this->isSuperAdmin()) {
            return true;
        } else {
            if ($pvt != 2) {
                return $this->isOwnerByUserId($crt);
            } else {
                return false;
            }
        }
    }

    protected function checkadded($eid, $type, $id) {
        $cnt = M('exp_question')
            ->where('exam_id=%d and type=%d and question_id=%d', $eid, $type, $id)
            ->count();
        return $cnt;
    }
}