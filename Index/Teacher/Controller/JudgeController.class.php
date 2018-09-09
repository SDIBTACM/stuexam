<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午3:13
 */

namespace Teacher\Controller;


use Basic\Log;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\QuestionPointBaseModel;
use Teacher\Model\StudentAnswerModel;
use Teacher\Service\JudgeService;
use Teacher\Service\KeyPointService;

class JudgeController extends AbsQuestionController
{
    function doSave() {
        $reqResult = null;
        if (isset($_POST['judgeid'])) {
            $reqResult = JudgeService::instance()->updateJudgeInfo();
        } else if (isset($_POST['judge_des'])) {
            $reqResult = JudgeService::instance()->addJudgeInfo();
        }
        $this->checkReqResult($reqResult);
    }

    function doDelete($id, $page) {
        $tmp = JudgeBaseModel::instance()->getById($id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: no privilege",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->echoError('You have no privilege!');
        } else {
            JudgeBaseModel::instance()->delById($id);
            QuestionBaseModel::instance()->delQuestionByType($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            StudentAnswerModel::instance()->delAnswerByQuestionAndType($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            QuestionPointBaseModel::instance()->delByQuestion($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->success("判断题删除成功", U("Teacher/Index/judge", array('page' => $page)), 2);
        }
    }

    function index() {
        if (IS_GET && I('get.id') != '') {

            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $row = JudgeBaseModel::instance()->getById($id);
            if (empty($row)) {
                $this->echoError('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                Log::info("user id: {} {} id: {}, require: change {} info, result: FAIL, reason: private question ",
                    $_SESSION['user_id'], __FUNCTION__, $id, __FUNCTION__);
                $this->echoError('You have no privilege!');
            }
            $pnt = KeyPointService::instance()->getQuestionPoints($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->zadd('problemType', $problemType);
            $this->auto_display("Add:judge");
        } else {

            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('problemType', $problemType);
            $this->zadd('mykey', $key);
            $this->auto_display("Add:judge");
        }
    }

}