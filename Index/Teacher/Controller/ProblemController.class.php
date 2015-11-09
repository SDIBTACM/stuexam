<?php
namespace Teacher\Controller;

use Teacher\Model\ProblemServiceModel;
use Think\Controller;

class ProblemController extends TemplateController
{

    private $eid = null;

    public function _initialize() {
        parent::_initialize();
        if (isset($_GET['eid']) && isset($_GET['type'])) {
            $this->eid = intval($_GET['eid']);
            $type = intval($_GET['type']);
            $this->zadd('eid', $this->eid);
            $this->zadd('type', $type);
            if (!$this->isOwner4ExamByExamId($this->eid)) {
                $this->error('You have no privilege of this exam~');
            }
        } else if (isset($_POST['eid'])) {
            $this->eid = intval($_POST['eid']);
        } else {
            $this->error('No Such Exam!');
        }
    }

    public function add() {
        $type = I('get.type', 1, 'intval');
        switch ($type) {
            case 1:
                $this->addchoose();
                break;
            case 2:
                $this->addjudge();
                break;
            case 3:
                $this->addfill();
                break;
            case 4:
                $this->addprogram();
                break;
            default:
                $this->error('Invaild Path');
                break;
        }
    }

    private function addchoose() {

        $sch = getproblemsearch();
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_choose', $sch['sql']);
        $numofchoose = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_choose')->field('choose_id,question,creator,point,easycount')
            ->where($sch['sql'])->order('choose_id asc')->limit($mypage['sqladd'])
            ->select();

        $haveadded = array();
        if ($row) {
            foreach ($row as $value) {
                $haveadded[$value['choose_id']] = $this->checkQuestionHasAdded($this->eid, 1, $value['choose_id']);
            }
        }
        $this->zadd('row', $row);
        $this->zadd('added', $haveadded);
        $this->zadd('mypage', $mypage);
        $this->zadd('numofchoose', $numofchoose);
        $this->zadd('isadmin', $isadmin);
        $this->zadd('search', $sch['search']);
        $this->zadd('problem', $sch['problem']);

        $this->auto_display('choose');
    }

    private function addjudge() {
        $sch = getproblemsearch();
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_judge', $sch['sql']);
        $numofjudge = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = m('ex_judge')->field('judge_id,question,creator,point,easycount')
            ->where($sch['sql'])->order('judge_id asc')->limit($mypage['sqladd'])
            ->select();
        $haveadded = array();
        if ($row) {
            foreach ($row as $value) {
                $haveadded[$value['judge_id']] = $this->checkQuestionHasAdded($this->eid, 2, $value['judge_id']);
            }
        }
        $this->zadd('row', $row);
        $this->zadd('added', $haveadded);
        $this->zadd('numofjudge', $numofjudge);
        $this->zadd('isadmin', $isadmin);
        $this->zadd('mypage', $mypage);
        $this->zadd('search', $sch['search']);
        $this->zadd('problem', $sch['problem']);
        $this->auto_display('judge');
    }

    private function addfill() {
        $sch = getproblemsearch();
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_fill', $sch['sql']);
        $numoffill = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_fill')->field('fill_id,question,creator,point,easycount,kind')
            ->where($sch['sql'])->order('fill_id asc')->limit($mypage['sqladd'])
            ->select();
        $haveadded = array();
        if ($row) {
            foreach ($row as $value) {
                $haveadded[$value['fill_id']] = $this->checkQuestionHasAdded($this->eid, 3, $value['fill_id']);
            }
        }
        $this->zadd('added', $haveadded);
        $this->zadd('mypage', $mypage);
        $this->zadd('numoffill', $numoffill);
        $this->zadd('isadmin', $isadmin);
        $this->zadd('row', $row);
        $this->zadd('search', $sch['search']);
        $this->zadd('problem', $sch['problem']);
        $this->auto_display('fill');
    }

    public function addprogram() {
        if (IS_POST && I('post.eid')) {
            if (!check_post_key()) {
                $this->error('发生错误！');
            } else if (!$this->isCreator()) {
                $this->error('You have no privilege of this exam');
            } else {
                $eid = I('post.eid', 0, 'intval');
                $flag = ProblemServiceModel::instance()->addProgram2Exam($eid);
                if ($flag === true) {
                    $this->success('程序题添加成功', U('Teacher/Problem/addprogram', array('eid' => $eid, 'type' => 4)), 2);
                } else {
                    $this->error('Invaild Path');
                }
            }
        } else {
            $ansrow = M('exp_question')->field('question_id')
                ->where('exam_id=%d and type=4', $this->eid)->order('question_id')
                ->select();
            $answernumC = M('exp_question')->where('exam_id=%d and type=4', $this->eid)
                ->count();
            $key = set_post_key();
            $this->zadd('mykey', $key);
            $this->zadd('answernumC', $answernumC);
            $this->zadd('ansrow', $ansrow);
            $this->auto_display('program');
        }
    }

    public function addpte() {
        if (isset($_POST['eid']) && isset($_POST['id']) && isset($_POST['type']) && isset($_POST['sid'])) {
            $eid = intval($_POST['eid']);
            $quesid = intval($_POST['id']);
            $typeid = intval($_POST['type']);
            if ($this->isOwner4ExamByExamId($eid) && $eid > 0 && $quesid > 0 && $typeid >= 1 && $typeid <= 3) {
                $arr['type'] = $typeid;
                $arr['exam_id'] = $eid;
                $arr['question_id'] = $quesid;
                if (M('exp_question')->add($arr)) {
                    echo "已添加";
                } else {
                    echo "添加失败";
                }
            } else {
                echo "No Privilege";
            }
        } else {
            echo "Invaild path";
        }
    }

    public function delpte() {
        if (isset($_POST['eid']) && isset($_POST['id']) && isset($_POST['type']) && isset($_POST['sid'])) {
            $eid = intval($_POST['eid']);
            $quesid = intval($_POST['id']);
            $typeid = intval($_POST['type']);
            if ($this->isOwner4ExamByExamId($eid) && $eid > 0 && $quesid > 0 && $typeid >= 1 && $typeid <= 3) {
                $arr['type'] = $typeid;
                $arr['exam_id'] = $eid;
                $arr['question_id'] = $quesid;
                if (M('exp_question')->where($arr)->delete()) {
                    echo "ok";
                } else {
                    echo "删除错误";
                }
            } else {
                echo "No Privilege";
            }
        } else {
            echo "Invaild path";
        }
    }
}
