<?php
namespace Teacher\Controller;

use Teacher\Model\AdminchooseModel;
use Teacher\Model\AdminexamModel;
use Teacher\Model\AdminfillModel;
use Teacher\Model\AdminjudgeModel;
use Teacher\Model\ExamModel;
use Think\Controller;

class AddController extends TemplateController
{

    private $typename_ch = array('选择题', '判断题', '填空题', '考试');
    private $typename_en = array('choose', 'judge', 'fill', 'index');

    public function _initialize() {
        parent::_initialize();
    }

    public function exam() {
        if (IS_POST) {
            if (!check_post_key())
                $this->error('发生错误！');
            if (!$this->isCreator()) {
                $this->error('You have no privilege!');
            }
            if (isset($_POST['examid'])) {
                $flag = AdminexamModel::instance()->upd_exam();
                $this->checkflag($flag, 3);
            } else if (isset($_POST['examname'])) {
                $flag = AdminexamModel::instance()->add_exam();
                $this->checkflag($flag, 3);
            }
        } else if (IS_GET && I('get.eid') != '') {
            $eid = I('get.eid', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $row = M('exam')->where("exam_id=%d and visible='Y'", $eid)->find();
            if ($row) {
                if (!$this->isOwnerByUserId($row['creator'])) {
                    $this->error('You have no privilege!');
                }
                $this->zadd('page', $page);
                $this->zadd('row', $row);
                $this->zadd('mykey', $key);
                $this->auto_display();
            } else {
                $this->error('No Such Exam!');
            }
        } else {
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->auto_display();
        }
    }

    public function choose() {
        if (IS_POST) {
            if (!check_post_key())
                $this->error('发生错误！');
            if (isset($_POST['chooseid'])) {
                $flag = AdminchooseModel::instance()->upd_question();
                $this->checkflag($flag, 0);
            } else if (isset($_POST['choose_des'])) {
                $flag = AdminchooseModel::instance()->add_question();
                $this->checkflag($flag, 0);
            }
        } else if (IS_GET && I('get.id') != '') {
            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $pnt = M('ex_point')->select();
            $key = set_post_key();
            $row = M('ex_choose')
                ->field('choose_id,question,ams,bms,cms,dms,answer,creator,point,easycount,isprivate')
                ->where('choose_id=%d', $id)->find();
            if ($row) {
                if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                    $this->error('You have no privilege!');
                }
                $this->zadd('page', $page);
                $this->zadd('row', $row);
                $this->zadd('mykey', $key);
                $this->zadd('problemType', $problemType);
                $this->zadd('pnt', $pnt);
                $this->auto_display();
            } else {
                $this->error('No Such Problem!');
            }
        } else {
            $pnt = M('ex_point')->select();
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->zadd('problemType', $problemType);
            $this->auto_display();
        }
    }

