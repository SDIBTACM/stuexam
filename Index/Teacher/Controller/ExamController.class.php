<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\ProblemServiceModel;

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

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->eid);
        $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $fillans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, FillBaseModel::FILL_PROBLEM_TYPE);
        $programans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, ProblemServiceModel::EXAMPROBLEM_TYPE_PROGRAM);

        $fillans2 = array();
        if ($fillans) {
            foreach ($fillans as $key => $value) {
                $fillans2[$value['fill_id']] = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($value['fill_id'], ProblemServiceModel::PROBLEMANS_TYPE_FILL);
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
                $flag = ExamServiceModel::instance()->addUsers2Exam($eid);
                if ($flag === true)
                    $this->success('考生添加成功', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
                else
                    $this->echoError('Invaild Path');
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

        $query = "SELECT `stu`.`user_id`,`stu`.`nick`,`choosesum`,`judgesum`,`fillsum`,`programsum`,`score`,`extrainfo`
			FROM (SELECT `users`.`user_id`,`users`.`nick`,`extrainfo` FROM `ex_privilege`,`users` WHERE `ex_privilege`.`user_id`=`users`.`user_id` AND
			 `ex_privilege`.`rightstr`=\"e$this->eid\" )stu left join `ex_student` on `stu`.`user_id`=`ex_student`.`user_id` AND 
			`ex_student`.`exam_id`='$this->eid' $sqladd";
        $row = M()->query($query);

        $hasSubmit = 0;
        $hasTakeIn = 0;
        foreach ($row as $r) {
            if (!is_null($r['score'])) {
                $hasSubmit++;
            }
            if ($r['extrainfo'] != 0) {
                $hasTakeIn++;
            }
        }

        $isShowDel = false;
        if ($hasTakeIn <= $hasSubmit) {
            $isShowDel = true;
        }
        $xsid = I('get.xsid', '');
        $xsname = I('get.xsname','');
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
            $sqladd = " AND `user_id` like '%$student%'";
        }

        $totalnum = M('ex_privilege')->where("rightstr='e$this->eid' $sqladd")
            ->count();

        $query = "SELECT COUNT(*) as `realnum`,MAX(`choosesum`) as `choosemax`,MAX(`judgesum`) as `judgemax`,MAX(`fillsum`) as `fillmax`,
				MAX(`programsum`) as `programmax`,MIN(`choosesum`) as `choosemin`,MIN(`judgesum`) as `judgemin`,MIN(`fillsum`) as `fillmin`,
				MIN(`programsum`) as `programmin`,MAX(`score`) as `scoremax`,MIN(`score`) as `scoremin`,AVG(`choosesum`) as `chooseavg`,
				AVG(`judgesum`) as `judgeavg`,AVG(`fillsum`) as `fillavg`,AVG(`programsum`) as `programavg`,
				AVG(`score`) as `scoreavg` FROM `ex_student` WHERE `exam_id`='$this->eid' $sqladd";
        $row = M()->query($query);

        $fd[] = M('ex_student')->where("score<60 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=60 and score<70 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=70 and score<80 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=80 and score<90 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=90 and score<=100 and exam_id=$this->eid $sqladd")->count();

        $this->zadd('totalnum', $totalnum);
        $this->zadd('row', $row[0]);
        $this->zadd('fd', $fd);
        $this->zadd('student', $student);

        $this->auto_display();
    }

    public function programRank() {

        $this->isCanWatchInfo($this->eid);

        $where = array(
            'exam_id' => $this->eid,
            'type' => 4,
            'answer_id' => 1
        );
        $field = array('user_id', 'question_id');
        $programRank = M('ex_stuanswer')->field($field)->where($where)->select();
        $userRank = array();

        foreach ($programRank as $p) {
            $userRank[$p['user_id']][$p['question_id']] = 4;
        }
        $programs = M('exp_question')->field('question_id')
            ->where('exam_id=%d and type=4', $this->eid)->order('question_id')
            ->select();

        $this->zadd('programIds', $programs);
        $this->zadd('userRank', $userRank);
        $this->zadd('programRank', $programRank);
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
}