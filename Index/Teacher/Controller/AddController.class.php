<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ChooseServiceModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\FillServiceModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\JudgeServiceModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\QuestionBaseModel;

class AddController extends TemplateController
{

    private $typename_ch = array('选择题', '判断题', '填空题', '考试');
    private $typename_en = array('choose', 'judge', 'fill', 'index');

    public function _initialize() {
        parent::_initialize();
    }

    public function exam() {
        if (IS_POST) {
            if (!check_post_key()) {
                $this->echoError('发生错误！');
            }
            if (!$this->isCreator()) {
                $this->echoError('You have no privilege!');
            }
            if (isset($_POST['examid'])) {
                $flag = ExamServiceModel::instance()->updateExamInfo();
                $this->flagChecked($flag, 3);
            } else if (isset($_POST['examname'])) {
                $flag = ExamServiceModel::instance()->addExamInfo();
                $this->flagChecked($flag, 3);
            }
        } else if (IS_GET && I('get.eid') != '') {
            $eid = I('get.eid', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $key = set_post_key();
            $row = ExamBaseModel::instance()->getExamInfoById($eid);
            if (empty($row)) {
                $this->echoError('No Such Exam!');
            }
            if (!$this->isOwner4ExamByUserId($row['creator'])) {
                $this->echoError('You have no privilege!');
            }
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->auto_display();
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
            if (!check_post_key()) {
                $this->echoError('发生错误！');
            }
            if (isset($_POST['chooseid'])) {
                $flag = ChooseServiceModel::instance()->updateChooseInfo();
                $this->flagChecked($flag, 0);
            } else if (isset($_POST['choose_des'])) {
                $flag = ChooseServiceModel::instance()->addChooseInfo();
                $this->flagChecked($flag, 0);
            }
        } else if (IS_GET && I('get.id') != '') {
            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $pnt = M('ex_point')->select();
            $key = set_post_key();
            $row = ChooseBaseModel::instance()->getChooseById($id);
            if (empty($row)) {
                $this->error('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                $this->echoError('You have no privilege!');
            }
            $row['point'] = explode(",", $row['point']);
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->zadd('problemType', $problemType);
            $this->zadd('pnt', $pnt);
            $this->auto_display();
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
                $this->echoError('发生错误！');
            }
            if (isset($_POST['judgeid'])) {
                $flag = JudgeServiceModel::instance()->updateJudgeInfo();
                $this->flagChecked($flag, 1);
            } else if (isset($_POST['judge_des'])) {
                $flag = JudgeServiceModel::instance()->addJudgeInfo();
                $this->flagChecked($flag, 1);
            }
        } else if (IS_GET && I('get.id') != '') {

            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $pnt = M('ex_point')->select();
            $key = set_post_key();
            $row = JudgeBaseModel::instance()->getJudgeById($id);
            if (empty($row)) {
                $this->echoError('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                $this->echoError('You have no privilege!');
            }
            $row['point'] = explode(",", $row['point']);
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
                $this->echoError('发生错误！');
            }
            if (isset($_POST['fillid'])) {
                $flag = FillServiceModel::instance()->updateFillInfo();
                $this->flagChecked($flag, 2);
            } else if (isset($_POST['fill_des'])) {
                $flag = FillServiceModel::instance()->addFillInfo();
                $this->flagChecked($flag, 2);
            }
        } else if (IS_GET && I('get.id') != '') {
            $id = I('get.id', 0, 'intval');
            $page = I('get.page', 1, 'intval');
            $problemType = I('get.problem', 0, 'intval');
            $pnt = M('ex_point')->select();
            $key = set_post_key();
            $row = FillBaseModel::instance()->getFillById($id);
            if (empty($row)) {
                $this->echoError('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                $this->echoError('You have no privilege!');
            }
            if ($row['answernum'] != 0) {
                $ansrow = FillBaseModel::instance()->getFillAnswerByFillId($id);
                $this->zadd('ansrow', $ansrow);
            }
            $row['point'] = explode(",", $row['point']);
            $this->zadd('page', $page);
            $this->zadd('row', $row);
            $this->zadd('mykey', $key);
            $this->zadd('pnt', $pnt);
            $this->zadd('problemType', $problemType);
            $this->auto_display();
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
        $row = ExamBaseModel::instance()->getExamInfoById($eid);
        if (empty($row)) {
            $this->echoError("No Such Exam!");
        }
        if (!$this->isOwner4ExamByUserId($row['creator'])) {
            $this->echoError('You have no privilege!');
        } else {
            // copy exam's base info
            unset($row['exam_id']);
            $row['creator'] = $this->userInfo['user_id'];
            $examId = ExamBaseModel::instance()->addExamBaseInfo($row);
            // copy exam's problem
            $field = array('exam_id', 'question_id', 'type');
            $res = QuestionBaseModel::instance()->getQuestionByExamId($eid, $field);
            foreach ($res as &$r) {
                $r['exam_id'] = $examId;
            }
            unset($r);
            QuestionBaseModel::instance()->insertQuestions($res);
            $this->success('考试复制成功!', U('/Teacher'), 1);
        }
    }

    private function flagChecked($flag, $type, $second = 1) {
        $typech = $this->typename_ch[$type];
        $typeen = $this->typename_en[$type];
        if (is_bool($flag)) {
            if ($flag === true) {
                $page = I('post.page', 1, 'intval');
                $problem = I('post.problem', 0, 'intval');
                $this->success("$typech 添加成功!", U("Teacher/Index/$typeen", array('page' => $page, 'problem' => $problem)), $second);
            } else {
                $this->echoError("$typech 添加失败！");
            }
        } else {
            if ($flag === -1) {
                $this->echoError('You have no privilege to modify it!');
            } else if ($flag === -2) {
                $this->echoError("$typech 修改失败！");
            } else if ($flag === 1) {
                $page = I('post.page', 1, 'intval');
                $problem = I('post.problem', 0, 'intval');
                $this->success("$typech 修改成功!", U("Teacher/Index/$typeen", array('page' => $page, 'problem' => $problem)), $second);
            }
        }
    }
}
