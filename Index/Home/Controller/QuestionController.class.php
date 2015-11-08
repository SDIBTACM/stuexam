<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 20:15
 */

namespace Home\Controller;

use Home\Model\ExamadminModel;
use Teacher\Model\ExamBaseModel;

class QuestionController extends TemplateController
{

    public $examId = null;
    public $randnum = null;
    public $examBase = null;
    public $isRunning = null;
    private static $isOnExam = 0;

    public function _initialize() {
        parent::_initialize();
        if (!self::$isOnExam) {
            $this->preExamQuestion();
            $this->startExamQuestion();
        }
    }

    protected function preExamQuestion() {
        $eid = I('eid', 0, 'intval');
        if (empty($eid)) {
            $this->echoError('No Such Exam!');
        } else {
            $this->examId = $eid;
            $userId = $this->userInfo['user_id'];
            $this->examBase = ExamadminModel::instance()->chkexamprivilege($eid, $userId, true);

            if (is_array($this->examBase)) {
                $isruning = ExamadminModel::instance()->chkruning($this->examBase['start_time'], $this->examBase['end_time']);
                if ($isruning != ExamBaseModel::EXAM_RUNNING) {
                    $this->alertError('exam is not running!', U('Home/Index/index'));
                }
                $this->isRunning = $isruning;
                $lefttime = strtotime($this->examBase['end_time']) - time();
                $this->zadd('lefttime', $lefttime);
                $this->zadd('row', $this->examBase);

            } else {
                $row = $this->examBase;
                if ($row == 0) {
                    $this->echoError('You have no privilege!');
                } else if ($row == -1) {
                    $this->echoError('No Such Exam!');
                } else if ($row == -2) {
                    $this->echoError('Do not login in diff machine,Please Contact administrator');
                } else if ($row == -3) {
                    $this->echoError('You have taken part in it');
                }
            }
        }
    }

    protected function startExamQuestion() {
        $eid = $this->examId;
        $userId = $this->userInfo['user_id'];
        $randNum = M('ex_privilege')
            ->field('randnum')
            ->where("user_id='$userId' and rightstr='e$eid'")
            ->find();
        if ($this->isCreator()) {
            $num = 0;
        } else {
            $num = intval($randNum['randnum']);
        }
        $this->randnum = $num;
        $this->zadd('randnum', $num);
        self::$isOnExam = 1;
    }

    public function navigation() {
        $field = array('nick');
        $where = array(
            'user_id' => $this->userInfo['user_id']
        );
        $name = M('users')->field($field)->where($where)->find();
        $this->zadd('name', $name['nick']);
        $this->zadd('eid', $this->examId);
        $this->zadd('isruning', $this->isRunning);
        $this->auto_display('Index:about');
    }
}