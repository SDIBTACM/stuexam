<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-9 下午3:13
 */

namespace Teacher\Controller;


use Basic\Log;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\QuestionPointBaseModel;
use Teacher\Model\StudentAnswerModel;
use Teacher\Service\JudgeService;
use Teacher\Service\KeyPointService;

class JudgeController extends AbsQuestionController {
    protected function doSave() {
        $reqResult = null;
        if (isset($_POST['judgeid'])) {
            $reqResult = JudgeService::instance()->updateJudgeInfo();
        } else if (isset($_POST['judge_des'])) {
            $reqResult = JudgeService::instance()->addJudgeInfo();
        }
        $this->checkReqResult($reqResult);
    }

    protected function doDelete($id, $page) {
        $tmp = JudgeBaseModel::instance()->getById($id, array('creator', 'isprivate'));
        if (!$this->isProblemCanDelete($tmp['isprivate'], $tmp['creator'])) {
            Log::info("user id: {} {} id: {}, result: delete, result: FAIL! reason: no privilege",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->echoError('You have no privilege!');
        } else {
            JudgeBaseModel::instance()->delById($id);
            QuestionBaseModel::instance()->delQuestionByType($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            StudentAnswerModel::instance()->delAnswerByQuestionAndType($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            QuestionPointBaseModel::instance()->delByQuestion($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            Log::info("user id: {} {} id: {}, result: delete, result: success",
                $this->userInfo['user_id'], __FUNCTION__, $id);
            $this->success("判断题删除成功", U("Teacher/Judge/showList", array('page' => $page)), 2);
        }
    }

    protected function getList() {
        $sch = getproblemsearch('judge_id', JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $myPage = splitpage('ex_judge', $sch['sql']);
        $numOfJudge = 1 + ($myPage['page'] - 1) * $myPage['eachpage'];
        $row = M('ex_judge')
            ->field('judge_id,question,creator,easycount,private_code')
            ->where($sch['sql'])
            ->order('private_code asc, judge_id asc')
            ->limit($myPage['sqladd'])
            ->select();
        $widgets = array(
            'row' => $row,
            'mypage' => $myPage,
            'numofjudge' => $numOfJudge,
        );

        $questionIds = array();
        foreach ($row as $r) {
            $questionIds[] = $r['judge_id'];
        }
        $this->getQuestionChapterAndPoint($questionIds, JudgeBaseModel::JUDGE_PROBLEM_TYPE);

        $this->ZaddWidgets($widgets);
    }

    protected function getDetail() {
        $id = I('get.id', 0, 'intval');
        if ($id > 0) {
            $row = JudgeBaseModel::instance()->getById($id);
            if (empty($row)) {
                $this->echoError('No Such Problem!');
            }
            if ($this->checkProblemPrivate($row['isprivate'], $row['creator']) == -1) {
                Log::info("user id: {} {} id: {}, require: change {} info, result: FAIL, reason: private question ",
                    $_SESSION['user_id'], __FUNCTION__, $id, __FUNCTION__);
                $this->echoError('You have no privilege!');
            }
            $pnt = KeyPointService::instance()->getQuestionPoints($id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $this->zadd('row', $row);
            $this->zadd('pnt', $pnt);
        }
    }

    protected function getQuestionHaveIn($examId) {
        $questionAddedIds = QuestionBaseModel::instance()->getQuestionIds4ExamByType(
            $examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE
        );
        $haveAdded = array();
        foreach ($questionAddedIds as $qid) {
            $haveAdded[$qid['question_id']] = 1;
        }
        return $haveAdded;
    }

}
