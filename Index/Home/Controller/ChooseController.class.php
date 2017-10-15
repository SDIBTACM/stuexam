<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:40
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamAdminModel;

use Teacher\Model\ChooseBaseModel;

use Teacher\Service\ExamService;
use Teacher\Service\ProblemService;
use Teacher\Service\StudentService;

class ChooseController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
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

        $allBaseScore = ExamService::instance()->getBaseScoreByExamId($this->examId);
        $chooseArr = ExamService::instance()->getUserAnswer(
            $this->examId, $this->userInfo['user_id'], ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseAns = ProblemService::instance()->getProblemsAndAnswer4Exam(
            $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseSeq = ExamAdminModel::instance()->getProblemSequence(
            $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allBaseScore);
        $this->zadd('choosearr', $chooseArr);
        $this->zadd('choosesx', $chooseSeq);
        $this->zadd('chooseans', $chooseAns);
        $this->zadd('questionName', ChooseBaseModel::CHOOSE_PROBLEM_NAME);
        $this->zadd('problemType', ChooseBaseModel::CHOOSE_PROBLEM_TYPE);

        $this->auto_display('Exam:choose', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->saveProblemAnswer(
            $this->userInfo['user_id'], $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        echo $this->leftTime;
    }

    public function submitPaper() {
        $allScore = ExamService::instance()->getBaseScoreByExamId($this->examId);
        $cRight = AnswerModel::instance()->saveProblemAnswer(
            $this->userInfo['user_id'], $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, false);
        $inArr['choosesum'] = $cRight * $allScore['choosescore'];
        StudentService::instance()->submitExamPaper(
            $this->userInfo['user_id'], $this->examId, $inArr);
        $this->checkActionAfterSubmit();
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}