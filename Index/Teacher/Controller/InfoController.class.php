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

class InfoController extends TemplateController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function delscore() {
        if (!(isset($_GET['eid']) && isset($_GET['users']))) {
            Log::info("user id: {} url error, url data: {}, ", $this->userInfo['user_id'], $_SERVER['REQUEST_URI']);
            $this->echoError('Wrong Path');
            return;
        }

        $eid = intval(trim($_GET['eid']));
        $typeStr = I('type', 'all');
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        $users = trim($_GET['users']);
        if (!$this->isOwner4ExamByExamId($eid)) {
            Log::info("user id: {} exam id: {} stuid: {}, require: del one score, result: FAIL, reason: no privilege", $this->userInfo['user_id'], $users, $eid);
            $this->echoError('You have no privilege to do it!');
            return;
        }

        $where = array(
            'exam_id' => $eid,
            'user_id' => $users
        );
        $typeList = $this->getTypeList($typeStr);
        foreach ($typeList as $type) {
            $this->delScoreByType($type, $where);
        }
        Log::info("user id: {} exam id: {} stuid: {}, require: del one score, result: success", $this->userInfo['user_id'], $users, $eid);
        $this->redirect("Exam/userscore", array(
            'eid' => $eid,
            'sortdnum' => $sortdnum,
            'sortanum' => $sortanum
        ));
    }

    public function submitAllPaper() {
        $eid = I('get.eid', 0, 'intval');
        if (empty($eid)) {
            Log::info("user id: {} url error, url data: {}, ", $this->userInfo['user_id'], $_SERVER['REQUEST_URI']);
            $this->alertError('Invaild Exam');
            return;
        }

        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        if (!$this->isOwner4ExamByExamId($eid)) {
            Log::info("user id: {} exam: {}, require: submit all paper, result: FAIL, reason: no privilege", $this->userInfo['user_id'], $eid);
            $this->echoError('You have no privilege to do it!');
        }

        $allTakeIn = PrivilegeBaseModel::instance()->getTakeInExamUsersByExamId($eid);

        $allHaveScore = M('ex_student')->distinct('user_id')->field('user_id,score')
            ->where('exam_id=%d', $eid)->select();

        $haveScoreUserIds = array();
        $userIds2Submit = array();
        $negScoreUserId = array();

        foreach ($allHaveScore as $uid) {
            if ($uid['score'] >= 0) {
                $haveScoreUserIds[] = strtolower($uid['user_id']);
            } else {
                $negScoreUserId[strtolower($uid['user_id'])] = 1;
            }
        }

        foreach ($allTakeIn as $userId) {
            $_userId = strtolower($userId['user_id']);
            if (!in_array($_userId, $haveScoreUserIds)) {
                $userIds2Submit[] = $_userId;
            }
        }

        if (!empty($userIds2Submit)) {
            $userIds2Submit = array_unique($userIds2Submit);
            $field = array('start_time', 'end_time');
            $prirow = ExamBaseModel::instance()->getById($eid, $field);
            $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
            $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

            foreach ($userIds2Submit as $_uid) {
                $mark = isset($negScoreUserId[$_uid]) ? 1 : 0;
                $this->rejudgePaper($_uid, $eid, $start_timeC, $end_timeC, $mark);
                usleep(10000);
            }
        }
        Log::info("user id: {} exam: {}, require: submit all paper, result: success", $this->userInfo['user_id'], $eid);

        $this->redirect("Exam/userscore", array(
            'eid' => $eid,
            'sortdnum' => $sortdnum,
            'sortanum' => $sortanum
        ));
    }

    public function DelAllUserScore() {
        $eid = I('post.eid', 0, 'intval');
        $typeStr = I('post.type', 'all');
        if (empty($eid)) {
            Log::info("user id: {} url error, url data: {}, ", $this->userInfo['user_id'], $_SERVER['REQUEST_URI']);
            $this->echoError("bad exam id");
            return;
        }
        if (!$this->isOwner4ExamByExamId($eid)) {
            Log::info("user id: {} exam id: {}, require: del all score, result: FAIL, reason: no privilege",
                $this->userInfo['user_id'], $eid);
            $this->echoError('You have no privilege to do it!');
        }
        unset($_POST['type']);
        unset($_POST['eid']);

        $userIds = array();
        foreach ($_POST as $k => $v) {
            $userIds[] = mb_substr($k, 5);
        }
        if (!empty($userIds)) {
            $where = array(
                'exam_id' => $eid,
                'user_id' => array('in', $userIds)
            );
            $typeList = $this->getTypeList($typeStr);
            foreach ($typeList as $type) {
                $this->delScoreByType($type, $where);
            }
        }
        Log::info("user id: {} exam id: {}, require: del all score, result: success", $this->userInfo['user_id'], $eid);
        $this->redirect("Exam/userscore", array('eid' => $eid));
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
            $prirow = ExamBaseModel::instance()->getById($eid, array('start_time', 'end_time'));
            $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
            $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));
            $userlist = M('ex_student')->field('user_id')->where('exam_id=%d', $eid)->select();
            if ($userlist) {
                foreach ($userlist as $value) {
                    $this->rejudgePaper($value['user_id'], $eid, $start_timeC, $end_timeC, 1);
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
        $field = array('start_time', 'end_time');
        $prirow = ExamBaseModel::instance()->getById($eid, $field);
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

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
        $this->rejudgePaper($userId, $eid, $start_timeC, $end_timeC, $mark);
        return true;
    }

    private function rejudgePaper($userId, $eid, $start_timeC, $end_timeC, $mark) {

        $allscore = ExamBaseModel::instance()->getById($eid,
            array('choosescore', 'judgescore', 'fillscore', 'prgans', 'prgfill', 'programscore')
        );
        $choosesum = ChooseService::instance()->doRejudgeChooseByExamIdAndUserId($eid, $userId, $allscore['choosescore']);
        $judgesum = JudgeService::instance()->doRejudgeJudgeByExamIdAndUserId($eid, $userId, $allscore['judgescore']);
        $fillsum = FillService::instance()->doRejudgeFillByExamIdAndUserId($eid, $userId, $allscore);
        $programsum = ProblemService::instance()->doRejudgeProgramByExamIdAndUserId($eid, $userId, $allscore['programscore'], $start_timeC, $end_timeC);

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

    private function getTypeList($typeStr) {
        $typeList = explode(',', $typeStr);
        if (in_array('all', $typeList)) {
            $typeList = array('all');
        }
        return $typeList;
    }

    private function delScoreByType($type, $where) {
        switch ($type) {
            case "choose" : {
                M('ex_student')->where($where)->save(array("choosesum" => -1, "score" => -1));
                break;
            }
            case "judge" : {
                M('ex_student')->where($where)->save(array("judgesum" => -1, "score" => -1));
                break;
            }
            case "fill" : {
                M('ex_student')->where($where)->save(array("fillsum" => -1, "score" => -1));
                break;
            }
            case "program" : {
                M('ex_student')->where($where)->save(array("programsum" => -1, "score" => -1));
                break;
            }
            default : {
                M('ex_student')->where($where)->delete();
                break;
            }
        }
    }
}

