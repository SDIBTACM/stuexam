<?php

/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午3:12
 */

namespace Teacher\Controller;


use Basic\Log;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\QuestionPointBaseModel;
use Teacher\Model\StudentAnswerModel;
use Teacher\Service\ChooseService;
use Teacher\Service\KeyPointService;

class ChooseController extends AbsQuestionController {
    protected function doSave() {
        $reqResult = null;
        if (isset($_POST['chooseid'])) {
            $reqResult = ChooseService::instance()->updateChooseInfo();
        } else if (isset($_POST['choose_des'])) {
            $reqResult = ChooseService::instance()->addChooseInfo();
        }
        $this->checkReqResult($reqResult);
    }

    protected function doDelete($id, $page) {
        $tmp = ChooseBaseModel::instance()->getById($id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: no privilege",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->echoError('You have no privilege!');
        } else {
            ChooseBaseModel::instance()->delById($id);
            QuestionBaseModel::instance()->delQuestionByType($id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            StudentAnswerModel::instance()->delAnswerByQuestionAndType($id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            QuestionPointBaseModel::instance()->delByQuestion($id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->success("选择题删除成功", U("Teacher/Choose/showList", array('page' => $page)), 2);
        }
    }

    protected function getList() {
        $sch = getproblemsearch('choose_id', ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $myPage = splitpage('ex_choose', $sch['sql']);
        $numOfChoose = 1 + ($myPage['page'] - 1) * $myPage['eachpage'];
        $row = M('ex_choose')
            ->field('choose_id,question,creator,easycount,private_code')
            ->where($sch['sql'])
            ->order('private_code asc, choose_id asc')
            ->limit($myPage['sqladd'])
            ->select();
        $widgets = array(
            'row' => $row,
            'mypage' => $myPage,
            'numofchoose' => $numOfChoose
        );

        $questionIds = array();
        foreach ($row as $r) {
            $questionIds[] = $r['choose_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);

        $this->ZaddWidgets($widgets);
    }

    protected function getDetail() {
        $id = I('get.id', 0, 'intval');
        if ($id > 0) {
            $row = ChooseBaseModel::instance()->getById($id);
            if (empty($row)) {
                $this->error('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                Log::info("user id: {} {} id: {}, require: change {} info, result: FAIL, reason: private question ",
                    $_SESSION['user_id'], __FUNCTION__, $id, __FUNCTION__);
                $this->echoError('You have no privilege!');
            }
            $pnt = KeyPointService::instance()->getQuestionPoints($id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $this->zadd('row', $row);
            $this->zadd('pnt', $pnt);
        }
    }

    protected function getQuestionHaveIn($examId) {
        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType(
            $examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE
        );
        $haveAdded = array();
        foreach ($questionAddedIds as $qid) {
            $haveAdded[$qid['question_id']] = 1;
        }
        return $haveAdded;
    }
}
