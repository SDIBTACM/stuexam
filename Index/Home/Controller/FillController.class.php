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
use Teacher\Model\AdminexamModel;
use Teacher\Model\AdminproblemModel;

class FillController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        $allscore = AdminexamModel::instance()->getallscore($this->examId);
        $fillarr = AdminexamModel::instance()->getuserans($this->examId, $this->userInfo['user_id'], 3);
        $fillans = AdminproblemModel::instance()->getproblemans($this->examId, 3);
        $fillsx = ExamadminModel::instance()->getproblemsx($this->examId, 3, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('fillarr', $fillarr);
        $this->zadd('fillsx', $fillsx);
        $this->zadd('fillans', $fillans);

        $this->auto_display('Exam:fill', false);
    }
}