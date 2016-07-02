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
use Teacher\Model\StudentBaseModel;

class ChooseController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        $this->addExamBaseInfo();
        if ($this->chooseSumScore != -1) {
            $this->success('该题型你已经交卷,不能再查看了哦', $this->navigationUrl, 1);
            exit;
        }
        if ($this->chooseCount == 0) {
            redirect($this->navigationUrl);
        }
    }

    public function index() {

        $this->start2Exam();

        $allBaseScore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $choosearr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $choosesx = ExamadminModel::instance()->getProblemSequence($this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allBaseScore);
        $this->zadd('choosearr', $choosearr);
        $this->zadd('choosesx', $choosesx);
        $this->zadd('chooseans', $chooseans);
        $this->zadd('questionName', '选择题');
        $this->zadd('problemType', ChooseBaseModel::CHOOSE_PROBLEM_TYPE);

        $this->auto_display('Exam:choose', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        echo "ok";
    }

    public function submitPaper() {
        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $cright = AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, false);
        $inarr['choosesum'] = $cright * $allscore['choosescore'];
        StudentBaseModel::instance()->submitExamPaper(
            $this->userInfo['user_id'], $this->examId, $inarr);
        $this->checkActionAfterSubmit();
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}