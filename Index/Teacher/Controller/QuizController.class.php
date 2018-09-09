<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午4:39
 */

namespace Teacher\Controller;


use Basic\Log;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Service\ExamService;

class QuizController extends AbsQuestionController
{
    protected function doSave() {
        if (!check_post_key()) {
            $this->echoError('发生错误！');
            Log::error("user id: {} post key error", $this->userInfo['user_id']);
        }
        if (!$this->isCreator()) {
            Log::info("user id:{} {} id: {}, require: change {} info, result: FAIL, reason: no admin or creator ",
                $this->userInfo['user_id'], __FUNCTION__, I('get.eid', 0, 'intval'), __FUNCTION__);
            $this->echoError('You have no privilege!');
        }
        $reqResult = null;
        if (isset($_POST['examid'])) {
            $reqResult = ExamService::instance()->updateExamInfo();
        } else if (isset($_POST['examname'])) {
            $reqResult = ExamService::instance()->addExamInfo();
        }
        $this->checkReqResult($reqResult);
    }

    protected function doDelete($id, $page) {
        if (!$this->isOwner4ExamByExamId($id)) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: privilege",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->echoError('You have no privilege!');
        } else {
            $data = array('visible' => 'N');
            ExamBaseModel::instance()->updateById($id, $data);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->success("考试删除成功", U("Teacher/Index/index", array('page' => $page)), 2);
        }
    }

    public function index() {
        if (IS_GET && I('get.eid') != '') {
            $examId = I('get.eid', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $examInfo = ExamBaseModel::instance()->getExamInfoById($examId);
            if (empty($examInfo)) {
                $this->echoError('No Such Exam!');
            }
            if (!$this->isOwner4ExamByUserId($examInfo['creator'])) {
                $this->echoError('You have no privilege!');
            }
            $this->zadd('page', $page);
            $this->zadd('row', $examInfo);
            $this->zadd('mykey', $key);
            $this->auto_display("Add:exam");
        } else {
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->auto_display("Add:exam");
        }
    }

    public function copyOneExam() {
        $eid = I('get.eid', 0, 'intval');
        $row = ExamBaseModel::instance()->getExamInfoById($eid);
        if (empty($row)) {
            $this->echoError("No Such Exam!");
        }
        if (!$this->isOwner4ExamByUserId($row['creator'])) {
            $this->echoError('You have no privilege!');
        } else {
            // copy exam's base info
            unset($row['exam_id']);
            $row['creator'] = $this->userInfo['user_id'];
            $examId = ExamBaseModel::instance()->insertData($row);
            if (empty($examId)) {
                Log::warn("user id: {}, require: clone exam id: {} , result: FAIL", $this->userInfo['user_id'], $eid);
                $this->echoError("复制考试失败,请刷新页面重试");
            }
            // copy exam's problem
            $field = array('exam_id', 'question_id', 'type');
            $res = QuestionBaseModel::instance()->getQuestionByExamId($eid, $field);
            foreach ($res as &$r) {
                $r['exam_id'] = $examId;
            }
            unset($r);
            QuestionBaseModel::instance()->insertQuestions($res);
            Log::info("user id: {}, require: clone exam id: {} , result: success", $this->userInfo['user_id'], $examId);
            $this->success('考试复制成功!', U('/Teacher'), 1);
        }
    }

}