<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午3:14
 */

namespace Teacher\Controller;


use Basic\Log;
use Constant\ReqResult\Result;

abstract class AbsQuestionController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
        if (IS_GET) {
            $this->ZaddChapters();
        }
    }

    protected function save() {
        if (!check_post_key()) {
        $this->echoError('发生错误！');
            Log::error("user id: {} post key error", $this->userInfo['user_id']);
        }
        $this->doSave();
    }

    abstract function doSave();

    protected function del() {
        if (!check_get_key() || I('get.id') == '') {
            $this->echoError('发生错误');
        }
        $id = I('get.id', 0, 'intval');
        $page = I('get.page', 1, 'intval');
        $this->doDelete($id, $page);
    }

    abstract function doDelete($id, $page);

    abstract function index();

    protected function checkReqResult(Result $result) {
        if ($result == null) {
            $this->echoError("网络错误, 请刷新页面!");
        }

        if ($result->getStatus()) {
            $page = I('post.page', 1, 'intval');
            $problem = I('post.problem', 0, 'intval');
            $this->success($result->getMessage(), U("Teacher/Index/" . $result->getData(),
                array('page' => $page, 'problem' => $problem)), 1);
        } else {
            $this->echoError($result->getMessage());
        }
    }
}