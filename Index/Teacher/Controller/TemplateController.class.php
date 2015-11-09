<?php
namespace Teacher\Controller;

use Teacher\Model\ExamBaseModel;
use Think\Controller;

class TemplateController extends \Home\Controller\TemplateController
{

    public function _initialize() {
        parent::_initialize();
        if (!$this->isTeacher()) {
            $this->error('请先登陆管理员账号！');
        }
    }

    protected function isOwner4ExamByExamId($eid) {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $field = array('creator');
        $res = ExamBaseModel::instance()->getExamInfoById(intval($eid), $field);
        if (empty($res) || $res['creator'] != $this->userInfo['user_id']) {
            return false;
        } else {
            return true;
        }
    }

    protected function isOwner4ExamByUserId($userId) {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return ($userId == $this->userInfo['user_id']);
    }

    protected function isCanWatchInfo($eid, $isReturn = false) {
        $field = array('creator','isprivate', 'end_time');
        $res = ExamBaseModel::instance()->getExamInfoById(intval($eid), $field);

        $hasPrivilege = 0;
        if ($res['isprivate'] == 0 && $this->isCreator()) {
            $hasPrivilege = 1;
        }

        if (!($this->isSuperAdmin() || $this->isOwner4ExamByUserId($res['creator']) || $hasPrivilege !=0 )) {
            $this->error('You have no privilege of this exam');
        }
        if ($isReturn) {
            return $res;
        }
    }

    protected function isProblemCanDelete($private, $creator) {
        if ($this->isSuperAdmin()) {
            return true;
        } else {
            if ($private != 2) {
                return $this->isOwner4ExamByUserId($creator);
            } else {
                return false;
            }
        }
    }

    protected function checkProblemPrivate($private, $creator) {
        if ($private == 2 && !$this->isSuperAdmin()) {
            return -1;
        }
        if (!$this->isSuperAdmin()) {
            if ($private == 1 && $creator != $this->userInfo['user_id']) {
                return -1;
            }
        }
        return 1;
    }

    protected function checkQuestionHasAdded($eid, $type, $id) {
        $cnt = M('exp_question')
            ->where('exam_id=%d and type=%d and question_id=%d', $eid, $type, $id)
            ->count();
        return $cnt;
    }
}