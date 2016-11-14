<?php
namespace Teacher\Controller;

use Constant\Constants\Chapter;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\PrivilegeBaseModel;
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

        $hasPrivilege = false;
        if ($res['isprivate'] == PrivilegeBaseModel::PROBLEM_PUBLIC && $this->isCreator()) {
            $hasPrivilege = true;
        }

        if (!($this->isSuperAdmin() || $this->isOwner4ExamByUserId($res['creator']) || $hasPrivilege)) {
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
            if ($private != PrivilegeBaseModel::PROBLEM_SYSTEM) {
                return $this->isOwner4ExamByUserId($creator);
            } else {
                return false;
            }
        }
    }

    protected function checkProblemPrivate($private, $creator) {
        if ($private == PrivilegeBaseModel::PROBLEM_SYSTEM && !$this->isSuperAdmin()) {
            return -1;
        }
        if (!$this->isSuperAdmin()) {
            if ($private == PrivilegeBaseModel::PROBLEM_PRIVATE && $creator != $this->userInfo['user_id']) {
                return -1;
            }
        }
        return 1;
    }

    protected function ZaddChapters() {
        $chapters = Chapter::getConstant();
        $chapterMap = array();
        foreach ($chapters as $chapter) {
            if ($chapter instanceof Chapter) {
                $chapterMap[$chapter->getId()] = $chapter->getName();
            }
        }
        $this->zadd('chapters', $chapterMap);
    }
}