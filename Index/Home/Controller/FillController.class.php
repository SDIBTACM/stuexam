<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:42
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamAdminModel;

use Teacher\Model\FillBaseModel;

use Teacher\Service\ExamService;
use Teacher\Service\ProblemService;
use Teacher\Service\StudentService;

class FillController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        if ($this->fillSumScore != -1) {
            $this->success('该题型你已经交卷,不能再查看了哦', $this->navigationUrl, 1);
            exit;
        }
        if ($this->fillCount == 0) {
            redirect($this->navigationUrl);
        }
    }

    public function index() {

        $this->start2Exam();

        $allBaseScore = ExamService::instance()->getBaseScoreByExamId($this->examId);
        $fillarr = ExamService::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], FillBaseModel::FILL_PROBLEM_TYPE);
        $fillans = ProblemService::instance()->getProblemsAndAnswer4Exam($this->examId, FillBaseModel::FILL_PROBLEM_TYPE);
        $fillsx = ExamAdminModel::instance()->getProblemSequence($this->examId, FillBaseModel::FILL_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allBaseScore);
        $this->zadd('fillarr', $fillarr);
        $this->zadd('fillsx', $fillsx);
        $this->zadd('fillans', $fillans);
        $this->zadd('questionName', FillBaseModel::FILL_PROBLEM_NAME);
        $this->zadd('problemType', FillBaseModel::FILL_PROBLEM_TYPE);

        $this->auto_display('Exam:fill', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, FillBaseModel::FILL_PROBLEM_TYPE);
        echo $this->leftTime;
    }

    public function submitPaper() {
        $fscore = AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, FillBaseModel::FILL_PROBLEM_TYPE, false);
        $inarr['fillsum'] = $fscore;
        StudentService::instance()->submitExamPaper(
            $this->userInfo['user_id'], $this->examId, $inarr);
        $this->checkActionAfterSubmit();
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}