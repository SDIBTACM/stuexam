<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:40
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamadminModel;
use Teacher\Model\AdminexamModel;
use Teacher\Model\AdminproblemModel;


class ChooseController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        $allscore = AdminexamModel::instance()->getallscore($this->examId);
        $choosearr = AdminexamModel::instance()->getuserans($this->examId, $this->userInfo['user_id'], 1);
        $chooseans = AdminproblemModel::instance()->getproblemans($this->examId, 1);
        $choosesx = ExamadminModel::instance()->getproblemsx($this->examId, 1, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('choosearr', $choosearr);
        $this->zadd('choosesx', $choosesx);
        $this->zadd('chooseans', $chooseans);

        $this->auto_display('Exam:choose', false);
    }
}