<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 15/10/2017 21:49
 */

namespace Teacher\Controller;


class DataController extends TemplateController
{
    private $eid = null;

    public function _initialize() {
        parent::_initialize();
        if (isset($_GET['eid'])) {
            $this->eid = intval($_GET['eid']);
            $this->zadd('eid', $this->eid);
        } else if (isset($_POST['eid'])) {
            $this->eid = intval($_POST['eid']);
        } else {
            $this->echoError('No Such Exam!');
        }
    }

    public function userScoreList() {
        $scoreList = array();
        if (!isset($_GET['uid'])) {
            $this->ajaxReturn($scoreList, 'JSON');
        }

        $userId = I('get.uid', 0, 'trim');
        $query = "SELECT `title`,`exam`.`exam_id`,`score`,`choosesum`,`judgesum`,`fillsum`,`programsum` " .
            "FROM `exam`,`ex_student` WHERE `ex_student`.`user_id`='" . $userId .
            "' AND `ex_student`.`exam_id`=`exam`.`exam_id` AND score >= 0";
        $scoreList = M()->query($query);

        $this->ajaxReturn($scoreList, 'JSON');
    }
}