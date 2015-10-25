<?php
namespace Teacher\Controller;

use Teacher\Model\AdminexamModel;
use Teacher\Model\AdminproblemModel;
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

        if (!$this->isowner($this->eid)) {
            $this->error('You have no privilege of this exam~');
        }

        $allscore = AdminexamModel::instance()->getallscore($this->eid);
        $chooseans = AdminproblemModel::instance()->getproblemans($this->eid, 1);
        $judgeans = AdminproblemModel::instance()->getproblemans($this->eid, 2);
        $fillans = AdminproblemModel::instance()->getproblemans($this->eid, 3);
        $programans = AdminproblemModel::instance()->getproblemans($this->eid, 5);

        $fillans2 = array();
        if ($fillans) {
            foreach ($fillans as $key => $value) {
                $fillans2[$value['fill_id']] = AdminproblemModel::instance()->getproblemans($value['fill_id'], 4);
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
        $prirow = $this->isallow($this->eid, true);
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
        $this->zadd('row', $row);
        $this->zadd('isonline', $isonline);
        $this->zadd('end_timeC', strtotime($prirow['end_time']));
        $this->auto_display();
    }

    public function adduser() {
        if (IS_POST && I('post.eid') != '') {
            if (!check_post_key()) {
                $this->error('发生错误！');
            } else if (!checkAdmin(2)) {
                $this->error('You have no privilege of this exam');
            } else {
                $eid = I('post.eid', 0, 'intval');
                $flag = AdminexamModel::instance()->addexamuser($eid);
                if ($flag === true)
                    $this->success('考生添加成功', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
                else
                    $this->error('Invaild Path');
            }
        } else {
            if (!$this->isowner($this->eid)) {
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
        $this->isallow($this->eid);
        $student = I('get.student', '', 'htmlspecialchars');
        $sqladd = '';
        if (!empty($student))
            $sqladd = " AND `user_id` like '%$student%'";

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
        if (!checkAdmin(1)) {
            $this->error('Sorry,Only admin can do');
        } else {
            $key = set_post_key();
            $this->zadd('mykey', $key);
            $this->auto_display();
        }
    }
}