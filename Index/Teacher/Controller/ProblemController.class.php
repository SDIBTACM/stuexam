<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\QuestionBaseModel;

use Teacher\Service\ProblemService;

use Think\Controller;

class ProblemController extends TemplateController
{

    private $eid = null;

    public function _initialize() {

        parent::_initialize();

        if (isset($_GET['eid']) && isset($_GET['type'])) {
            $this->eid = I('get.eid', 0, 'intval');
            $problemType = I('get.type', 0, 'intval');
            $widgets = array(
                'eid' => $this->eid,
                'type' => $problemType
            );
            if (!$this->isOwner4ExamByExamId($this->eid)) {
                $this->echoError('You have no privilege of this exam~');
            } else {
                $this->ZaddWidgets($widgets);
            }
        } else if (isset($_POST['eid'])) {
            $this->eid = I('post.eid', 0, 'intval');
        } else {
            $this->echoError('No Such Exam!');
        }
    }

    public function add() {
        $problemType = I('get.type', 1, 'intval');
        switch ($problemType) {
            case ChooseBaseModel::CHOOSE_PROBLEM_TYPE:
                $this->addChooseProblem();
                break;
            case JudgeBaseModel::JUDGE_PROBLEM_TYPE:
                $this->addJudgeProblem();
                break;
            case FillBaseModel::FILL_PROBLEM_TYPE:
                $this->addFillProblem();
                break;
            case ProblemService::PROGRAM_PROBLEM_TYPE:
                $this->addProgramProblem();
                break;
            default:
                $this->echoError('Invaild Path');
                break;
        }
    }

    private function addChooseProblem() {

        $sch = getproblemsearch('choose_id', ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_choose', $sch['sql']);
        $numofchoose = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_choose')->field('choose_id,question,creator,easycount')
            ->where($sch['sql'])->order('choose_id asc')->limit($mypage['sqladd'])
            ->select();

        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $haveadded = array();
        foreach ($questionAddedIds as $qid) {
            $haveadded[$qid['question_id']] = 1;
        }

        $widgets = array(
            'row' => $row,
            'added' => $haveadded,
            'mypage' => $mypage,
            'isadmin' => $isadmin,
            'problem' => $sch['problem'],
            'numofchoose' => $numofchoose
        );
        $this->ZaddWidgets($widgets);
        $this->auto_display('choose');
    }

    private function addJudgeProblem() {
        $sch = getproblemsearch('judge_id', JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_judge', $sch['sql']);
        $numofjudge = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = m('ex_judge')->field('judge_id,question,creator,easycount')
            ->where($sch['sql'])->order('judge_id asc')->limit($mypage['sqladd'])
            ->select();

        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $haveadded = array();
        foreach ($questionAddedIds as $qid) {
            $haveadded[$qid['question_id']] = 1;
        }

        $widgets = array(
            'row' => $row,
            'added' => $haveadded,
            'mypage' => $mypage,
            'isadmin' => $isadmin,
            'problem' => $sch['problem'],
            'numofjudge' => $numofjudge
        );
        $this->ZaddWidgets($widgets);
        $this->auto_display('judge');
    }

    private function addFillProblem() {
        $sch = getproblemsearch('fill_id', FillBaseModel::FILL_PROBLEM_TYPE);
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_fill', $sch['sql']);
        $numoffill = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_fill')->field('fill_id,question,creator,easycount,kind')
            ->where($sch['sql'])->order('fill_id asc')->limit($mypage['sqladd'])
            ->select();

        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, FillBaseModel::FILL_PROBLEM_TYPE);
        $haveadded = array();
        foreach ($questionAddedIds as $qid) {
            $haveadded[$qid['question_id']] = 1;
        }

        $widgets = array(
            'row' => $row,
            'added' => $haveadded,
            'mypage' => $mypage,
            'isadmin' => $isadmin,
            'problem' => $sch['problem'],
            'numoffill' => $numoffill
        );
        $this->ZaddWidgets($widgets);
        $this->auto_display('fill');
    }

    public function addProgramProblem() {
        if (IS_POST && I('post.eid')) {
            if (!check_post_key()) {
                $this->echoError('发生错误！');
            } else if (!$this->isCreator()) {
                $this->echoError('You have no privilege of this exam');
            } else {
                $eid = I('post.eid', 0, 'intval');
                $flag = ProblemService::instance()->addProgram2Exam($eid);
                if ($flag === true) {
                    $this->success('程序题添加成功', U('Teacher/Problem/addProgramProblem', array('eid' => $eid, 'type' => 4)), 2);
                } else {
                    $this->echoError('Invaild Path');
                }
            }
        } else {
            $ansrow = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, ProblemService::PROGRAM_PROBLEM_TYPE);
            $answernumC = count($ansrow);
            $key = set_post_key();
            $widgets = array(
                'mykey' => $key,
                'ansrow' => $ansrow,
                'answernumC' => $answernumC
            );
            $this->ZaddWidgets($widgets);
            $this->auto_display('program');
        }
    }

    public function addpte() {
        if (!(isset($_POST['eid']) && isset($_POST['id']) && isset($_POST['type']) && isset($_POST['sid']))) {
            $this->echoError("Invaild path");
            return;
        }
        $eid = intval($_POST['eid']);
        $quesid = intval($_POST['id']);
        $typeid = intval($_POST['type']);
        if ($this->isOwner4ExamByExamId($eid) &&
            $eid > 0 && $quesid > 0 &&
            in_array($typeid, array(ChooseBaseModel::CHOOSE_PROBLEM_TYPE, JudgeBaseModel::JUDGE_PROBLEM_TYPE, FillBaseModel::FILL_PROBLEM_TYPE))
        ) {
            $data = array(
                'exam_id' => $eid,
                'question_id' => $quesid,
                'type' => $typeid
            );
            if (M('exp_question')->add($data)) {
                $this->echoError("已添加");
            } else {
                $this->echoError("添加失败");
            }
        } else {
            $this->echoError("No Privilege!");
        }
    }

    public function delpte() {
        if (!(isset($_POST['eid']) && isset($_POST['id']) && isset($_POST['type']) && isset($_POST['sid']))) {
            $this->echoError("Invaild path");
            return;
        }
        $eid = intval($_POST['eid']);
        $quesid = intval($_POST['id']);
        $typeid = intval($_POST['type']);
        if ($this->isOwner4ExamByExamId($eid) &&
            $eid > 0 && $quesid > 0 &&
            in_array($typeid, array(ChooseBaseModel::CHOOSE_PROBLEM_TYPE, JudgeBaseModel::JUDGE_PROBLEM_TYPE, FillBaseModel::FILL_PROBLEM_TYPE))
        ) {
            $data = array(
                'exam_id' => $eid,
                'question_id' => $quesid,
                'type' => $typeid
            );
            if (M('exp_question')->where($data)->delete()) {
                $this->echoError("ok");
            } else {
                $this->echoError("删除错误");
            }
        } else {
            $this->echoError("No Privilege!");
        }
    }
}
