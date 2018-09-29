<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午3:14
 */

namespace Teacher\Controller;


use Home\Helper\PrivilegeHelper;
use Teacher\Model\PrivilegeBaseModel;

abstract class AbsQuestionController extends AbsEventController {
    public function _initialize() {
        parent::_initialize();
        if (IS_GET) {
            $this->ZaddChapters();
        }
    }

    public function showList() {
        $this->buildSearch();
        parent::showList();
    }

    /**
     * 题目详情
     */
    public function index() {
        $problemType = I('get.problem', 0, 'intval');
        $this->zadd('problemType', $problemType);
        parent::index();
    }

    /**
     * 添加题目到考试的列表展示
     */
    public function toExam() {
        $examId = I('get.eid', 0, 'intval');
        $problemType = I('get.type', 0, 'intval');
        $widgets = array(
            'eid' => $examId,
            'type' => $problemType
        );
        if (!$this->isOwner4ExamByExamId($examId)) {
            $this->echoError('You have no privilege of this exam~');
        } else {
            $this->ZaddWidgets($widgets);
        }

        $this->buildSearch();

        $isAdmin = $this->isSuperAdmin();
        $this->zadd("isadmin", $isAdmin);

        $this->getList();

        $this->zadd("added", $this->getQuestionHaveIn($examId));
        $this->auto_display("toexam");
    }

    abstract protected function getQuestionHaveIn($examId);

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
                return PrivilegeHelper::isExamOwner($creator);
            } else {
                return false;
            }
        }
    }
}
