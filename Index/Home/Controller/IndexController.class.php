<?php
namespace Home\Controller;

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
                $_where = array(
                    'isprivate' => 0,
                    'creator' => array('eq', $userId),
                    '_logic' => 'or'
                );
                $where = array(
                    'visible' => 'Y',
                    '_complex' => $_where,
                    '_logic' => 'and'
                );
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

        $where['order'] = array('end_time desc');
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
}
