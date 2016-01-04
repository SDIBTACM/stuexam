<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:40
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamadminModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\ProblemServiceModel;

// TODO 暂时未开放此类,主要为了将各题目模型分隔
class ChooseController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        $this->addExamBaseInfo();
    }

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $choosearr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $choosesx = ExamadminModel::instance()->getproblemsx($this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('choosearr', $choosearr);
        $this->zadd('choosesx', $choosesx);
        $this->zadd('chooseans', $chooseans);

        $this->auto_display('Exam:choose', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
    }

    public function submitPaper() {
        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $cright = AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, false);
        $inarr['user_id'] = $this->userInfo['user_id'];
        $inarr['exam_id'] = $this->examId;
        $inarr['choosesum'] = $cright * $allscore['choosescore'];
        M('ex_student')->add($inarr);
    }
}