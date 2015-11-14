<?php
namespace Teacher\Controller;

use Teacher\Model\ExamServiceModel;
use Teacher\Model\ProblemServiceModel;
use Think\Controller;

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
            $this->error('No Such Exam!');
        }
    }

    public function index() {

        if (!$this->isOwner4ExamByExamId($this->eid)) {
            $this->error('You have no privilege of this exam~');
        }

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->eid);
        $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, 1);
        $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, 2);
        $fillans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, 3);
        $programans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->eid, 5);

        $fillans2 = array();
        if ($fillans) {
            foreach ($fillans as $key => $value) {
                $fillans2[$value['fill_id']] = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($value['fill_id'], 4);
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

    public function userscore() {
        $sqladd = SortStuScore('stu');
        $prirow = $this->isCanWatchInfo($this->eid, true);
        $query = "SELECT `stu`.`user_id`,`stu`.`nick`,`choosesum`,`judgesum`,`fillsum`,`programsum`,`score`
			FROM (SELECT `users`.`user_id`,`users`.`nick` FROM `ex_privilege`,`users` WHERE `ex_privilege`.`user_id`=`users`.`user_id` AND
			 `ex_privilege`.`rightstr`=\"e$this->eid\" )stu left join `ex_student` on `stu`.`user_id`=`ex_student`.`user_id` AND 
			`ex_student`.`exam_id`='$this->eid' $sqladd";
        $row = M()->query($query);
        $online = M('ex_stuanswer')->distinct('user_id')->field('user_id')->where('exam_id=%d', $this->eid)
            ->select();

        $isonline = array();
        if ($online) {
            foreach ($online as $key => $value) {
                $isonline[$value['user_id']] = 1;
            }
            unset($online);
        }
        $xsid = I('get.xsid', '');
        $xsname = I('get.xsname','');
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        $this->zadd('row', $row);
        $this->zadd('xsid', $xsid);
        $this->zadd('xsname', $xsname);
        $this->zadd('isonline', $isonline);
        $this->zadd('asortnum', $sortanum);
        $this->zadd('dsortnum', $sortdnum);
        $this->zadd('end_timeC', strtotime($prirow['end_time']));
        $this->auto_display();
    }

    public function adduser() {
        if (IS_POST && I('post.eid') != '') {
            if (!check_post_key()) {
                $this->error('发生错误！');
            } else if (!$this->isCreator()) {
                $this->error('You have no privilege of this exam');
            } else {
                $eid = I('post.eid', 0, 'intval');
                $flag = ExamServiceModel::instance()->addUsers2Exam($eid);
                if ($flag === true)
                    $this->success('考生添加成功', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
                else
                    $this->error('Invaild Path');
            }
        } else {
            if (!$this->isOwner4ExamByExamId($this->eid)) {
                $this->error('You have no privilege of this exam');
            } else {
                $ulist = "";
                $row = M('ex_privilege')->field('user_id')
                    ->where("rightstr='e$this->eid'")->order('user_id')->select();
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

    public function rejudge() {
        if (!$this->isSuperAdmin()) {
            $this->error('Sorry,Only admin can do');
        } else {
            $key = set_post_key();
            $this->zadd('mykey', $key);
            $this->auto_display();
        }
    }

    public function DelAllUserScore() {
        ddbg(I('post.'));
    }
}