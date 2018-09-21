<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午3:14
 */

namespace Teacher\Controller;


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
        $this->auto_display();
    }

    abstract protected function getQuestionHaveIn($examId);
}
