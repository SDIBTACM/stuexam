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
use Teacher\Model\FillBaseModel;
use Teacher\Model\ProblemServiceModel;

// TODO 暂时未开放此类,主要为了将各题目模型分隔
class FillController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        $this->addExamBaseInfo();
    }

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $fillarr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], FillBaseModel::FILL_PROBLEM_TYPE);
        $fillans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, FillBaseModel::FILL_PROBLEM_TYPE);
        $fillsx = ExamadminModel::instance()->getproblemsx($this->examId, FillBaseModel::FILL_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('fillarr', $fillarr);
        $this->zadd('fillsx', $fillsx);
        $this->zadd('fillans', $fillans);

        $this->auto_display('Exam:fill', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, FillBaseModel::FILL_PROBLEM_TYPE);
    }

    public function submitPaper() {
        $fscore = AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, FillBaseModel::FILL_PROBLEM_TYPE, false);
        $inarr['fillsum'] = $fscore;
        // TODO update fill score
    }
}