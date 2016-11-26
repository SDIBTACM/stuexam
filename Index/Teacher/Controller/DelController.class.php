<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\QuestionPointBaseModel;
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
            $this->echoError('You have no privilege!');
        } else {
            $data = array('visible' => 'N');
            ExamBaseModel::instance()->updateById($this->id, $data);
            $this->success("考试删除成功", U("Teacher/Index/index", array('page' => $this->page)), 2);
            //if the exam was deleted
            //the info of exam was deleted
            // $query="DELETE FROM `exp_question` WHERE `exam_id`='$id'";
            // $query="DELETE FROM `ex_privilege` WHERE `rightstr`='e$id'";
            // $query="DELETE FROM `ex_stuanswer` WHERE `exam_id`='$id'";
            // $query="DELETE FROM `ex_student` WHERE `exam_id`='$id'";
        }
    }

    public function choose() {
        $tmp = ChooseBaseModel::instance()->getById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->echoError('You have no privilege!');
        } else {
            ChooseBaseModel::instance()->delById($this->id);
            $sql = "DELETE FROM `exp_question` WHERE `question_id`=$this->id and `type`=1";
            M()->execute($sql);
            $sql = "DELETE FROM `ex_stuanswer` WHERE `question_id`=$this->id and `type`=1";
            M()->execute($sql);
            QuestionPointBaseModel::instance()->delByQuestion($this->id, 1);
            $this->success("选择题删除成功", U("Teacher/Index/choose", array('page' => $this->page)), 2);
        }
    }

    public function judge() {
        $tmp = JudgeBaseModel::instance()->getById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->echoError('You have no privilege!');
        } else {
            JudgeBaseModel::instance()->delById($this->id);
            $sql = "DELETE FROM `exp_question` WHERE `question_id`=$this->id and `type`=2";
            M()->execute($sql);
            $sql = "DELETE FROM `ex_stuanswer` WHERE `question_id`=$this->id and `type`=2";
            M()->execute($sql);
            QuestionPointBaseModel::instance()->delByQuestion($this->id, 2);
            $this->success("判断题删除成功", U("Teacher/Index/judge", array('page' => $this->page)), 2);
        }
    }

    public function fill() {
        $tmp = FillBaseModel::instance()->getById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->echoError('You have no privilege!');
        } else {
            FillBaseModel::instance()->delById($this->id);
            $sql = "DELETE FROM `fill_answer` WHERE `fill_id`=$this->id";
            M()->execute($sql);
            $sql = "DELETE FROM `exp_question` WHERE `question_id`=$this->id and `type`=3";
            M()->execute($sql);
            $sql = "DELETE FROM `ex_stuanswer` WHERE `question_id`=$this->id and `type`=3";
            M()->execute($sql);
            QuestionPointBaseModel::instance()->delByQuestion($this->id, 3);
            $this->success("填空题删除成功", U("Teacher/Index/fill", array('page' => $this->page)), 2);
        }
    }
}