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

class JudgeController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        $allscore = AdminexamModel::instance()->getallscore($this->examId);
        $judgearr = AdminexamModel::instance()->getuserans($this->examId, $this->userInfo['user_id'], 2);
        $judgeans = AdminproblemModel::instance()->getproblemans($this->examId, 2);
        $judgesx = ExamadminModel::instance()->getproblemsx($this->examId, 2, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('judgesx', $judgesx);
        $this->zadd('judgeans', $judgeans);

        $this->auto_display('Exam:judge', false);
    }
}