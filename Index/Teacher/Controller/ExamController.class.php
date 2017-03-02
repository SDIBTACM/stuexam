<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\QuestionBaseModel;

use Teacher\Service\ExamService;
use Teacher\Service\ProblemService;


class ExamController extends TemplateController
{

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
            } else if (!$this->isCreator()) {
                $this->echoError('You have no privilege of this exam');
            } else {
                $eid = I('post.eid', 0, 'intval');
                $ulist = trim($_POST['ulist']);
                $flag = ExamService::instance()->addUsers2Exam($eid, $ulist);
                if ($flag === true) {
                    $this->success('考生添加成功', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
                } else {
                    $this->echoError('Invaild Path');
                }
            }
        } else {
            if (!$this->isOwner4ExamByExamId($this->eid)) {
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
    public function userscore() {
        $sqladd = SortStuScore('stu');
        $prirow = $this->isCanWatchInfo($this->eid, true);

        $isExamEnd = (time() > strtotime($prirow['end_time']) ? true : false);

        $query = "SELECT `stu`.`user_id`,`stu`.`nick`,`choosesum`,`judgesum`,`fillsum`,`programsum`,`score`,`extrainfo` ".
			"FROM (SELECT `users`.`user_id`,`users`.`nick`,`extrainfo` FROM `ex_privilege`,`users` WHERE `ex_privilege`.`user_id`=`users`.`user_id` AND ".
            "`ex_privilege`.`rightstr`=\"e$this->eid\" )stu left join `ex_student` on `stu`.`user_id`=`ex_student`.`user_id` AND ".
			"`ex_student`.`exam_id`='$this->eid' $sqladd";
        $row = M()->query($query);

        $hasSubmit = 0;
        $hasTakeIn = 0;
        foreach ($row as &$r) {
            $r['hasTakenIn'] = 0;
            $r['hasSubmit'] = 0;
            if (isset($r['score']) && $r['score'] >= 0) {
                $hasSubmit++;
                $r['hasSubmit'] = 1;
            }
            if ($r['extrainfo'] != 0 ||
                (isset($r['score']) && ($r['choosesum'] + $r['judgesum'] + $r['fillsum'] + $r['programsum']) != -4)) {
                $hasTakeIn++;
                $r['hasTakenIn'] = 1;
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

    public function analysis() {
        $this->isCanWatchInfo($this->eid);
        $student = I('get.student', '', 'htmlspecialchars');
        $sqladd = '';
        if (!empty($student)) {
            $sqladd = " AND `user_id` like '$student%'";
        }

        $totalnum = M('ex_privilege')->where("rightstr='e$this->eid' $sqladd")
            ->count();

        $query = "SELECT COUNT(*) as `realnum`,MAX(`choosesum`) as `choosemax`,MAX(`judgesum`) as `judgemax`,MAX(`fillsum`) as `fillmax`,".
				"MAX(`programsum`) as `programmax`,MIN(`choosesum`) as `choosemin`,MIN(`judgesum`) as `judgemin`,MIN(`fillsum`) as `fillmin`,".
				"MIN(`programsum`) as `programmin`,MAX(`score`) as `scoremax`,MIN(`score`) as `scoremin`,AVG(`choosesum`) as `chooseavg`,".
				"AVG(`judgesum`) as `judgeavg`,AVG(`fillsum`) as `fillavg`,AVG(`programsum`) as `programavg`,".
				"AVG(`score`) as `scoreavg` FROM `ex_student` WHERE `exam_id`='$this->eid' $sqladd AND `score` >= 0";
        $row = M()->query($query);

        $fd[] = M('ex_student')->where("score>=0  and score<60 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=60 and score<70 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=70 and score<80 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=80 and score<90 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=90 and score<=100 and exam_id=$this->eid $sqladd")->count();

        $query = array(
            'exam_id' => $this->eid,
            'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
            'order' => 'exp_qid'
        );
        $programIds = QuestionBaseModel::instance()->queryData($query, array('question_id'));

        $programAvgScore = $this->getEachProgramAvgScore($programIds, $totalnum, $sqladd);

        $this->zadd('totalnum', $totalnum);
        $this->zadd('row', $row[0]);
        $this->zadd('fd', $fd);
        $this->zadd('student', $student);
        $this->zadd("programIds", $programIds);
        $this->zadd("programAvgScore", $programAvgScore);

        $this->auto_display();
    }

    public function programRank() {

        $this->isCanWatchInfo($this->eid);

        $where = array(
            'exam_id' => $this->eid,
            'type' => 4,
            'answer_id' => 1,
//            'answer' => 4
        );
        $field = array('user_id', 'question_id', 'answer');
        $programRank = M('ex_stuanswer')->field($field)->where($where)->select();
        $userRank = array();
        $users = array();
        $unames = array();
        $programCount = array();

        $query = "select user_id, count(distinct question_id) as cnt" .
            " from ex_stuanswer where exam_id = ".  $this->eid .
            " and type = 4 and answer_id = 1 and answer = 4 group by user_id order by cnt desc";
        $acCount = M()->query($query);
        foreach($acCount as $ac) {
            $programCount[$ac['user_id']] = $ac['cnt'];
            $users[] = $ac['user_id'];
        }

        foreach ($programRank as $p) {
            $userRank[$p['user_id']][$p['question_id']] = $p['answer'];
            if (!in_array($p['user_id'], $users)) {
                $users[] = $p['user_id'];
            }
        }

        $userIds_chunk = array_chunk($users, 50);
        foreach ($userIds_chunk as $_userIds) {
            $where = array(
                'user_id' => array('in', $_userIds)
            );
            $field = array('user_id', 'nick');
            $_unames = M('users')->field($field)->where($where)->select();
            foreach ($_unames as $_uname) {
                $unames[$_uname['user_id']] = $_uname['nick'];
            }
            usleep(10000);
        }

        $query = array(
            'exam_id' => $this->eid,
            'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
            'order' => 'exp_qid'
        );
        $programs = QuestionBaseModel::instance()->queryData($query, array('question_id'));

        $this->zadd('unames', $unames);
        $this->zadd('userIds', $users);
        $this->zadd('programIds', $programs);
        $this->zadd('userRank', $userRank);
        $this->zadd('programCount', $programCount);
        $this->auto_display('ranklist');
    }

    // only admin can do
    public function rejudge() {
        if (!$this->isSuperAdmin()) {
            $this->error('Sorry,Only admin can do');
        } else {
            $key = set_post_key();
            $this->zadd('mykey', $key);
            $this->auto_display();
        }
    }

    private function getEachProgramAvgScore($programIds, $personCnt, $sqladd) {

        $examId = $this->eid;
        $ans = array();

        foreach($programIds as $_programId) {

            $programId = $_programId['question_id'];

            if ($personCnt == 0) {
                $ans[$programId] = 0;
                continue;
            }

            $allScore = ExamService::instance()->getBaseScoreByExamId($examId);
            $examBase = ExamBaseModel::instance()->getById($examId);
            $sTime = $examBase['start_time'];
            $eTime = $examBase['end_time'];

            $programScore = $allScore['programscore'];

            $sql = "select (sum(rate) * $programScore / $personCnt) as r from (" .
                "select user_id, if(max(pass_rate)=0.99, 1, max(pass_rate)) as rate from solution " .
                "where problem_id=$programId and pass_rate > 0 and " .
                "in_date>='$sTime' and in_date<='$eTime' and user_id in (select user_id from ex_privilege where rightstr='e$examId') " .
                "$sqladd group by user_id" .
                ") t";

            $res = M()->query($sql);
            if (empty($res)) {
                $ans[$programId] = 0;
            } else {
                $ans[$programId] = isset($res[0]['r']) ? $res[0]['r'] : 0;
            }
        }
        return $ans;
    }
}