<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 20:15
 */

namespace Home\Controller;

use Home\Model\ExamadminModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\ProblemServiceModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\StudentBaseModel;

class QuestionController extends TemplateController
{

    public $examId = null;
    public $examBase = null;
    public $isRunning = null;
    public $leftTime = 0;
    public $randnum = null;

    protected $navigationUrl;

    public function _initialize() {
        parent::_initialize();
        $this->preExamQuestion();
    }

    protected function preExamQuestion() {
        $eid = I('eid', 0, 'intval');
        if (empty($eid)) {
            $this->alertError('No Such Exam!', U('/Home'));
        } else {
            $this->examId = $eid;
            $userId = $this->userInfo['user_id'];
            $this->examBase = ExamadminModel::instance()->chkexamprivilege($eid, $userId, true);
            $this->navigationUrl = U('Home/Question/navigation', array('eid' => $this->examId));

            if (is_array($this->examBase)) {
                $isruning = ExamadminModel::instance()->chkruning($this->examBase['start_time'], $this->examBase['end_time']);
                if ($isruning != ExamBaseModel::EXAM_RUNNING) {
                    $this->alertError('exam is not running!', U('Home/Index/index'));
                }
                $this->isRunning = $isruning;
                $lefttime = strtotime($this->examBase['end_time']) - time();
                $this->leftTime = $lefttime;
            } else {
                $row = $this->examBase;
                if ($row == 0) {
                    $this->alertError('You have no privilege!', U('/Home/'));
                } else if ($row == -1) {
                    $this->alertError('No Such Exam!', U('/Home/'));
                } else if ($row == -2) {
                    $this->alertError('Do not login in diff machine,Please Contact administrator', U('/Home'));
                } else if ($row == -3) {
                    $this->alertError('You have taken part in it', U('/Home/'));
                }
            }
        }
    }

    protected function addExamBaseInfo() {
        $this->getStudentRandom();
        $widgets = array();
        $widgets['row'] = $this->examBase;
        $widgets['lefttime'] = $this->leftTime;
        $widgets['randnum'] = $this->randnum;
        $this->ZaddWidgets($widgets);
    }

    protected function getStudentRandom() {
        $eid = $this->examId;
        $userId = $this->userInfo['user_id'];
        $randNum = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($userId, $eid, array('randnum'));
        if ($this->isCreator()) {
            $num = 0;
        } else {
            $num = intval($randNum['randnum']);
        }
        $this->randnum = $num;
    }

    protected function checkHasScore($scoreName) {
        $scores = StudentBaseModel::instance()->getStudentScoreInfoByExamAndUserId($this->examId, $this->userInfo['user_id']);
        if (empty($scores)) {
            return false;
        }
        if ($scores[$scoreName] == -1) {
            return false;
        }
        return true;
    }

    public function navigation() {
        $field = array('nick');
        $where = array(
            'user_id' => $this->userInfo['user_id']
        );
        $name = M('users')->field($field)->where($where)->find();
        $allScore = StudentBaseModel::instance()->getStudentScoreInfoByExamAndUserId($this->examId, $this->userInfo['user_id']);
        if (empty($allScore)) {
            $allScore = array(
                'choosesum' => -1,
                'judgesum' => -1,
                'fillsum' => -1,
                'programsum' => -1
            );
        }

        $allProblemNum = array();
        $allProblemNum['choosenum'] = QuestionBaseModel::instance()->getQuestionCntByType($this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $allProblemNum['judgenum'] =  QuestionBaseModel::instance()->getQuestionCntByType($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $allProblemNum['fillnum'] = QuestionBaseModel::instance()->getQuestionCntByType($this->examId, FillBaseModel::FILL_PROBLEM_TYPE);
        $allProblemNum['programnum'] = QuestionBaseModel::instance()->getQuestionCntByType($this->examId, ProblemServiceModel::PROGRAM_PROBLEM_TYPE);
        $this->zadd('name', $name['nick']);
        $this->zadd('eid', $this->examId);
        $this->zadd('isruning', $this->isRunning);
        $this->zadd('row', $this->examBase);
        $this->zadd('userScore', $allScore);
        $this->zadd('allNum', $allProblemNum);
        if (!empty($this->userInfo) && $this->userInfo['user_id'] == 'jk11171228')
            $this->auto_display('Index:navigation');
        else
            $this->auto_display('Index:about');
    }

    public function getLeftTime() {
        return $this->leftTime;
    }
}
