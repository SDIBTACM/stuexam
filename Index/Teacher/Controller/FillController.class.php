<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午3:20
 */

namespace Teacher\Controller;


use Basic\Log;
use Teacher\Model\FillBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\QuestionPointBaseModel;
use Teacher\Model\StudentAnswerModel;
use Teacher\Service\FillService;
use Teacher\Service\KeyPointService;

class FillController extends AbsQuestionController
{
    function doSave() {
        $reqResult = null;
        if (isset($_POST['fillid'])) {
            $reqResult = FillService::instance()->updateFillInfo();
        } else if (isset($_POST['fill_des'])) {
            $reqResult = FillService::instance()->addFillInfo();
        }
        $this->checkReqResult($reqResult);
    }

    function doDelete($id, $page) {
        $tmp = FillBaseModel::instance()->getById($id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->echoError('You have no privilege!');
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: no privilege",
                $this->userInfo['user_id'], __FUNCTION__, $id);
        } else {
            FillBaseModel::instance()->delById($id);
            $sql = "DELETE FROM `fill_answer` WHERE `fill_id`=$id";
            M()->execute($sql);
            QuestionBaseModel::instance()->delQuestionByType($id, FillBaseModel::FILL_PROBLEM_TYPE);
            StudentAnswerModel::instance()->delAnswerByQuestionAndType($id, FillBaseModel::FILL_PROBLEM_TYPE);
            QuestionPointBaseModel::instance()->delByQuestion($id, FillBaseModel::FILL_PROBLEM_TYPE);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->success("填空题删除成功", U("Teacher/Index/fill", array('page' => $page)), 2);
        }
    }

    function index() {
        if (IS_GET && I('get.id') != '') {
            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $row = FillBaseModel::instance()->getById($id);
            if (empty($row)) {
                $this->echoError('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                $this->echoError('You have no privilege!');
                Log::info("user id: {} {} id: {}, require: change {} info, result: FAIL, reason: private question ",
                    $_SESSION['user_id'], __FUNCTION__, $id, __FUNCTION__);

            }
            if ($row['answernum'] != 0) {
                $ansrow = FillBaseModel::instance()->getFillAnswerByFillId($id);
                $this->zadd('ansrow', $ansrow);
            }
            $pnt = KeyPointService::instance()->getQuestionPoints($id, FillBaseModel::FILL_PROBLEM_TYPE);
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->zadd('problemType', $problemType);
            $this->auto_display("Add:fill");
        } else {
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $problemType = I('get.problem', 0, 'intval');
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->zadd('problemType', $problemType);
            $this->auto_display("Add:fill");
        }
    }

}