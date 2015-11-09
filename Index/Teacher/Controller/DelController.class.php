<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Think\Controller;

class DelController extends TemplateController
{

    private $id = null;
    private $page = null;

    public function _initialize() {
        parent::_initialize();
        if (!check_get_key() || I('get.id') == '') {
            $this->error('发生错误');
        }
        $this->id = I('get.id', 0, 'intval');
        $this->page = I('get.page', 1, 'intval');
    }

    public function exam() {
        if (!$this->isOwner4ExamByExamId($this->id)) {
            $this->error('You have no privilege!');
        } else {
            $data = array('visible' => 'N');
            ExamBaseModel::instance()->updateExamInfoById($this->id, $data);
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
        $tmp = ChooseBaseModel::instance()->getChooseById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->error('You have no privilege!');
        } else {
            ChooseBaseModel::instance()->delChooseById($this->id);
            $sql = "DELETE FROM `exp_question` WHERE `question_id`=$this->id and `type`=1";
            M()->execute($sql);
            $sql = "DELETE FROM `ex_stuanswer` WHERE `question_id`=$this->id and `type`=1";
            M()->execute($sql);
            $this->success("选择题删除成功", U("Teacher/Index/choose", array('page' => $this->page)), 2);
        }
    }

    public function judge() {
        $tmp = JudgeBaseModel::instance()->getJudgeById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->error('You have no privilege!');
        } else {
            JudgeBaseModel::instance()->delJudgeById($this->id);
            $sql = "DELETE FROM `exp_question` WHERE `question_id`=$this->id and `type`=2";
            M()->execute($sql);
            $sql = "DELETE FROM `ex_stuanswer` WHERE `question_id`=$this->id and `type`=2";
            M()->execute($sql);
            $this->success("判断题删除成功", U("Teacher/Index/judge", array('page' => $this->page)), 2);
        }
    }

    public function fill() {
        $tmp = FillBaseModel::instance()->getFillById($this->id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            $this->error('You have no privilege!');
        } else {
            FillBaseModel::instance()->delFillById($this->id);
            $sql = "DELETE FROM `fill_answer` WHERE `fill_id`=$this->id";
            M()->execute($sql);
            $sql = "DELETE FROM `exp_question` WHERE `question_id`=$this->id and `type`=3";
            M()->execute($sql);
            $sql = "DELETE FROM `ex_stuanswer` WHERE `question_id`=$this->id and `type`=3";
            M()->execute($sql);
            $this->success("填空题删除成功", U("Teacher/Index/fill", array('page' => $this->page)), 2);
        }
    }
}