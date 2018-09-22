<?php

namespace Teacher\Controller;

use Basic\Log;
use Constant\ReqResult\Result;

/**
 * Class AbsEventController
 *
 * @package \Teacher\Controller
 */
abstract class AbsEventController extends QuestionBaseController {
    public function _initialize() {
        parent::_initialize();
    }

    /**
     *  新增、更新
     */
    public function save() {
        if (!check_post_key()) {
            $this->echoError('发生错误！');
            Log::error("user id: {} post key error", $this->userInfo['user_id']);
        }
        $this->doSave();
    }

    abstract protected function doSave();

    /**
     * 删除
     */
    public function del() {
        if (!check_get_key() || I('get.id') == '') {
            $this->echoError('发生错误');
        }
        $id = I('get.id', 0, 'intval');
        $page = I('get.page', 1, 'intval');
        $this->doDelete($id, $page);
    }

    abstract protected function doDelete($id, $page);

    /**
     * 列表展示
     */
    public function showList() {
        $key = set_get_key();
        $isAdmin = $this->isSuperAdmin();

        $widgets = array(
            'mykey' => $key,
            'isadmin' => $isAdmin,
        );
        $this->ZaddWidgets($widgets);

        $this->getList();

        $this->auto_display("showlist");
    }

    abstract protected function getList();

    /**
     * 详情
     */
    public function index() {
        $page = I('get.page', 1, 'intval');
        $key = set_post_key();

        $this->zadd('page', $page);
        $this->zadd('mykey', $key);

        $this->getDetail();

        $this->auto_display();
    }

    abstract protected function getDetail();

    protected function checkReqResult(Result $result) {
        if ($result == null) {
            $this->echoError("网络错误, 请刷新页面!");
        }

        if ($result->getStatus()) {
            $page = I('post.page', 1, 'intval');
            $problem = I('post.problem', 0, 'intval');
            $this->success($result->getMessage(), U("Teacher/" . $result->getData() . "/showList",
                array('page' => $page, 'problem' => $problem)), 1);
        } else {
            $this->echoError($result->getMessage());
        }
    }
}
