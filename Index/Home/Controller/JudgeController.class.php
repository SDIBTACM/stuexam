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

use Teacher\Model\JudgeBaseModel;

use Teacher\Service\ExamService;
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

        $allBaseScore = ExamService::instance()->getBaseScoreByExamId($this->examId);
        $judgearr = ExamService::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeans = ProblemService::instance()->getProblemsAndAnswer4Exam($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgesx = ExamAdminModel::instance()->getProblemSequence($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allBaseScore);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('judgesx', $judgesx);
        $this->zadd('judgeans', $judgeans);
        $this->zadd('questionName', JudgeBaseModel::JUDGE_PROBLEM_NAME);
        $this->zadd('problemType', JudgeBaseModel::JUDGE_PROBLEM_TYPE);

        $this->auto_display('Exam:judge', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        echo $this->leftTime;
    }

    public function submitPaper() {
        $allscore = ExamService::instance()->getBaseScoreByExamId($this->examId);
        $jright = AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, false);
        $inarr['judgesum'] = $jright * $allscore['judgescore'];
        StudentService::instance()->submitExamPaper(
            $this->userInfo['user_id'], $this->examId, $inarr);
        $this->checkActionAfterSubmit();
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}