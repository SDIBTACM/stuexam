<?php
namespace Home\Controller;

use Home\Helper\SqlExecuteHelper;
use Home\Model\ExamAdminModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Service\ExamService;
use Teacher\Service\ProblemService;

class IndexController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        if (!$this->isSuperAdmin()) {
            if ($this->isCreator()) {
                $userId = $this->userInfo['user_id'];
                $_where = array(
                    'isprivate' => 0,
                    'creator' => array('eq', $userId),
                    '_logic' => 'or'
                );
                $where = array(
                    'visible' => 'Y',
                    '_complex' => $_where,
                    '_logic' => 'and'
                );
            } else {
                $where = array(
                    'user_id' => $this->userInfo['user_id'],
                    'rightstr' => array("like", "e%")
                );
                $fields = array('rightstr');
                $privileges = PrivilegeBaseModel::instance()->queryAll($where, $fields);
                $examIds = array(0);
                foreach ($privileges as $privilege) {
                    $rightstr = $privilege['rightstr'];
                    $examIds[] = intval(substr($rightstr, 1));
                }
                $where = array(
                    'visible' => 'Y',
                    'exam_id' => array('in', $examIds),
                );
            }
        } else {
            $where = array('visible' => 'Y');
        }

        $mypage = splitpage('exam', $where);

        $where['order'] = array('end_time desc');
        $where['limit'] = $mypage['sqladd'];
        $field = array('exam_id', 'title', 'start_time', 'end_time');
        $row = ExamBaseModel::instance()->queryData($where, $field);

        $this->zadd('row', $row);
        $this->zadd('mypage', $mypage);
        $this->auto_display();
    }

    public function score() {
        $user_id = $this->userInfo['user_id'];
        $row = M('users')->field('nick,email,reg_time')
            ->where("user_id='%s'", $user_id)->find();
        $score = SqlExecuteHelper::Home_GetUserScore($user_id);

        $where = array(
            'user_id' => $user_id,
            'rightstr' => array('like', 'wa%')
        );
        $field = array('rightstr');
        $allUserCanSeeWA = PrivilegeBaseModel::instance()->queryAll($where, $field);
        $canSeeWaExamMap = array();

        foreach ($allUserCanSeeWA as $rights) {
            $_examId = intval(substr($rights['rightstr'], 2));
            $canSeeWaExamMap[$_examId] = 1;
        }

        foreach ($score as &$eachScore) {
            $eachScore['canSeeWA'] = 0;
            if ($this->isTeacher() || isset($canSeeWaExamMap[$eachScore['exam_id']])) {
                $eachScore['canSeeWA'] = 1;
            }
        }

        $this->zadd('score', $score);
        $this->zadd('row', $row);
        $this->auto_display();
    }

    public function showPaper() {
        // 验证是否能查看这张试卷的错题
        $examId = I('get.eid', 0, 'intval');
        $userId = $this->userInfo['user_id'];
        $field = array('title', 'start_time', 'end_time');
        $row = ExamBaseModel::instance()->getById($examId, $field);
        if (empty($row)) {
            $this->alertError("考试不存在", U('/Home/Index/score'));
        }

        $isRunning = ExamAdminModel::instance()->getExamRunningStatus(
            $row['start_time'], $row['end_time']
        );
        if ($isRunning != ExamBaseModel::EXAM_END) {
            $this->alertError("只有结束后的考试才能查看错题", U('/Home/Index/score'));
        }

        $where = array(
            'user_id' => $userId,
            'rightstr' => "wa$examId"
        );
        $hasPrivilege = PrivilegeBaseModel::instance()->countNumber($where);
        if ($hasPrivilege > 0 || $this->isTeacher()) {
            // 获取题目信息
            $row = ExamBaseModel::instance()->getById($examId);

            $chooseUserAnswer = ExamService::instance()->getUserAnswer($examId, $userId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $judgeUserAnswer = ExamService::instance()->getUserAnswer($examId, $userId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $fillUserAnswer = ExamService::instance()->getUserAnswer($examId, $userId, FillBaseModel::FILL_PROBLEM_TYPE);

            $chooseRightAnswer = ProblemService::instance()->getProblemsAndAnswer4Exam($examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $judgeRightAnswer = ProblemService::instance()->getProblemsAndAnswer4Exam($examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $fillRightAnswer = ProblemService::instance()->getProblemsAndAnswer4Exam($examId, FillBaseModel::FILL_PROBLEM_TYPE);
            $fillRightAnswer2 = array();

            if ($fillRightAnswer) {
                foreach ($fillRightAnswer as $key => $value) {
                    $fillRightAnswer2[$value['fill_id']] = ProblemService::instance()
                        ->getProblemsAndAnswer4Exam($value['fill_id'], ProblemService::PROBLEMANS_TYPE_FILL);
                }
            }

            $this->removeAllChooseUserRightAnswer($chooseUserAnswer, $chooseRightAnswer);
            $this->removeAllJudgeUserRightAnswer($judgeUserAnswer, $judgeRightAnswer);
            $this->removeAllFillUserRightAnswer($fillUserAnswer, $fillRightAnswer, $fillRightAnswer2);

            $this->zadd('title', $row['title']);
            $this->zadd('allscore', $row);
            $this->zadd('choosearr', $chooseUserAnswer);
            $this->zadd('judgearr', $judgeUserAnswer);
            $this->zadd('fillarr', $fillUserAnswer);
            $this->zadd('chooseans', $chooseRightAnswer);
            $this->zadd('judgeans', $judgeRightAnswer);
            $this->zadd('fillans', $fillRightAnswer);
            $this->zadd('fillans2', $fillRightAnswer2);

            $this->auto_display('paper');
        } else {
            $this->alertError("没有权限查看该考试的试卷", U('/Home/Index/score'));
        }
    }

    private function removeAllChooseUserRightAnswer($chooseUserAnswer, &$chooseRightAnswer) {
        foreach ($chooseRightAnswer as $key => $rightAnswer) {
            $userAnswer = isset($chooseUserAnswer[ $rightAnswer['choose_id'] ]) ?
                $chooseUserAnswer[ $rightAnswer['choose_id'] ] : "未选";
            if ($userAnswer == $rightAnswer['answer']) {
                unset($chooseRightAnswer[$key]);
            }
        }
    }

    private function removeAllJudgeUserRightAnswer($judgeUserAnswer, &$judgeRightAnswer) {
        foreach ($judgeRightAnswer as $key => $rightAnswer) {
            $userAnswer = isset($judgeUserAnswer[$rightAnswer['judge_id'] ]) ? $judgeUserAnswer[ $rightAnswer['judge_id'] ] : "未选";
            if ($userAnswer == $rightAnswer['answer']) {
                unset($judgeRightAnswer[$key]);
            }
        }
    }

    private function removeAllFillUserRightAnswer($fillUserAnswer, &$fillRightAnswer, $fillRightAnswer2) {
        foreach ($fillRightAnswer as $key => $rightAnswer) {
            $flag = true;
            for ($i = 1; $i < $rightAnswer['answernum'] + 1; $i++) {
                $answer = $fillRightAnswer2[$rightAnswer['fill_id']][$i - 1]['answer'];
                $userAnswer = isset($fillUserAnswer[$rightAnswer['fill_id']][$i]) ? $fillUserAnswer[$rightAnswer['fill_id']][$i] : "未选";
                if ($userAnswer == $answer && strlen($answer) == strlen($userAnswer)) {
                } else {
                    $flag = false;
                    break;
                }
            }

            if ($flag) {
                unset($fillRightAnswer[$key]);
            }
        }
    }
}
