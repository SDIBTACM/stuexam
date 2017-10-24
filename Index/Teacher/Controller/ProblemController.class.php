<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\QuestionBaseModel;

use Teacher\Service\ProblemService;

use Think\Controller;

class ProblemController extends QuestionBaseController
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
            if (!strcmp($this->action, 'add')) {
                $this->buildSearch();
                $this->ZaddChapters();
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
        $isAdmin = $this->isSuperAdmin();
        $myPage = splitpage('ex_choose', $sch['sql']);
        $numOfChoose = 1 + ($myPage['page'] - 1) * $myPage['eachpage'];
        $row = M('ex_choose')->field('choose_id,question,creator,easycount')
            ->where($sch['sql'])->order('choose_id asc')->limit($myPage['sqladd'])
            ->select();

        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $haveAdded = array();
        foreach ($questionAddedIds as $qid) {
            $haveAdded[$qid['question_id']] = 1;
        }

        $widgets = array(
            'row' => $row,
            'added' => $haveAdded,
            'mypage' => $myPage,
            'isadmin' => $isAdmin,
            'numofchoose' => $numOfChoose
        );

        $questionIds = array();
        foreach($row as $r) {
            $questionIds[] = $r['choose_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);

        $this->ZaddWidgets($widgets);
        $this->auto_display('choose');
    }

    private function addJudgeProblem() {
        $sch = getproblemsearch('judge_id', JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $isAdmin = $this->isSuperAdmin();
        $myPage = splitpage('ex_judge', $sch['sql']);
        $numOfJudge = 1 + ($myPage['page'] - 1) * $myPage['eachpage'];
        $row = m('ex_judge')->field('judge_id,question,creator,easycount')
            ->where($sch['sql'])->order('judge_id asc')->limit($myPage['sqladd'])
            ->select();

        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $haveAdded = array();
        foreach ($questionAddedIds as $qid) {
            $haveAdded[$qid['question_id']] = 1;
        }

        $widgets = array(
            'row' => $row,
            'added' => $haveAdded,
            'mypage' => $myPage,
            'isadmin' => $isAdmin,
            'numofjudge' => $numOfJudge
        );

        $questionIds = array();
        foreach($row as $r) {
            $questionIds[] = $r['judge_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, JudgeBaseModel::JUDGE_PROBLEM_TYPE);

        $this->ZaddWidgets($widgets);
        $this->auto_display('judge');
    }

    private function addFillProblem() {
        $sch = getproblemsearch('fill_id', FillBaseModel::FILL_PROBLEM_TYPE);
        $isAdmin = $this->isSuperAdmin();
        $myPage = splitpage('ex_fill', $sch['sql']);
        $numOfFill = 1 + ($myPage['page'] - 1) * $myPage['eachpage'];
        $row = M('ex_fill')->field('fill_id,question,creator,easycount,kind')
            ->where($sch['sql'])->order('fill_id asc')->limit($myPage['sqladd'])
            ->select();

        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, FillBaseModel::FILL_PROBLEM_TYPE);
        $haveAdded = array();
        foreach ($questionAddedIds as $qid) {
            $haveAdded[$qid['question_id']] = 1;
        }

        $widgets = array(
            'row' => $row,
            'added' => $haveAdded,
            'mypage' => $myPage,
            'isadmin' => $isAdmin,
            'numoffill' => $numOfFill
        );

        $questionIds = array();
        foreach($row as $r) {
            $questionIds[] = $r['fill_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, FillBaseModel::FILL_PROBLEM_TYPE);

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
                $ansNumber = I('post.numanswer', 0, 'intval');

                $problemIds = array();
                for ($i = 1; $i <= $ansNumber; $i++) {
                    $programId = test_input($_POST["answer$i"]);
                    if (!is_numeric($programId)) {
                        continue;
                    } else {
                        $problemIds[] = intval($programId);
                    }
                }
                if ($ansNumber == 0) {
                    $pList = "0";
                } else {
                    $pList = implode(',', $problemIds);
                }
                $sql = "select defunct, author, problem_id from problem where problem_id in ($pList)";

                $res = M()->query($sql);
                $validProblemCnt = 0;

                foreach ($res as $r) {
                    if ($r['defunct'] == 'N') {
                        $validProblemCnt++;
                    } else {
                        if (!strcmp($r['author'], $this->userInfo['user_id']) || $this->isSuperAdmin()) {
                            $validProblemCnt++;
                        }
                    }
                }
                if ($validProblemCnt != $ansNumber) {
                    $this->echoError('其中一些题目您没有权限添加哦~');
                }
                $flag = ProblemService::instance()->addProgram2Exam($eid, $problemIds);
                if ($flag === true) {
                    $this->success('程序题添加成功', U('Teacher/Problem/addProgramProblem', array('eid' => $eid, 'type' => 4)), 2);
                } else {
                    $this->echoError('Invaild Path');
                }
            }
        } else {
            $ansRow = QuestionBaseModel::instance()->getQuestionIds4ExamByType($this->eid, ProblemService::PROGRAM_PROBLEM_TYPE);
            $answerNumC = count($ansRow);
            $key = set_post_key();
            $widgets = array(
                'mykey' => $key,
                'ansrow' => $ansRow,
                'answernumC' => $answerNumC
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
        $questionId = intval($_POST['id']);
        $typeId = intval($_POST['type']);
        if ($this->isOwner4ExamByExamId($eid) &&
            $eid > 0 && $questionId > 0 &&
            in_array($typeId, array(ChooseBaseModel::CHOOSE_PROBLEM_TYPE, JudgeBaseModel::JUDGE_PROBLEM_TYPE, FillBaseModel::FILL_PROBLEM_TYPE))
        ) {
            $data = array(
                'exam_id' => $eid,
                'question_id' => $questionId,
                'type' => $typeId
            );
            if (M('exp_question')->add($data)) {
                $this->echoError("ok");
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
        $questionId = intval($_POST['id']);
        $typeId = intval($_POST['type']);
        if ($this->isOwner4ExamByExamId($eid) &&
            $eid > 0 && $questionId > 0 &&
            in_array($typeId, array(ChooseBaseModel::CHOOSE_PROBLEM_TYPE, JudgeBaseModel::JUDGE_PROBLEM_TYPE, FillBaseModel::FILL_PROBLEM_TYPE))
        ) {
            $data = array(
                'exam_id' => $eid,
                'question_id' => $questionId,
                'type' => $typeId
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
