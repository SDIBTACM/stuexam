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


class ChooseController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $choosearr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, 1);
        $choosesx = ExamadminModel::instance()->getproblemsx($this->examId, 1, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('choosearr', $choosearr);
        $this->zadd('choosesx', $choosesx);
        $this->zadd('chooseans', $chooseans);

        $this->auto_display('Exam:choose', false);
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