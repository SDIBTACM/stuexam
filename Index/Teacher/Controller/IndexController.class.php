<?php
namespace Teacher\Controller;

use Think\Controller;

class IndexController extends TemplateController
{

    public function index() {
        $sch = getexamsearch();
        $key = set_get_key();
        $mypage = splitpage('exam', $sch['sql']);
        $row = M('exam')->field('exam_id,title,start_time,end_time,creator')
            ->where($sch['sql'])->order('exam_id desc')
            ->limit($mypage['sqladd'])->select();
        $this->zadd('row', $row);
        $this->zadd('mypage', $mypage);
        $this->zadd('search', $sch['search']);
        $this->zadd('mykey', $key);
        $this->auto_display();
    }

    public function choose() {
        $sch = getproblemsearch();
        $key = set_get_key();
        $isadmin = checkAdmin(1);
        $mypage = splitpage('ex_choose', $sch['sql']);
        $numofchoose = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_choose')->field('choose_id,question,creator,point,easycount')
            ->where($sch['sql'])->order('choose_id asc')->limit($mypage['sqladd'])
            ->select();
        $this->zadd('row', $row);
        $this->zadd('mypage', $mypage);
        $this->zadd('numofchoose', $numofchoose);
        $this->zadd('isadmin', $isadmin);
        $this->zadd('mykey', $key);
        $this->zadd('search', $sch['search']);
        $this->zadd('problem', $sch['problem']);
        $this->auto_display();
    }

    public function judge() {

        $sch = getproblemsearch();
        $key = set_get_key();
        $isadmin = checkAdmin(1);
        $mypage = splitpage('ex_judge', $sch['sql']);
        $numofjudge = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_judge')->field('judge_id,question,creator,point,easycount')
            ->where($sch['sql'])->order('judge_id asc')->limit($mypage['sqladd'])
            ->select();
        $this->zadd('row', $row);
        $this->zadd('numofjudge', $numofjudge);
        $this->zadd('isadmin', $isadmin);
        $this->zadd('mykey', $key);
        $this->zadd('mypage', $mypage);
        $this->zadd('search', $sch['search']);
        $this->zadd('problem', $sch['problem']);
        $this->auto_display();
    }

    public function fill() {
        $sch = getproblemsearch();
        $key = set_get_key();
        $isadmin = checkAdmin(1);
        $mypage = splitpage('ex_fill', $sch['sql']);
        $numoffill = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = m('ex_fill')->field('fill_id,question,creator,point,easycount,kind')
            ->where($sch['sql'])->order('fill_id asc')->limit($mypage['sqladd'])
            ->select();
        $this->zadd('row', $row);
        $this->zadd('mypage', $mypage);
        $this->zadd('numoffill', $numoffill);
        $this->zadd('isadmin', $isadmin);
        $this->zadd('mykey', $key);
        $this->zadd('search', $sch['search']);
        $this->zadd('problem', $sch['problem']);
        $this->auto_display();
    }

    public function point() {
        if (!checkAdmin(1)) {
            $this->error('Sorry,Only admin can do');
        }
        $pnt = M('ex_point')->order('point_pos')->select();
        $this->zadd('pnt', $pnt);
        $this->auto_display();
    }

    public function dosort() {
        if (IS_AJAX && I('get.id', false) && I('get.pos', false)) {
            $arr['point_id'] = intval($_GET['id']);
            $arr['point_pos'] = intval($_GET['pos']);
            M('ex_point')->data($arr)->save();
            echo "success";
        } else {
            echo "wrong method";
        }
    }
}
