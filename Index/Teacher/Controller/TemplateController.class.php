<?php
namespace Teacher\Controller;

use Teacher\Model\ExamBaseModel;
use Think\Controller;

class TemplateController extends \Home\Controller\TemplateController
{

    public function _initialize() {
        parent::_initialize();
        if (!$this->isTeacher()) {
            $this->echoError('请先登陆管理员账号！');
        }
    }

    /**
     * 是否是某场考试的拥有者,其中超级管理员永远一切权限
     * @param $eid
     * @return bool
     */
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

    /**
     * 当前登录用户是否跟创建者一致
     * @param $userId
     * @return bool
     */
    protected function isOwner4ExamByUserId($userId) {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return ($userId == $this->userInfo['user_id']);
    }

    /**
     * 是否可以查看某场考试的信息
     * @param $eid
     * @param bool|false $isReturn
     * @return mixed
     */
    protected function isCanWatchInfo($eid, $isReturn = false) {
        $field = array('creator','isprivate', 'end_time');
        $res = ExamBaseModel::instance()->getExamInfoById(intval($eid), $field);

        $hasPrivilege = 0;
        if ($res['isprivate'] == 0 && $this->isCreator()) {
            $hasPrivilege = 1;
        }

        if (!($this->isSuperAdmin() || $this->isOwner4ExamByUserId($res['creator']) || $hasPrivilege !=0 )) {
            $this->echoError('You have no privilege of this exam');
        }
        if ($isReturn) {
            return $res;
        }
    }

    /**
     * 当前登录用户是否可以删除某题目
     * @param $private
     * @param $creator
     * @return bool
     */
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