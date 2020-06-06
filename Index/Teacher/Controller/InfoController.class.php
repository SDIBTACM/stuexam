<?php

namespace Teacher\Controller;

use Basic\Log;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\StudentBaseModel;
use Teacher\Service\ChooseService;
use Teacher\Service\FillService;
use Teacher\Service\JudgeService;
use Teacher\Service\ProblemService;

class InfoController extends TemplateController {

    public function _initialize() {
        parent::_initialize();
    }

    public function submitpaper() {
        if (!(isset($_GET['eid']) && isset($_GET['users']))) {
            Log::info("user id: {} url error, url data: {}, ", $this->userInfo['user_id'], $_SERVER['REQUEST_URI']);
            $this->echoError('Wrong Path');
            return;
        }
        $eid = intval(trim($_GET['eid']));
        $userId = trim($_GET['users']);
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        if (!$this->isOwner4ExamByExamId($eid)) {
            Log::info("user id: {} exam id: {} stuid: {}, require: submit paper, result: FAIL, reason: privilege", $this->userInfo['user_id'], $eid, $userId);
            $this->echoError('You have no privilege to do it!');
        }
        $flag = $this->doJudgeOne($eid, $userId);
        if ($flag) {
            Log::info("user id: {} exam id: {} stuid: {}, require: submit paper, result: success", $this->userInfo['user_id'], $eid, $userId);
            $this->redirect("Exam/userscore", array(
                'eid' => $eid,
                'sortdnum' => $sortdnum,
                'sortanum' => $sortanum
            ));
        }
        Log::warn("user id: {} exam id: {} stuid: {}, require: submit paper, result: FAIL, reason: unknow", $this->userInfo['user_id'], $eid, $userId);
    }

    public function hardSubmit() {
        $eid = I('get.eid', 0, 'intval');
        $userId = I('get.userId', '');
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        if (!$this->isOwner4ExamByExamId($eid)) {
            Log::info("user id: {} exam id: {} stuid: {}, require: hard submit paper, result: FAIL, reason: no privilege",
                $this->userInfo['user_id'], $eid, $userId);
            $this->echoError('You have no privilege to do it!');
        }
        if (empty($eid) || empty($userId)) {
        } else {
            $this->doJudgeOne($eid, $userId);
            Log::info("user id: {} exam id: {} stuid: {}, require: hard submit paper, result: success",
                $this->userInfo['user_id'], $eid, $userId);
        }
        $this->redirect("Exam/userscore", array(
            'eid' => $eid,
            'sortdnum' => $sortdnum,
            'sortanum' => $sortanum
        ));
    }

    public function dorejudge() {
        if (!(IS_POST && I('post.eid'))) {
            Log::info("user id: {} url error, url data: {}, ", $this->userInfo['user_id'], $_SERVER['REQUEST_URI']);
            $this->echoError('Wrong Method');
            return;
        }
        if (!$this->isSuperAdmin()) {
            $this->echoError('发生错误！');
            Log::info("user id: {}, require: rejudge, result: FAIL, reason: no privilege",
                $this->userInfo['user_id']);
        }
        if (!check_post_key()) {
            Log::error("user id: {} post key error", $this->userInfo['user_id']);
            $this->echoError('发生错误！');
        }
        $eid = intval($_POST['eid']);

        if (I('post.rjall')) {
            $examInfo = ExamBaseModel::instance()->getById($eid);
            $userlist = M('ex_student')->field('user_id')->where('exam_id=%d', $eid)->select();
            if ($userlist) {
                foreach ($userlist as $value) {
                    $this->rejudgePaper($value['user_id'], $examInfo, 1);
                }
                unset($userlist);
            }
            $this->success('全部重判成功！', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
        } else if (I('post.rjone')) {
            $rjuserid = test_input($_POST['rjuserid']);
            $flag = $this->doJudgeOne($eid, $rjuserid);
            if ($flag) {
                Log::info("user id: {} exam id: {}, require: rejudge exam, result: success", $this->userInfo['user_id'], $eid);
                $this->success('重判成功！', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
            }
        } else {
            Log::info("user id: {} exam id: {}, post data: {} require: rejudge exam, result: FAIL, reason: post error ",
                $this->userInfo['user_id'], $eid, I('post.'));
            $this->echoError('Invaild Path');
        }
    }

    private function doJudgeOne($eid, $userId) {
        $examInfo = ExamBaseModel::instance()->getById($eid);
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($examInfo['start_time']));
        $where = array('user_id' => $userId, 'rightstr' => "e$eid");
        $cnt1 = PrivilegeBaseModel::instance()->countNumber($where);
        if ($cnt1 == 0) {
            $this->echoError('Student ID is Wrong!');
            return false;
        }
        if (time() < $start_timeC) {
            $this->echoError('Exam Not Start');
        }
        $where = array("exam_id" => $eid, "user_id" => $userId);
        $mark = StudentBaseModel::instance()->countNumber($where);
        $this->rejudgePaper($userId, $examInfo, $mark);
        return true;
    }

    private function rejudgePaper($userId, $examInfo, $mark) {
        $eid = $examInfo['exam_id'];
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($examInfo['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($examInfo['end_time']));

        $choosesum = ChooseService::instance()->doRejudgeChooseByExamIdAndUserId($eid, $userId, $examInfo['choosescore']);
        $judgesum = JudgeService::instance()->doRejudgeJudgeByExamIdAndUserId($eid, $userId, $examInfo['judgescore']);
        $fillsum = FillService::instance()->doRejudgeFillByExamIdAndUserId($eid, $userId, $examInfo);
        $programsum = ProblemService::instance()->doRejudgeProgramByExamIdAndUserId($eid, $userId, $examInfo['programscore'], $start_timeC, $end_timeC);

        $sum = $choosesum + $judgesum + $fillsum + $programsum;

        $data = array(
            'score' => $sum,
            'choosesum' => $choosesum,
            'judgesum' => $judgesum,
            'fillsum' => $fillsum,
            'programsum' => $programsum
        );

        if ($mark == 0) {
            // if the student has not submitted the paper
            $data['user_id'] = $userId;
            $data['exam_id'] = $eid;
            StudentBaseModel::instance()->insertData($data);
        } else {
            StudentBaseModel::instance()->updateStudentScore($eid, $userId, $data);
        }
        Log::info("user id: {} exam id: {} stuid:{}, require: rejudge one paper, result: success",
            $this->userInfo['user_id'], $eid, $userId);
        ProblemService::instance()->doFixStuAnswerProgramRank($eid, $userId, $start_timeC, $end_timeC);
    }
}

