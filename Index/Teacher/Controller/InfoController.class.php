<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\ExamBaseModel;

use Teacher\Service\ChooseService;
use Teacher\Service\JudgeService;
use Teacher\Service\ExamService;
use Teacher\Service\FillService;
use Teacher\Service\ProblemService;

use Think\Controller;

class InfoController extends TemplateController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function showpaper() {
        if (!(isset($_GET['eid']) && isset($_GET['users']))) {
            $this->echoError('Wrong Path');
            return;
        }

        $eid = intval(trim($_GET['eid']));
        $this->isCanWatchInfo($eid);

        $users = trim($_GET['users']);
        $row = ExamBaseModel::instance()->getExamInfoById($eid, array('title'));

        $_res = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($users, $eid);
        if (empty($_res)) {
            $this->echoError("The student have no privilege to take part in it");
        }

        $allscore = ExamService::instance()->getBaseScoreByExamId($eid);

        $choosearr = ExamService::instance()->getUserAnswer($eid, $users, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $judgearr = ExamService::instance()->getUserAnswer($eid, $users, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $fillarr = ExamService::instance()->getUserAnswer($eid, $users, FillBaseModel::FILL_PROBLEM_TYPE);

        $chooseans = ProblemService::instance()->getProblemsAndAnswer4Exam($eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $judgeans = ProblemService::instance()->getProblemsAndAnswer4Exam($eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $fillans = ProblemService::instance()->getProblemsAndAnswer4Exam($eid, FillBaseModel::FILL_PROBLEM_TYPE);
        $fillans2 = array();

        if ($fillans) {
            foreach ($fillans as $key => $value) {
                $fillans2[$value['fill_id']] = ProblemService::instance()
                    ->getProblemsAndAnswer4Exam($value['fill_id'], ProblemService::PROBLEMANS_TYPE_FILL);
            }
        }
        $this->zadd('title', $row['title']);
        $this->zadd('allscore', $allscore);
        $this->zadd('choosearr', $choosearr);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('fillarr', $fillarr);
        $this->zadd('chooseans', $chooseans);
        $this->zadd('judgeans', $judgeans);
        $this->zadd('fillans', $fillans);
        $this->zadd('fillans2', $fillans2);

        $this->auto_display('paper');
    }

    public function delscore() {
        if (!(isset($_GET['eid']) && isset($_GET['users']))) {
            $this->echoError('Wrong Path');
            return;
        }

        $eid = intval(trim($_GET['eid']));
        $typeStr = I('type', 'all');
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        $users = trim($_GET['users']);
        if (!$this->isOwner4ExamByExamId($eid)) {
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
        $this->redirect("Exam/userscore", array(
            'eid' => $eid,
            'sortdnum' => $sortdnum,
            'sortanum' => $sortanum
        ));
    }

    public function submitAllPaper() {
        $eid = I('get.eid', 0, 'intval');
        if (empty($eid)) {
            $this->alertError('Invaild Exam');
            return;
        }

        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        if (!$this->isOwner4ExamByExamId($eid)) {
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
            $prirow = ExamBaseModel::instance()->getExamInfoById($eid, $field);
            $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
            $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

            foreach ($userIds2Submit as $_uid) {
                $mark = isset($negScoreUserId[$_uid]) ? 1 : 0;
                $this->rejudgepaper($_uid, $eid, $start_timeC, $end_timeC, $mark);
                usleep(10000);
            }
        }
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
            $this->echoError("bad exam id");
            return;
        }
        if (!$this->isOwner4ExamByExamId($eid)) {
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
        $this->redirect("Exam/userscore", array('eid' => $eid));
    }

    public function submitpaper() {
        if (!(isset($_GET['eid']) && isset($_GET['users']))) {
            $this->echoError('Wrong Path');
            return;
        }
        $eid = intval(trim($_GET['eid']));
        $userId = trim($_GET['users']);
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        if (!$this->isOwner4ExamByExamId($eid)) {
            $this->echoError('You have no privilege to do it!');
        }
        $flag = $this->dojudgeone($eid, $userId);
        if ($flag) {
            $this->redirect("Exam/userscore", array(
                'eid' => $eid,
                'sortdnum' => $sortdnum,
                'sortanum' => $sortanum
            ));
        }
    }

    public function hardSubmit() {
        $eid = I('get.eid', 0, 'intval');
        $userId = I('get.userId', '');
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        if (!$this->isOwner4ExamByExamId($eid)) {
            $this->echoError('You have no privilege to do it!');
        }
        if (empty($eid) || empty($userId)) {
        } else {
            $this->dojudgeone($eid, $userId);
        }
        $this->redirect("Exam/userscore", array(
            'eid' => $eid,
            'sortdnum' => $sortdnum,
            'sortanum' => $sortanum
        ));
    }

    public function dorejudge() {
        if (!(IS_POST && I('post.eid'))) {
            $this->echoError('Wrong Method');
            return;
        }
        if (!check_post_key() || !$this->isSuperAdmin()) {
            $this->echoError('发生错误！');
        }
        $eid = intval($_POST['eid']);

        if (I('post.rjall')) {
            $prirow = ExamBaseModel::instance()->getExamInfoById($eid, array('start_time', 'end_time'));
            $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
            $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));
            $userlist = M('ex_student')->field('user_id')->where('exam_id=%d', $eid)->select();
            if ($userlist) {
                foreach ($userlist as $value) {
                    $this->rejudgepaper($value['user_id'], $eid, $start_timeC, $end_timeC, 1);
                }
                unset($userlist);
            }
            $this->success('全部重判成功！', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
        } else if (I('post.rjone')) {
            $rjuserid = test_input($_POST['rjuserid']);
            $flag = $this->dojudgeone($eid, $rjuserid);
            if ($flag) {
                $this->success('重判成功！', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
            }
        } else {
            $this->echoError('Invaild Path');
        }
    }

    private function dojudgeone($eid, $userId) {
        $field = array('start_time', 'end_time');
        $prirow = ExamBaseModel::instance()->getExamInfoById($eid, $field);
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

        $rightstr = "e$eid";
        $cnt1 = M('ex_privilege')
            ->where("user_id='%s' and rightstr='%s'", $userId, $rightstr)
            ->count();
        if ($cnt1 == 0) {
            $this->echoError('Student ID is Wrong!');
            return false;
        }
        if (time() < $start_timeC) {
            $this->echoError('Exam Not Start');
        }
        $mark = M('ex_student')
            ->where("exam_id=%d and user_id='%s'", $eid, $userId)
            ->count();
        $this->rejudgepaper($userId, $eid, $start_timeC, $end_timeC, $mark);
        return true;
    }

    private function rejudgepaper($userId, $eid, $start_timeC, $end_timeC, $mark) {

        $allscore = ExamService::instance()->getBaseScoreByExamId($eid);

        $choosesum = ChooseService::instance()->doRejudgeChooseByExamIdAndUserId($eid, $userId, $allscore['choosescore']);
        $judgesum = JudgeService::instance()->doRejudgeJudgeByExamIdAndUserId($eid, $userId, $allscore['judgescore']);
        $fillsum = FillService::instance()->doRejudgeFillByExamIdAndUserId($eid, $userId, $allscore);
        $programsum = ProblemService::instance()->doRejudgeProgramByExamIdAndUserId($eid, $userId, $allscore['programscore'], $start_timeC, $end_timeC);

        $sum = $choosesum + $judgesum + $fillsum + $programsum;
        if ($mark == 0) { // if the student has not submitted the paper
            $sql = "INSERT INTO `ex_student` VALUES('" . $userId . "','$eid','$sum','$choosesum','$judgesum','$fillsum','$programsum')";
            M()->execute($sql);
        } else {
            $sql = "UPDATE `ex_student` SET `score`='$sum',`choosesum`='$choosesum',`judgesum`='$judgesum',`fillsum`='$fillsum',`programsum`='$programsum'
			WHERE `user_id`='" . $userId . "' AND `exam_id`='$eid'";
            M()->execute($sql);
        }
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

