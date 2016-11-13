<?php
namespace Teacher\Controller;

use Constant\Constants\Chapter;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Think\Controller;

class IndexController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
        $chapters = Chapter::getConstant();
        $chapterMap = array();
        foreach ($chapters as $chapter) {
            if ($chapter instanceof Chapter) {
                $chapterMap[$chapter->getId()] = $chapter->getName();
            }
        }
        $this->zadd('chapters', $chapterMap);
    }

    public function index() {
        $sql = getexamsearch($this->userInfo['user_id']);
        $key = set_get_key();
        $mypage = splitpage('exam', $sql);
        $row = M('exam')
            ->field('exam_id,title,start_time,end_time,creator')
            ->where($sql)
            ->order('exam_id desc')
            ->limit($mypage['sqladd'])
            ->select();
        $this->zadd('row', $row);
        $this->zadd('mypage', $mypage);
        $this->zadd('mykey', $key);
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
            'problem' => $sch['problem'],
            'numofchoose' => $numofchoose,
        );
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
            'problem' => $sch['problem'],
            'numofjudge' => $numofjudge,
        );
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
            'problem' => $sch['problem'],
            'numoffill' => $numoffill,
        );
        $this->ZaddWidgets($widgets);
        $this->auto_display();
    }
}
