<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:42
 */

namespace Home\Controller;

use Basic\Log;
use Home\Model\AnswerModel;
use Teacher\Model\FillBaseModel;
use Teacher\Service\ExamService;
use Teacher\Service\FillService;
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

        $fillArr = ExamService::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], $this->getProblemType());
        $fillAns = ProblemService::instance()->getProblemsAndAnswer4Exam($this->examId, $this->getProblemType());
        $fillSeq = getProblemSequence(count($fillAns), $this->randnum);

        $this->zadd('fillarr', $fillArr);
        $this->zadd('fillsx', $fillSeq);
        $this->zadd('fillans', $fillAns);
        $this->zadd('questionName', FillBaseModel::FILL_PROBLEM_NAME);
        $this->zadd('problemType', $this->getProblemType());

        $this->auto_display('Exam:fill', 'exlayout');
    }

    public function saveAnswer() {
        parent::saveAnswer();
    }

    protected function getProblemType() {
        return FillBaseModel::FILL_PROBLEM_TYPE;
    }

    public function submitPaper() {
        $userId = $this->userInfo['user_id'];
        Log::info("userId:{} submit type={} paper start", $userId, $this->getProblemType());
        AnswerModel::instance()->saveProblemAnswer($userId, $this->examId, $this->getProblemType());
        $fillSum = FillService::instance()->doRejudgeFillByExamIdAndUserId($this->examId, $userId, $this->examBase);
        $inArr['fillsum'] = $fillSum;
        StudentService::instance()->submitExamPaper($userId, $this->examId, $inArr);

        $this->fillSumScore = $fillSum;
        $this->checkActionAfterSubmit();
        Log::info("userId:{} submit type={} paper end", $userId, $this->getProblemType());
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}
