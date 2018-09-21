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
}
