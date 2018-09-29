<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午4:39
 */

namespace Teacher\Controller;


use Basic\Log;
use Home\Helper\PrivilegeHelper;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Service\ExamService;

class QuizController extends AbsEventController {
    protected function doSave() {
        if (!$this->isCreator()) {
            Log::info("user id:{} {} id: {}, require: change {} info, result: FAIL, reason: no admin or creator ",
                $this->userInfo['user_id'], __FUNCTION__, I('get.eid', 0, 'intval'), __FUNCTION__);
            $this->echoError('You have no privilege!');
        }
        $reqResult = null;
        if (isset($_POST['examid'])) {
            $reqResult = ExamService::instance()->updateExamInfo();
        } else if (isset($_POST['examname'])) {
            $reqResult = ExamService::instance()->addExamInfo();
        }
        $this->checkReqResult($reqResult);
    }

    protected function doDelete($id, $page) {
        if (!$this->isOwner4ExamByExamId($id)) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: privilege",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->echoError('You have no privilege!');
        } else {
            $data = array('visible' => 'N');
            ExamBaseModel::instance()->updateById($id, $data);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->success("考试删除成功", U("Teacher/Quiz/showList", array('page' => $page)), 2);
        }
    }

    protected function getDetail() {
        $examId = I('get.eid', 0, 'intval');
        if ($examId > 0) {
            $examInfo = ExamBaseModel::instance()->getById($examId);
            if (empty($examInfo)) {
                $this->echoError('No Such Exam!');
            }
            if (!PrivilegeHelper::isExamOwner($examInfo['creator'])) {
                $this->echoError('You have no privilege!');
            }
            $this->zadd('row', $examInfo);
        }
    }

    protected function getList() {
        $sql = getexamsearch($this->userInfo['user_id']);
        $myPage = splitpage('exam', $sql);
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
            ->limit($myPage['sqladd'])
            ->select();
        $this->zadd('row', $row);
        $this->zadd('mypage', $myPage);
        $this->zadd('teacherList', PrivilegeBaseModel::instance()->getTeacherListWithCache());
        $this->zadd("creator", $creator);
        $this->zadd("extraQuery", $extraQuery);
    }

    public function copyOneExam() {
        $eid = I('get.eid', 0, 'intval');
        $row = ExamBaseModel::instance()->getById($eid);
        if (empty($row)) {
            $this->echoError("No Such Exam!");
        }
        if (!PrivilegeHelper::isExamOwner($row['creator'])) {
            $this->echoError('You have no privilege!');
        } else {
            // copy exam's base info
            unset($row['exam_id']);
            $row['creator'] = $this->userInfo['user_id'];
            $examId = ExamBaseModel::instance()->insertData($row);
            if (empty($examId)) {
                Log::warn("user id: {}, require: clone exam id: {} , result: FAIL", $this->userInfo['user_id'], $eid);
                $this->echoError("复制考试失败,请刷新页面重试");
            }
            // copy exam's problem
            $field = array('exam_id', 'question_id', 'type');
            $res = QuestionBaseModel::instance()->getQuestionByExamId($eid, $field);
            foreach ($res as &$r) {
                $r['exam_id'] = $examId;
            }
            unset($r);
            QuestionBaseModel::instance()->insertQuestions($res);
            Log::info("user id: {}, require: clone exam id: {} , result: success", $this->userInfo['user_id'], $examId);
            $this->success('考试复制成功!', U('/Teacher/Quiz/showList'), 1);
        }
    }

}
