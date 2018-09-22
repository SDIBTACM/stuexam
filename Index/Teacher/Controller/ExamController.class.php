<?php

namespace Teacher\Controller;

use Basic\Log;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Service\ExamService;
use Teacher\Service\ProblemService;


class ExamController extends TemplateController {

    private $eid = null;

    public function _initialize() {
        parent::_initialize();
        if (isset($_GET['eid'])) {
            $this->eid = intval($_GET['eid']);
            $this->zadd('eid', $this->eid);
        } else if (isset($_POST['eid'])) {
            $this->eid = intval($_POST['eid']);
        } else {
            $this->echoError('No Such Exam!');
        }
    }

    //owner can do
    public function index() {

        if (!$this->isOwner4ExamByExamId($this->eid)) {
            $this->echoError('You have no privilege of this exam~');
        }

        $allscore = ExamService::instance()->getBaseScoreByExamId($this->eid);
        $chooseans = ProblemService::instance()->getProblemsAndAnswer4Exam($this->eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $judgeans = ProblemService::instance()->getProblemsAndAnswer4Exam($this->eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $fillans = ProblemService::instance()->getProblemsAndAnswer4Exam($this->eid, FillBaseModel::FILL_PROBLEM_TYPE);
        $programans = ProblemService::instance()->getProblemsAndAnswer4Exam($this->eid, ProblemService::PROGRAM_PROBLEM_TYPE);

        $fillans2 = array();
        if ($fillans) {
            foreach ($fillans as $key => $value) {
                $fillans2[$value['fill_id']] = ProblemService::instance()->getProblemsAndAnswer4Exam($value['fill_id'], ProblemService::PROBLEMANS_TYPE_FILL);
            }
        }
        $numofchoose = count($chooseans);
        $numofjudge = count($judgeans);
        $numoffill = 0;
        $numofprgans = 0;
        $numofprgfill = 0;
        $numofprogram = count($programans);

        $this->zadd('allscore', $allscore);
        $this->zadd('chooseans', $chooseans);
        $this->zadd('judgeans', $judgeans);
        $this->zadd('fillans', $fillans);
        $this->zadd('fillans2', $fillans2);
        $this->zadd('programans', $programans);

        $this->zadd('choosenum', $numofchoose);
        $this->zadd('judgenum', $numofjudge);
        $this->zadd('fillnum', $numoffill);
        $this->zadd('prgansnum', $numofprgans);
        $this->zadd('prgfillnum', $numofprgfill);
        $this->zadd('programnum', $numofprogram);

        $this->auto_display();
    }

    public function adduser() {
        if (IS_POST && I('post.eid') != '') {
            if (!check_post_key()) {
                $this->echoError('发生错误！');
                Log::error("user id: {} post key error", $this->userInfo['user_id']);
            } else if (!$this->isCreator()) {
                Log::info("user id: {} exam id: {}, require: add user, result: FAIL, reason: no privilege",
                    $this->userInfo['user_id'], I('post.eid', 0, 'intval'));
                $this->echoError('You have no privilege of this exam');
            } else {
                $eid = I('post.eid', 0, 'intval');
                $ulist = trim($_POST['ulist']);
                $flag = ExamService::instance()->addUsers2Exam($eid, $ulist);
                if ($flag === true) {
                    Log::info("user id: {} exam id: {}, require: add user, result: success", $this->userInfo['user_id'], $eid);
                    $this->success('考生添加成功', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
                } else {
                    Log::info("user id: {} exam id: {}, require: add stu, result: FAIL, data: {}",
                        $this->userInfo['user_id'], $eid, $ulist);
                    $this->echoError('Invaild Path');
                }
            }
        } else {
            if (!$this->isOwner4ExamByExamId($this->eid)) {
                Log::info("user id: {} exam id: {}, require:  check user, result: FAIL, reason: no privilege", $this->userInfo['user_id'], I('post.eid', 0, 'intval'));
                $this->echoError('You have no privilege of this exam');
            } else {
                $ulist = "";
                $row = PrivilegeBaseModel::instance()->getUsersByExamId($this->eid, array('user_id'));
                if ($row) {
                    $cnt = 0;
                    foreach ($row as $key => $value) {
                        if ($cnt) $ulist .= "\n";
                        $ulist .= $value['user_id'];
                        $cnt++;
                    }
                    unset($row);
                }
                $key = set_post_key();
                $this->zadd('mykey', $key);
                $this->zadd('ulist', $ulist);
                $this->auto_display();
            }
        }
    }

    // teacher can do
    public function userScore() {
        $sqladd = SortStuScore('stu');
        $prirow = $this->isCanWatchInfo($this->eid, true);

        $isExamEnd = (time() > strtotime($prirow['end_time']) ? true : false);

        $query = "SELECT `stu`.`user_id`,`stu`.`nick`,`choosesum`,`judgesum`,`fillsum`,`programsum`,`score`,`extrainfo` " .
            "FROM (SELECT `users`.`user_id`,`users`.`nick`,`extrainfo` FROM `ex_privilege`,`users` WHERE `ex_privilege`.`user_id`=`users`.`user_id` AND " .
            "`ex_privilege`.`rightstr`=\"e$this->eid\" )stu left join `ex_student` on `stu`.`user_id`=`ex_student`.`user_id` AND " .
            "`ex_student`.`exam_id`='$this->eid' $sqladd";
        $row = M()->query($query);

        $seeWAStudentMap = $this->getAllStudentMapCanSeeWrongAnswer($this->eid);
        $hasSubmit = 0;
        $hasTakeIn = 0;
        foreach ($row as &$r) {
            $r['hasTakenIn'] = 0;
            $r['hasSubmit'] = 0;
            $r['canSeeWA'] = 0;
            if (isset($r['score']) && $r['score'] >= 0) {
                $hasSubmit++;
                $r['hasSubmit'] = 1;
            }
            if ($r['extrainfo'] != 0 ||
                (isset($r['score']) && ($r['choosesum'] + $r['judgesum'] + $r['fillsum'] + $r['programsum']) != -4)) {
                $hasTakeIn++;
                $r['hasTakenIn'] = 1;
            }
            if (isset($seeWAStudentMap[$r['user_id']])) {
                $r['canSeeWA'] = 1;
            }
        }
        unset($r);

        $isShowDel = false;
        if ($hasTakeIn <= $hasSubmit) {
            $isShowDel = true;
        }
        $xsid = I('get.xsid', '');
        $xsname = I('get.xsname', '');
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        $this->zadd('row', $row);
        $this->zadd('xsid', $xsid);
        $this->zadd('xsname', $xsname);
        $this->zadd('asortnum', $sortanum);
        $this->zadd('dsortnum', $sortdnum);
        $this->zadd('isEnd', $isExamEnd);
        $this->zadd('isShowDel', $isShowDel);
        $this->auto_display();
    }

    // only admin can do
    public function rejudge() {
        if (!$this->isSuperAdmin()) {
            Log::info("user id: {} exam id: {}, require: rejudge, result: FAIL, reason: no privilege", $this->userInfo['user_id'], I('post.eid', 0, 'intval'));
            $this->error('Sorry,Only admin can do');
        } else {
            $key = set_post_key();
            Log::info("user id: {}, exam id: {} require: rejudge, result: success", $this->userInfo['user_id'], I('post.eid', 0, 'intval'));
            $this->zadd('mykey', $key);
            $this->auto_display();
        }
    }

    private function getAllStudentMapCanSeeWrongAnswer($examId) {
        $field = array('user_id');
        $where = array(
            'rightstr' => "wa$examId"
        );
        $allStudent = PrivilegeBaseModel::instance()->queryAll($where, $field);
        $answer = array();
        foreach ($allStudent as $student) {
            $answer[$student['user_id']] = 1;
        }
        unset($allStudent);
        return $answer;
    }
}