    public function judge() {
        if (IS_POST) {
            if (!check_post_key()) {
                $this->error('发生错误！');
            }
            if (isset($_POST['judgeid'])) {
                $flag = AdminjudgeModel::instance()->upd_question();
                $this->checkflag($flag, 1);
            } else if (isset($_POST['judge_des'])) {
                $flag = AdminjudgeModel::instance()->add_question();
                $this->checkflag($flag, 1);
            }
        } else if (IS_GET && I('get.id') != '') {
            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $pnt = M('ex_point')->select();
            $key = set_post_key();
            $row = M('ex_judge')->field('judge_id,question,answer,creator,point,easycount,isprivate')
                ->where('judge_id=%d', $id)->find();
            if ($row) {
                if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                    $this->error('You have no privilege!');
                }
            } else {
                $this->error('No Such Problem!');
            }
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->zadd('problemType', $problemType);
            $this->auto_display();
        } else {
            $page = I('get.page', 1, 'intval');
            $pnt = M('ex_point')->select();
            $problemType = I('get.problem', 0, 'intval');
            $key = set_post_key();
            $this->zadd('page', $page);
            $this->zadd('problemType', $problemType);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->auto_display();
        }
    }

    public function fill() {
        if (IS_POST) {
            if (!check_post_key()) {
                $this->error('发生错误！');
            }
            if (isset($_POST['fillid'])) {
                $flag = AdminfillModel::instance()->upd_question();
                $this->checkflag($flag, 2);
            } else if (isset($_POST['fill_des'])) {
                $flag = AdminfillModel::instance()->add_question();
                $this->checkflag($flag, 2);
            }
        } else if (IS_GET && I('get.id') != '') {
            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $pnt = M('ex_point')->select();
            $key = set_post_key();
            $row = M('ex_fill')
                ->field('fill_id,question,answernum,creator,point,easycount,kind,isprivate')
                ->where('fill_id=%d', $id)->find();
            if ($row) {
                if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                    $this->error('You have no privilege!');
                }
                if ($row['answernum'] != 0) {
                    $ansrow = M('fill_answer')->field('answer_id,answer')
                        ->where('fill_id=%d', $id)->order('answer_id')->select();
                    $this->zadd('ansrow', $ansrow);
                }
                $this->zadd('page', $page);
                $this->zadd('row', $row);
                $this->zadd('mykey', $key);
                $this->zadd('pnt', $pnt);
                $this->zadd('problemType', $problemType);
                $this->auto_display();
            } else {
                $this->error('No Such Problem!');
            }
        } else {
            $page = I('get.page', 1, 'intval');
            $pnt = M('ex_point')->select();
            $key = set_post_key();
            $problemType = I('get.problem', 0, 'intval');
            $this->zadd('page', $page);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->zadd('problemType', $problemType);
            $this->auto_display();
        }
    }

    public function point() {
        $action = I('post.action', '', 'htmlspecialchars');
        if ($action == 'add') {
            $data['point'] = I('post.point', '');
            $id = M('ex_point')->data($data)->add();
            $data['id'] = $id;
            $this->ajaxReturn(json_encode($data));
        } else if ($action == 'del') {
            $id = I('post.id', 0, 'intval');
            M('ex_point')->delete($id);
            echo "ok";
        }
    }

    public function copyOneExam() {
        $eid = I('get.eid', 0, 'intval');
        $row = M('exam')->where("exam_id=%d and visible='Y'", $eid)->find();
        if (!empty($row)) {
            if (!$this->isOwnerByUserId($row['creator'])) {
                $this->error('You have no privilege!');
            } else {
                // copy exam's base info
                unset($row['exam_id']);
                $row['creator'] = $this->userInfo['user_id'];
                dbg($row);
                $examId = ExamModel::instance()->addExamBaseInfo($row);
                // copy exam's problem
                $exQDao = M('exp_question');
                $res = $exQDao->field('exam_id,question_id,type')->where('exam_id=%d', $eid)->select();
                foreach ($res as &$r) {
                    $r['exam_id'] = $examId;
                }
                unset($r);
                $exQDao->addAll($res);
                $this->success('考试复制成功!', U('/Teacher'), 1);
            }
        }
    }

    private function checkProblemPrivate($private, $crt) {
        if ($private == 2 && !$this->isSuperAdmin()) {
            return -1;
        }
        if (!$this->isSuperAdmin()) {
            if ($private == 1 && $crt != $this->userInfo['user_id']) {
                return -1;
            }
        }
        return 1;
    }

    private function checkflag($flag, $type, $second = 1) {
        $typech = $this->typename_ch[$type];
        $typeen = $this->typename_en[$type];
        if (is_bool($flag)) {
            if ($flag === true) {
                $page = I('post.page', 1, 'intval');
                $problem = I('post.problem',0,'intval');
                $this->success("$typech 添加成功!", U("Teacher/Index/$typeen", array('page' => $page,'problem' => $problem)), $second);
            } else {
                $this->error("$typech 添加失败！");
            }
        } else {
            if ($flag === -1) {
                $this->error('You have no privilege to modify it!');
            } else if ($flag === -2) {
                $this->error("$typech 修改失败！");
            } else if ($flag === 1) {
                $page = I('post.page', 1, 'intval');
                $problem = I('post.problem',0,'intval');
                $this->success("$typech 修改成功!", U("Teacher/Index/$typeen", array('page' => $page,'problem'=> $problem)), $second);
            }
        }
    }
}
