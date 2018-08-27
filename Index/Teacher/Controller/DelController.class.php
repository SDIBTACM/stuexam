<?php
namespace Teacher\Controller;

use Basic\Log;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\QuestionPointBaseModel;
use Teacher\Model\StudentAnswerModel;
use Think\Controller;

class DelController extends TemplateController
{

    private $id = null;
    private $page = null;

    public function _initialize() {
        parent::_initialize();
        if (!check_get_key() || I('get.id') == '') {
            $this->echoError('发生错误');
        }
        $this->id = I('get.id', 0, 'intval');
        $this->page = I('get.page', 1, 'intval');
    }

    public function exam() {
        if (!$this->isOwner4ExamByExamId($this->id)) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: privilege",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
            $this->echoError('You have no privilege!');
        } else {
            $data = array('visible' => 'N');
            ExamBaseModel::instance()->updateById($this->id, $data);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
            $this->success("考试删除成功", U("Teacher/Index/index", array('page' => $this->page)), 2);
        }
    }

    public function choose() {
        $tmp = ChooseBaseModel::instance()->getById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: no privilege",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
            $this->echoError('You have no privilege!');
        } else {
            ChooseBaseModel::instance()->delById($this->id);
            QuestionBaseModel::instance()->delQuestionByType($this->id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            StudentAnswerModel::instance()->delAnswerByQuestionAndType($this->id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            QuestionPointBaseModel::instance()->delByQuestion($this->id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
            $this->success("选择题删除成功", U("Teacher/Index/choose", array('page' => $this->page)), 2);
        }
    }

    public function judge() {
        $tmp = JudgeBaseModel::instance()->getById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: no privilege",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
            $this->echoError('You have no privilege!');
        } else {
            JudgeBaseModel::instance()->delById($this->id);
            QuestionBaseModel::instance()->delQuestionByType($this->id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            StudentAnswerModel::instance()->delAnswerByQuestionAndType($this->id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            QuestionPointBaseModel::instance()->delByQuestion($this->id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
            $this->success("判断题删除成功", U("Teacher/Index/judge", array('page' => $this->page)), 2);
        }
    }

    public function fill() {
        $tmp = FillBaseModel::instance()->getById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->echoError('You have no privilege!');
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: no privilege",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
        } else {
            FillBaseModel::instance()->delById($this->id);
            $sql = "DELETE FROM `fill_answer` WHERE `fill_id`=$this->id";
            M()->execute($sql);
            QuestionBaseModel::instance()->delQuestionByType($this->id, FillBaseModel::FILL_PROBLEM_TYPE);
            StudentAnswerModel::instance()->delAnswerByQuestionAndType($this->id, FillBaseModel::FILL_PROBLEM_TYPE);
            QuestionPointBaseModel::instance()->delByQuestion($this->id, FillBaseModel::FILL_PROBLEM_TYPE);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $this->id);
            $this->success("填空题删除成功", U("Teacher/Index/fill", array('page' => $this->page)), 2);
        }
    }
}