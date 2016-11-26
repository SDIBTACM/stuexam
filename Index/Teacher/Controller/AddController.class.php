<?php
namespace Teacher\Controller;

use Constant\ReqResult\Result;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\QuestionBaseModel;

use Teacher\Service\ChooseService;
use Teacher\Service\ExamService;
use Teacher\Service\FillService;
use Teacher\Service\JudgeService;
use Teacher\Service\KeyPointService;


class AddController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
        if (IS_GET) {
            $this->ZaddChapters();
        }
    }

    public function exam() {
        if (IS_POST) {
            if (!check_post_key()) {
                $this->echoError('发生错误！');
            }
            if (!$this->isCreator()) {
                $this->echoError('You have no privilege!');
            }
            $reqResult = null;
            if (isset($_POST['examid'])) {
                $reqResult = ExamService::instance()->updateExamInfo();
            } else if (isset($_POST['examname'])) {
                $reqResult = ExamService::instance()->addExamInfo();
            }
            $this->checkReqResult($reqResult);
        } else if (IS_GET && I('get.eid') != '') {
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
            $this->auto_display();
        } else {
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->auto_display();
        }
    }

    public function choose() {
        if (IS_POST) {
            if (!check_post_key()) {
                $this->echoError('发生错误！');
            }
            $reqResult = null;
            if (isset($_POST['chooseid'])) {
                $reqResult = ChooseService::instance()->updateChooseInfo();
            } else if (isset($_POST['choose_des'])) {
                $reqResult = ChooseService::instance()->addChooseInfo();
            }
            $this->checkReqResult($reqResult);
        } else if (IS_GET && I('get.id') != '') {
            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $row = ChooseBaseModel::instance()->getById($id);
            if (empty($row)) {
                $this->error('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                $this->echoError('You have no privilege!');
            }
            $pnt = KeyPointService::instance()->getQuestionPoints($id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->zadd('problemType', $problemType);
            $this->zadd('pnt', $pnt);
            $this->auto_display();
        } else {
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->zadd('problemType', $problemType);
            $this->auto_display();
        }
    }

    public function judge() {
        if (IS_POST) {

            if (!check_post_key()) {
                $this->echoError('发生错误！');
            }
            $reqResult = null;
            if (isset($_POST['judgeid'])) {
                $reqResult = JudgeService::instance()->updateJudgeInfo();
            } else if (isset($_POST['judge_des'])) {
                $reqResult = JudgeService::instance()->addJudgeInfo();
            }
            $this->checkReqResult($reqResult);
        } else if (IS_GET && I('get.id') != '') {

            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $row = JudgeBaseModel::instance()->getById($id);
            if (empty($row)) {
                $this->echoError('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                $this->echoError('You have no privilege!');
            }
            $pnt = KeyPointService::instance()->getQuestionPoints($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->zadd('problemType', $problemType);
            $this->auto_display();
        } else {

            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('problemType', $problemType);
            $this->zadd('mykey', $key);
            $this->auto_display();
        }
    }

    public function fill() {
        if (IS_POST) {
            if (!check_post_key()) {
                $this->echoError('发生错误！');
            }
            $reqResult = null;
            if (isset($_POST['fillid'])) {
                $reqResult = FillService::instance()->updateFillInfo();
            } else if (isset($_POST['fill_des'])) {
                $reqResult = FillService::instance()->addFillInfo();
            }
            $this->checkReqResult($reqResult);
        } else if (IS_GET && I('get.id') != '') {
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
            $this->auto_display();
        } else {
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $problemType = I('get.problem', 0, 'intval');
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->zadd('problemType', $problemType);
            $this->auto_display();
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
            $this->success('考试复制成功!', U('/Teacher'), 1);
        }
    }

    private function checkReqResult(Result $result) {
        if ($result == null) {
            $this->echoError("网络错误, 请刷新页面!");
        }

        if ($result->getStatus()) {
            $page = I('post.page', 1, 'intval');
            $problem = I('post.problem', 0, 'intval');
            $this->success($result->getMessage(), U("Teacher/Index/" . $result->getData(), array('page' => $page, 'problem' => $problem)), 1);
        } else {
            $this->echoError($result->getMessage());
        }
    }
}
