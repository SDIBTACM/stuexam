<?php
namespace Teacher\Controller;

use Teacher\Model\ExamServiceModel;
use Teacher\Model\ProblemServiceModel;
use Teacher\Model\ExamBaseModel;
use Think\Controller;

class InfoController extends TemplateController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function showpaper() {
        if (isset($_GET['eid']) && isset($_GET['users'])) {
            $eid = intval(trim($_GET['eid']));
            $this->isCanWatchInfo($eid);

            $users = trim($_GET['users']);
            $rightstr = "e$eid";
            $row = M('exam')->field('title')->where('exam_id=%d', $eid)->find();

            $num = M('ex_privilege')->where("user_id='%s' and rightstr='%s'", $users, $rightstr)
                ->count();
            if (!$num) {
                $this->error("The student have no privilege to take part in it");
            }

            $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($eid);

            $choosearr = ExamServiceModel::instance()->getUserAnswer($eid, $users, 1);
            $judgearr = ExamServiceModel::instance()->getUserAnswer($eid, $users, 2);
            $fillarr = ExamServiceModel::instance()->getUserAnswer($eid, $users, 3);

            $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, 1);
            $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, 2);
            $fillans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, 3);
            $fillans2 = array();

            if ($fillans) {
                foreach ($fillans as $key => $value) {
                    $fillans2[$value['fill_id']] = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($value['fill_id'], 4);
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
        } else {
            $this->error('Wrong Path');
        }
    }

    public function delscore() {
        if (isset($_GET['eid']) && isset($_GET['users'])) {
            $eid = intval(trim($_GET['eid']));
            $users = trim($_GET['users']);
            if (!$this->isOwner4ExamByExamId($eid)) {
                $this->error('You have no privilege to do it!');
            } else {
                M('ex_student')
                    ->where("exam_id=%d and user_id='%s'", $eid, $users)
                    ->delete();
                $this->redirect("Exam/userscore", array('eid' => $eid));
            }
        } else {
            $this->error('Wrong Path');
        }
    }

    public function submitAllPaper() {
        $eid = I('get.eid', 0, 'intval');
        if (!empty($eid)) {

            if (!$this->isOwner4ExamByExamId($eid)) {
                $this->error('You have no privilege to do it!');
            }

            $allTakeIn = M('ex_stuanswer')->distinct('user_id')->field('user_id')->where('exam_id=%d', $eid)
                ->select();
            $allHaveScore = M('ex_student')->distinct('user_id')->field('user_id')
                ->where('exam_id=%d', $eid)->select();


            $haveScoreUserIds = array();
            $userIds2Submit = array();

            foreach($allHaveScore as $uid) {
                $haveScoreUserIds[] = $uid['user_id'];
            }

            foreach ($allTakeIn as $userId) {
                if (!in_array($userId['user_id'], $haveScoreUserIds)) {
                    $userIds2Submit[] = $userId['user_id'];
                }
            }

            if (!empty($userIds2Submit)) {
                $field = array('start_time','end_time');
                $prirow = ExamBaseModel::instance()->getExamInfoById($eid, $field);
                $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
                $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

                foreach ($userIds2Submit as $_uid) {
                    $this->rejudgepaper($_uid, $eid, $start_timeC, $end_timeC, 0);
                    usleep(10000);
                }
            }
            $this->redirect("Exam/userscore", array('eid' => $eid));
        } else {
            $this->alertError('Invaild Exam');
        }
    }

    public function submitpaper() {
        if (isset($_GET['eid']) && isset($_GET['users'])) {
            $eid = intval(trim($_GET['eid']));
            $users = trim($_GET['users']);
            if (!$this->isOwner4ExamByExamId($eid)) {
                $this->error('You have no privilege to do it!');
            }
            $flag = $this->dojudgeone($eid, $users);
            if ($flag) {
                $this->redirect("Exam/userscore", array('eid' => $eid));
            }
        } else {
            $this->error('Wrong Path');
        }
    }

    public function hardSubmit() {
        $eid = I('get.eid', 0, 'intval');
        $userId = I('get.userId', '');
        if (!$this->isOwner4ExamByExamId($eid) ) {
            $this->error('You have no privilege to do it!');
        }
        if (empty($eid) && empty($userId)) {
        } else {
            $this->dojudgeone($eid, $userId);
        }
        $this->redirect("Exam/userscore", array('eid' => $eid));
    }

    public function dorejudge() {
        if (IS_POST && I('post.eid')) {
            if (!check_post_key() || !$this->isSuperAdmin()) {
                $this->error('发生错误！');
            }
            $eid = intval($_POST['eid']);

            if (I('post.rjall')) {
                $prirow = M('exam')->field('start_time,end_time')
                    ->where('exam_id=%d', $eid)->find();
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
                if ($flag)
                    $this->success('重判成功！', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
            } else
                $this->error('Invaild Path');
        } else {
            $this->error('Wrong Method');
        }
    }

    private function dojudgeone($eid, $users) {
        $prirow = M('exam')->field('start_time,end_time')
            ->where('exam_id=%d', $eid)->find();
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

        $rightstr = "e$eid";
        $cnt1 = M('ex_privilege')
            ->where("user_id='%s' and rightstr='%s'", $users, $rightstr)
            ->count();
        if ($cnt1 == 0) {
            $this->error('Student ID is Wrong!');
        } else {
            if (time() < $start_timeC) {
                $this->error('Exam Not Start');
            }
            $mark = M('ex_student')
                ->where("exam_id=%d and user_id='%s'", $eid, $users)
                ->count();
            $this->rejudgepaper($users, $eid, $start_timeC, $end_timeC, $mark);
            return true;
        }
        return false;
    }

    public function rejudgepaper($users, $eid, $start_timeC, $end_timeC, $mark) {

        $choosesum = 0;
        $judgesum = 0;
        $fillsum = 0;
        $programsum = 0;
        $sum = 0;
        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($eid);

        $choosearr = ExamServiceModel::instance()->getUserAnswer($eid, $users, 1);
        $query = "SELECT `choose_id`,`answer` FROM `ex_choose` WHERE `choose_id` IN
		(SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='1')";
        $row = M()->query($query);
        if ($row) {
            foreach ($row as $key => $value) {
                if (isset($choosearr[$value['choose_id']])) {
                    $myanswer = $choosearr[$value['choose_id']];
                    if ($myanswer == $value['answer'])
                        $choosesum += $allscore['choosescore'];
                }
            }
            unset($row);
            unset($choosearr);
        }
        //choose over

        $judgearr = ExamServiceModel::instance()->getUserAnswer($eid, $users, 2);;
        $query = "SELECT `judge_id`,`answer` FROM `ex_judge` WHERE `judge_id` IN
		(SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='2')";
        $row = M()->query($query);
        if ($row) {
            foreach ($row as $key => $value) {
                if (isset($judgearr[$value['judge_id']])) {
                    $myanswer = $judgearr[$value['judge_id']];
                    if ($myanswer == $value['answer'])
                        $judgesum += $allscore['judgescore'];
                }
            }
            unset($row);
            unset($judgearr);
        }
        //judge over

        $fillarr = ExamServiceModel::instance()->getUserAnswer($eid, $users, 3);
        $query = "SELECT `fill_answer`.`fill_id`,`answer_id`,`answer`,`answernum`,`kind` FROM `fill_answer`,`ex_fill` WHERE
		`fill_answer`.`fill_id`=`ex_fill`.`fill_id` AND `fill_answer`.`fill_id` IN ( SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='3')";
        $row = M()->query($query);
        if ($row) {
            foreach ($row as $key => $value) {
                if (isset($fillarr[$value['fill_id']][$value['answer_id']])
                    && (!empty($fillarr[$value['fill_id']][$value['answer_id']])
                        || $fillarr[$value['fill_id']][$value['answer_id']] == "0")
                ) {

                    $myanswer = trim($fillarr[$value['fill_id']][$value['answer_id']]);

                    $rightans = trim($value['answer']);

                    if ($myanswer == $rightans && strlen($myanswer) == strlen($rightans)) {
                        if ($value['kind'] == 1)
                            $fillsum += $allscore['fillscore'];
                        else if ($value['kind'] == 2)
                            $fillsum = $fillsum + $allscore['prgans'] / $value['answernum'];
                        else if ($value['kind'] == 3)
                            $fillsum = $fillsum + $allscore['prgfill'] / $value['answernum'];
                    }
                }
            }
            unset($row);
            unset($fillarr);
        }
        //fillover

        $query = "SELECT distinct `question_id`,`result` FROM `exp_question`,`solution` WHERE `exam_id`='$eid' AND `type`='4' AND `result`='4'
		AND `in_date`>'$start_timeC' AND `in_date`<'$end_timeC' AND `user_id`='" . $users . "' AND `exp_question`.`question_id`=`solution`.`problem_id`";
        $row = M()->query($query);
        $row_cnt = count($row);
        $programsum = $row_cnt * $allscore['programscore'];
        //$program over

        $sum = $choosesum + $judgesum + $fillsum + $programsum;
        if ($mark == 0) { // if the student has not submitted the paper
            $sql = "INSERT INTO `ex_student` VALUES('" . $users . "','$eid','$sum','$choosesum','$judgesum','$fillsum','$programsum')";
            M()->execute($sql);
        } else {
            $sql = "UPDATE `ex_student` SET `score`='$sum',`choosesum`='$choosesum',`judgesum`='$judgesum',`fillsum`='$fillsum',`programsum`='$programsum'
			WHERE `user_id`='" . $users . "' AND `exam_id`='$eid'";
            M()->execute($sql);
        }
    }
}

