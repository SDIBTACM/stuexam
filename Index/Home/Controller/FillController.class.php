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
use Teacher\Model\ProblemServiceModel;

class FillController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $fillarr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], 3);
        $fillans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, 3);
        $fillsx = ExamadminModel::instance()->getproblemsx($this->examId, 3, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('fillarr', $fillarr);
        $this->zadd('fillsx', $fillsx);
        $this->zadd('fillans', $fillans);

        $this->auto_display('Exam:fill', false);
    }
}