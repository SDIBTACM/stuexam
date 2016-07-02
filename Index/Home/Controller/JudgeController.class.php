<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:42
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamadminModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\ProblemServiceModel;
use Teacher\Model\StudentBaseModel;

class JudgeController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        $this->addExamBaseInfo();
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

        $allBaseScore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $judgearr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgesx = ExamadminModel::instance()->getProblemSequence($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allBaseScore);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('judgesx', $judgesx);
        $this->zadd('judgeans', $judgeans);
        $this->zadd('questionName', '判断题');
        $this->zadd('problemType', JudgeBaseModel::JUDGE_PROBLEM_TYPE);

        $this->auto_display('Exam:judge', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        echo 'ok';
    }

    public function submitPaper() {
        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $jright = AnswerModel::instance()->saveProblemAnswer($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, false);
        $inarr['judgesum'] = $jright * $allscore['judgescore'];
        StudentBaseModel::instance()->submitExamPaper(
            $this->userInfo['user_id'], $this->examId, $inarr);
        $this->checkActionAfterSubmit();
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}