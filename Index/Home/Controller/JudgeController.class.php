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
use Teacher\Model\JudgeBaseModel;
use Teacher\Service\ExamService;
use Teacher\Service\JudgeService;
use Teacher\Service\ProblemService;
use Teacher\Service\StudentService;

class JudgeController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        if ($this->judgeSumScore != -1) {
            $this->success('该题型你已经交卷,不能再查看了哦', $this->navigationUrl, 1);
            exit;
        }
        if ($this->judgeCount == 0) {
            redirect($this->navigationUrl);
        }
    }

    public function index() {

        $this->start2Exam();

        $judgeArr = ExamService::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], $this->getProblemType());
        $judgeAns = ProblemService::instance()->getProblemsAndAnswer4Exam($this->examId, $this->getProblemType());
        $judgeSeq = getProblemSequence(count($judgeAns), $this->randnum);

        $this->zadd('judgearr', $judgeArr);
        $this->zadd('judgesx', $judgeSeq);
        $this->zadd('judgeans', $judgeAns);
        $this->zadd('questionName', JudgeBaseModel::JUDGE_PROBLEM_NAME);
        $this->zadd('problemType', $this->getProblemType());

        $this->auto_display('Exam:judge', 'exlayout');
    }

    public function saveAnswer() {
        parent::saveAnswer();
    }

    protected function getProblemType() {
        return JudgeBaseModel::JUDGE_PROBLEM_TYPE;
    }

    public function submitPaper() {
        $userId = $this->userInfo['user_id'];
        Log::info("userId:{} examId:{} submit type={} paper start userUA:{}",
            $userId, $this->examId, $this->getProblemType(), $this->getUserAgent());
        AnswerModel::instance()->saveProblemAnswer($userId, $this->examId, $this->getProblemType());
        $judgeSum = JudgeService::instance()->doRejudgeJudgeByExamIdAndUserId(
            $this->examId, $userId, $this->examBase['judgescore']
        );
        $inArr['judgesum'] = $judgeSum;
        StudentService::instance()->submitExamPaper($userId, $this->examId, $inArr);

        $this->judgeSumScore = $judgeSum;
        $this->checkActionAfterSubmit();
        Log::info("userId:{} submit type={} paper end", $userId, $this->getProblemType());
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}
