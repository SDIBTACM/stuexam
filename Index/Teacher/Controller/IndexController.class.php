<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Think\Controller;

class IndexController extends QuestionBaseController
{
    public function _initialize() {
        parent::_initialize();
        if (strcmp($this->action, "index")) {
            $this->ZaddChapters();
            $this->buildSearch();
        }
    }

    public function index() {
        $sql = getexamsearch($this->userInfo['user_id']);
        $key = set_get_key();
        $mypage = splitpage('exam', $sql);
        $creator = I('get.creator', '', 'htmlspecialchars');
        if (empty($creator)) {
            $extraQuery = "";
        } else {
            $extraQuery = "creator=$creator";
        }
        $row = M('exam')
            ->field('exam_id,title,start_time,end_time,creator')
            ->where($sql)
            ->order('exam_id desc')
            ->limit($mypage['sqladd'])
            ->select();
        $this->zadd('row', $row);
        $this->zadd('mypage', $mypage);
        $this->zadd('mykey', $key);
        $this->zadd('teacherList', C('TEACHER_LIST'));
        $this->zadd("creator", $creator);
        $this->zadd("extraQuery", $extraQuery);
        $this->auto_display();
    }

    public function choose() {
        $sch = getproblemsearch('choose_id', ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $key = set_get_key();
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_choose', $sch['sql']);
        $numofchoose = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_choose')
            ->field('choose_id,question,creator,easycount')
            ->where($sch['sql'])
            ->order('choose_id asc')
            ->limit($mypage['sqladd'])
            ->select();
        $widgets = array(
            'row' => $row,
            'mykey' => $key,
            'mypage' => $mypage,
            'isadmin' => $isadmin,
            'numofchoose' => $numofchoose,
        );

        $questionIds = array();
        foreach($row as $r) {
            $questionIds[] = $r['choose_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);

        $this->ZaddWidgets($widgets);
        $this->auto_display();
    }

    public function judge() {

        $sch = getproblemsearch('judge_id', JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $key = set_get_key();
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_judge', $sch['sql']);
        $numofjudge = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_judge')
            ->field('judge_id,question,creator,easycount')
            ->where($sch['sql'])
            ->order('judge_id asc')
            ->limit($mypage['sqladd'])
            ->select();
        $widgets = array(
            'row' => $row,
            'mykey' => $key,
            'mypage' => $mypage,
            'isadmin' => $isadmin,
            'numofjudge' => $numofjudge,
        );

        $questionIds = array();
        foreach($row as $r) {
            $questionIds[] = $r['judge_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, JudgeBaseModel::JUDGE_PROBLEM_TYPE);

        $this->ZaddWidgets($widgets);
        $this->auto_display();
    }

    public function fill() {
        $sch = getproblemsearch('fill_id', FillBaseModel::FILL_PROBLEM_TYPE);
        $key = set_get_key();
        $isadmin = $this->isSuperAdmin();
        $mypage = splitpage('ex_fill', $sch['sql']);
        $numoffill = 1 + ($mypage['page'] - 1) * $mypage['eachpage'];
        $row = M('ex_fill')
            ->field('fill_id,question,creator,easycount,kind')
            ->where($sch['sql'])
            ->order('fill_id asc')
            ->limit($mypage['sqladd'])
            ->select();
        $widgets = array(
            'row' => $row,
            'mykey' => $key,
            'mypage' => $mypage,
            'isadmin' => $isadmin,
            'numoffill' => $numoffill,
        );

        $questionIds = array();
        foreach($row as $r) {
            $questionIds[] = $r['fill_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, FillBaseModel::FILL_PROBLEM_TYPE);

        $this->ZaddWidgets($widgets);
        $this->auto_display();
    }
}
