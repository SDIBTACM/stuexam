<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 20:15
 */

namespace Home\Controller;

use Constant\ReqResult\Result;
use Home\Model\ExamAdminModel;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\StudentBaseModel;

use Teacher\Service\ProblemService;
use Teacher\Service\StudentService;

use Basic\Log;

class QuestionController extends TemplateController
{

    public $examId = null;
    public $examBase = null;
    public $isRunning = null;
    public $leftTime = 0;
    public $randnum = null;

    public $chooseCount;
    public $judgeCount;
    public $fillCount;
    public $programCount;

    public $chooseSumScore;
    public $judgeSumScore;
    public $fillSumScore;
    public $programSumScore;

    protected $navigationUrl;

    public function _initialize() {
        parent::_initialize();
        $this->preExamQuestion();
        $this->initExamQuestionCount();
        $this->initExamUserScore();
        $this->addExamBaseInfo();
    }

    protected function preExamQuestion() {
        $eid = I('eid', 0, 'intval');
        if (empty($eid)) {
            $this->alertError('No Such Exam!', U('/Home'));
        } else {
            $this->examId = $eid;
            $userId = $this->userInfo['user_id'];
            $this->navigationUrl = U('Home/Question/navigation', array('eid' => $this->examId));

            $reqResult = ExamAdminModel::instance()->checkExamPrivilege($eid, $userId, true);
            if (!$reqResult instanceof Result) {
                $this->alertError("获取考试信息失败, 请刷新页面重试", U('/Home/'));
            }
            if (!$reqResult->getStatus()) {
                $this->alertError($reqResult->getMessage(), U('/Home/'));
                Log::info("user id: {} , exam id : {} check failed, message:{}", $userId, $eid, $reqResult->getMessage());
            } else {
                $this->examBase = $reqResult->getData();
                $isRunning = ExamAdminModel::instance()->getExamRunningStatus(
                    $this->examBase['start_time'], $this->examBase['end_time']);
                if ($isRunning != ExamBaseModel::EXAM_RUNNING) {
                    $this->alertError('exam is not running!', U('Home/Index/index'));
                }
                $this->isRunning = $isRunning;
                $leftTime = strtotime($this->examBase['end_time']) - time();
                $this->leftTime = $leftTime;
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

    protected function initExamQuestionCount() {
        $this->chooseCount = QuestionBaseModel::instance()->getQuestionCntByType(
            $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $this->judgeCount = QuestionBaseModel::instance()->getQuestionCntByType(
            $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $this->fillCount = QuestionBaseModel::instance()->getQuestionCntByType(
            $this->examId, FillBaseModel::FILL_PROBLEM_TYPE);
        $this->programCount = QuestionBaseModel::instance()->getQuestionCntByType(
            $this->examId, ProblemService::PROGRAM_PROBLEM_TYPE);
    }

    protected function initExamUserScore() {
        $allScore = StudentBaseModel::instance()->getStudentScoreInfoByExamAndUserId(
            $this->examId, $this->userInfo['user_id']);
        if (empty($allScore)) {
            $this->chooseSumScore = -1;
            $this->judgeSumScore = -1;
            $this->fillSumScore = -1;
            $this->programSumScore = -1;
        } else {
            $this->chooseSumScore = $allScore['choosesum'];
            $this->judgeSumScore = $allScore['judgesum'];
            $this->fillSumScore = $allScore['fillsum'];
            $this->programSumScore = $allScore['programsum'];
        }
    }

    protected function checkActionAfterSubmit() {
        $chooseOk = !($this->chooseCount > 0 && $this->chooseSumScore == -1);
        $judgeOk = !($this->judgeCount > 0 && $this->judgeSumScore == -1);
        $fillOk = !($this->fillCount > 0 && $this->fillSumScore == -1);
        $programOk = !($this->programCount > 0 && $this->programSumScore == -1);

        $needFix = ($chooseOk && $judgeOk && $fillOk && $programOk);
        if ($needFix) {
            $inarr['choosesum'] = ($this->chooseSumScore == -1 ? 0 : $this->chooseSumScore);
            $inarr['judgesum'] = ($this->judgeSumScore == -1 ? 0 : $this->judgeSumScore);
            $inarr['fillsum'] = ($this->fillSumScore == -1 ? 0 : $this->fillSumScore);
            $inarr['programsum'] = ($this->programSumScore == -1 ? 0 : $this->programSumScore);
            $inarr['score'] = $inarr['choosesum'] + $inarr['judgesum'] + $inarr['fillsum'] + $inarr['programsum'];
            StudentService::instance()->submitExamPaper(
                $this->userInfo['user_id'], $this->examId, $inarr);
            $this->success('恭喜你所有题型已提交完成~', U('Home/Index/score'), 2);
            exit(0);
        }
    }

    protected function start2Exam() {
        $data = array(
            'extrainfo' => $this->leftTime + 1
        );
        PrivilegeBaseModel::instance()->updatePrivilegeByUserIdAndExamId(
            $this->userInfo['user_id'], $this->examId, $data);
    }

    public function navigation() {
        $field = array('nick');
        $where = array(
            'user_id' => $this->userInfo['user_id']
        );
        $name = M('users')->field($field)->where($where)->find();

        $allScore = array(
            'choosesum' => $this->chooseSumScore,
            'judgesum' => $this->judgeSumScore,
            'fillsum' => $this->fillSumScore,
            'programsum' => $this->programSumScore
        );

        $allProblemNum = array(
            'choosenum' => $this->chooseCount,
            'judgenum' => $this->judgeCount,
            'fillnum' => $this->fillCount,
            'programnum' => $this->programCount
        );

        $this->zadd('name', $name['nick']);
        $this->zadd('eid', $this->examId);
        $this->zadd('isruning', $this->isRunning);
        $this->zadd('row', $this->examBase);
        $this->zadd('userScore', $allScore);
        $this->zadd('allNum', $allProblemNum);

        $this->auto_display('Index:navigation');
    }

    public function getLeftTime() {
        return $this->leftTime;
    }
}
