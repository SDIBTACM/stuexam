<?php
namespace Teacher\Controller;

use Constant\Constants\Chapter;
use Home\Helper\PrivilegeHelper;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\PrivilegeBaseModel;

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
        $res = ExamBaseModel::instance()->getById(intval($eid), $field);
        return !empty($res) && PrivilegeHelper::isExamOwner($res['creator']);
    }

    /**
     * 是否可以查看某场考试的信息
     * @param $eid
     * @param bool|false $isReturn
     * @return mixed
     */
    protected function isCanWatchInfo($eid, $isReturn = false) {
        $field = array('creator','isprivate', 'end_time');
        $res = ExamBaseModel::instance()->getById(intval($eid), $field);

        $hasPrivilege = false;
        if ($res['isprivate'] == PrivilegeBaseModel::PROBLEM_PUBLIC && $this->isCreator()) {
            $hasPrivilege = true;
        }

        if (!(PrivilegeHelper::isExamOwner(($res['creator'])) || $hasPrivilege)) {
            $this->echoError('You have no privilege of this exam');
        }
        if ($isReturn) {
            return $res;
        }
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
