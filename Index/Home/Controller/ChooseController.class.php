<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:40
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Service\ChooseService;
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

        $chooseArr = ExamService::instance()->getUserAnswer(
            $this->examId, $this->userInfo['user_id'], ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseAns = ProblemService::instance()->getProblemsAndAnswer4Exam(
            $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseSeq = getProblemSequence(count($chooseAns), $this->randnum);

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
        $userId = $this->userInfo['user_id'];
        AnswerModel::instance()->saveProblemAnswer($userId, $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $chooseSum = ChooseService::instance()->doRejudgeChooseByExamIdAndUserId(
            $this->examId, $userId, $this->examBase['choosescore']
        );
        $inArr['choosesum'] = $chooseSum;
        StudentService::instance()->submitExamPaper($userId, $this->examId, $inArr);

        $this->chooseSumScore = $chooseSum;
        $this->checkActionAfterSubmit();
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}
