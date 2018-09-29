<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:42
 */

namespace Home\Controller;

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

        $judgeArr = ExamService::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeAns = ProblemService::instance()->getProblemsAndAnswer4Exam($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeSeq = getProblemSequence(count($judgeAns), $this->randnum);

        $this->zadd('judgearr', $judgeArr);
        $this->zadd('judgesx', $judgeSeq);
        $this->zadd('judgeans', $judgeAns);
        $this->zadd('questionName', JudgeBaseModel::JUDGE_PROBLEM_NAME);
        $this->zadd('problemType', JudgeBaseModel::JUDGE_PROBLEM_TYPE);

        $this->auto_display('Exam:judge', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        echo $this->leftTime;
    }

    public function submitPaper() {
        $userId = $this->userInfo['user_id'];
        AnswerModel::instance()->saveProblemAnswer($userId, $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeSum = JudgeService::instance()->doRejudgeJudgeByExamIdAndUserId(
            $this->examId, $userId, $this->examBase['judgescore']
        );
        $inArr['judgesum'] = $judgeSum;
        StudentService::instance()->submitExamPaper($userId, $this->examId, $inArr);

        $this->judgeSumScore = $judgeSum;
        $this->checkActionAfterSubmit();
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}
