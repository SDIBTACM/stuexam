<?php
namespace Home\Controller;

use Home\Model\ExamadminModel;

use Teacher\Model\ExamBaseModel;

class IndexController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        if (!$this->isSuperAdmin()) {
            if ($this->isCreator()) {
                $userId = $this->userInfo['user_id'];
                $where = "`visible`='Y' AND (`isprivate`=0 or `creator` like '$userId')";
            } else {
                $where = array(
                    'user_id' => $this->userInfo['user_id']
                );
                $fields = array('rightstr');
                $privileges = M('ex_privilege')->field($fields)->where($where)->select();
                $examIds = array(0);
                foreach ($privileges as $privilege) {
                    $rightstr = $privilege['rightstr'];
                    $examIds[] = intval(substr($rightstr, 1));
                }
                $where = array(
                    'visible' => 'Y',
                    'exam_id' => array('in', $examIds),
                );
            }
        } else {
            $where = array('visible' => 'Y');
        }

        $mypage = splitpage('exam', $where);

        $where['order'] = array('exam_id desc');
        $where['limit'] = $mypage['sqladd'];
        $field = array('exam_id', 'title', 'start_time', 'end_time');
        $row = ExamBaseModel::instance()->queryData($where, $field);

        $this->zadd('row', $row);
        $this->zadd('mypage', $mypage);
        $this->auto_display();
    }

    public function score() {
        $user_id = $this->userInfo['user_id'];
        $row = M('users')->field('nick,email,reg_time')
            ->where("user_id='%s'", $user_id)->find();
        $query = "SELECT `title`,`exam`.`exam_id`,`score`,`choosesum`,`judgesum`,`fillsum`,`programsum` " .
            "FROM `exam`,`ex_student` WHERE `ex_student`.`user_id`='" . $user_id . "' AND `exam`.`visible`='Y' " .
            "AND `ex_student`.`exam_id`=`exam`.`exam_id` AND score >= 0 ORDER BY `exam`.`exam_id` DESC";
        $score = M()->query($query);
        $this->zadd('score', $score);
        $this->zadd('row', $row);
        $this->auto_display();
    }

    public function about() {
        $eid = I('get.eid', 0, 'intval');
        if (empty($eid)) {
            $this->echoError('No Such Exam');
            return;
        }
        $user_id = $this->userInfo['user_id'];
        $row = ExamadminModel::instance()->checkExamPrivilege($eid, $user_id);
        if (!is_array($row)) {
            if ($row == 0) {
                $this->echoError('You have no privilege!');
            } else if ($row == -1) {
                $this->echoError('No Such Exam');
            } else if ($row == -2) {
                $this->echoError('Do not login in diff machine,Please Contact administrator');
            }
        }
        $isruning = ExamadminModel::instance()->getExamRunningStatus($row['start_time'], $row['end_time']);

        $name = M('users')->field('nick')->where("user_id='%s'", $user_id)->find();

        $this->zadd('isruning', $isruning);
        $this->zadd('row', $row);
        $this->zadd('name', $name['nick']);

        $this->auto_display();
    }
}
