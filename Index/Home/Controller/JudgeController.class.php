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

// TODO 暂时未开放此类,主要为了将各题目模型分隔
class JudgeController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        $this->addExamBaseInfo();
    }

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $judgearr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgesx = ExamadminModel::instance()->getproblemsx($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('judgesx', $judgesx);
        $this->zadd('judgeans', $judgeans);

        $this->auto_display('Exam:judge', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
    }

    public function submitPaper() {
        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $jright = AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $inarr['judgesum'] = $jright * $allscore['judgescore'];
        // TODO update judge score
    }
}