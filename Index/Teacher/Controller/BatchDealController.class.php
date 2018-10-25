<?php

namespace Teacher\Controller;

use Basic\Log;
use Home\Helper\SqlExecuteHelper;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\StudentBaseModel;
use Teacher\Service\ChooseService;
use Teacher\Service\FillService;
use Teacher\Service\JudgeService;
use Teacher\Service\ProblemService;

/**
 * Class BatchDealController
 *
 * @package \Teacher\Controller
 */
class BatchDealController extends TemplateController {


    private $eid = null;
    private $userIdList = array();

    public function _initialize() {
        parent::_initialize();
        $this->eid = I('eid', 0, 'intval');
        $userIdListStr = I('get.userIdList', '');
        $this->userIdList = explode(",", $userIdListStr);
        if ($this->eid <= 0) {
            $this->ajaxCodeReturn(4004, "错误的考试信息");
        }

        if (!$this->isOwner4ExamByExamId($this->eid)) {
            $this->ajaxCodeReturn(4004, 'You have no privilege of this exam~');
        }

        if (empty($this->userIdList)) {
            $this->ajaxCodeReturn(1001, "");
        }
    }

    public function submitUserPaper() {
        $examInfo = ExamBaseModel::instance()->getById($this->eid);
        if (time() < strtotime($examInfo['start_time'])) {
            $this->ajaxCodeReturn(4004, 'Exam Not Start');
        }

        $where = array(
            'user_id' => array('in', $this->userIdList),
            'rightstr' => 'e' . $this->eid,
            'extrainfo' => array('neq', 0)
        );
        $allStudent = PrivilegeBaseModel::instance()->queryAll($where, "user_id");
        $this->userIdList = array();
        foreach ($allStudent as $student) {
            array_push($this->userIdList, $student['user_id']);
        }

        $typeStr = I('type', 'all');
        $typeList = $this->getTypeList($typeStr);

        foreach ($this->userIdList as $userId) {
            $this->doSubmitOnePerson($userId, $typeList, $examInfo, false);
        }

        $this->ajaxCodeReturn(1001, $this->userIdList);
    }

    public function deleteUserScore() {
        $typeStr = I('type', 'all');
        $typeList = $this->getTypeList($typeStr);
        $where = array(
            'exam_id' => $this->eid,
            'user_id' => array('in', $this->userIdList)
        );
        foreach ($typeList as $type) {
            $this->delScoreByType($type, $where);
        }
        Log::info("user id: {} exam id: {}, require: del all score, result: success",
            $this->userInfo['user_id'], $this->eid);
        $this->ajaxCodeReturn(1001, "删除成功");
    }

    public function distributePaper() {
        // 删除这些中已有的
        $rightStr = 'wa' . $this->eid;
        $where = array(
            "user_id" => array('in', $this->userIdList),
            "rightstr" => $rightStr
        );
        M('ex_privilege')->where($where)->delete();

        // 重新添加
        SqlExecuteHelper::Teacher_AddUserPrivilege($this->userIdList, $rightStr);
        $this->ajaxCodeReturn(1001, "");
    }

    public function recoverPaper() {
        // 删除这些中已有的
        $rightStr = 'wa' . $this->eid;
        $where = array(
            "user_id" => array('in', $this->userIdList),
            "rightstr" => $rightStr
        );
        M('ex_privilege')->where($where)->delete();

        $this->ajaxCodeReturn(1001, "");
    }

    private function getTypeList($typeStr) {
        $typeList = explode(',', $typeStr);
        if (in_array('all', $typeList)) {
            $typeList = array('all');
        }
        return $typeList;
    }

    private function delScoreByType($type, $where) {
        switch ($type) {
            case "choose" : {
                M('ex_student')->where($where)->save(array("choosesum" => -1, "score" => -1));
                break;
            }
            case "judge" : {
                M('ex_student')->where($where)->save(array("judgesum" => -1, "score" => -1));
                break;
            }
            case "fill" : {
                M('ex_student')->where($where)->save(array("fillsum" => -1, "score" => -1));
                break;
            }
            case "program" : {
                M('ex_student')->where($where)->save(array("programsum" => -1, "score" => -1));
                break;
            }
            default : {
                M('ex_student')->where($where)->delete();
                break;
            }
        }
    }

    private function doSubmitOnePerson($userId, $typeList, $examInfo, $needForceJudge) {
        $userScoreBefore = StudentBaseModel::instance()->getStudentScoreInfoByExamAndUserId(
            $this->eid, $userId
        );

        // 如果不是强制提交的场景, 如果这个学生已经提交了试卷, 就不再重判了
        if (!$needForceJudge && !empty($userScoreBefore) && $userScoreBefore['score'] > -1) {
            return;
        }

        $insert = false;
        if (empty($userScoreBefore)) {
            $userScoreBefore = array(
                'score' => -1,
                'choosesum' => -1,
                'judgesum' => -1,
                'fillsum' => -1,
                'programsum' => -1
            );
            $insert = true;
        }

        foreach ($typeList as $type) {
            $this->judgePaperByType($userId, $type, $userScoreBefore, $examInfo);
        }

        do {
            if ($userScoreBefore['choosesum'] < 0 || $userScoreBefore['judgesum'] < 0 ||
                $userScoreBefore['fillsum'] < 0 || $userScoreBefore['programsum'] < 0) {
                $sumScore = -1;
                break;
            }
            $sumScore = $userScoreBefore['choosesum'] + $userScoreBefore['judgesum'] +
                $userScoreBefore['fillsum'] + $userScoreBefore['programsum'];
        } while(false);

        $userScoreBefore['score'] = $sumScore;
        if ($insert) {
            $userScoreBefore['user_id'] = $userId;
            $userScoreBefore['exam_id'] = $this->eid;
            StudentBaseModel::instance()->insertData($userScoreBefore);
        } else {
            StudentBaseModel::instance()->updateStudentScore($this->eid, $userId, $userScoreBefore);
        }
        unset($userScoreBefore);
    }

    private function judgePaperByType($userId, $type, &$userScore, $examInfo) {
        if (!strcmp($type, "choose") || !strcmp($type, "all")) {
            $userScore['choosesum'] = ChooseService::instance()->doRejudgeChooseByExamIdAndUserId(
                $this->eid, $userId, $examInfo['choosescore']
            );
        }

        if (!strcmp($type, "judge") || !strcmp($type, "all")) {
            $userScore['judgesum'] = JudgeService::instance()->doRejudgeJudgeByExamIdAndUserId(
                $this->eid, $userId, $examInfo['judgescore']);
        }

        if (!strcmp($type, "fill") || !strcmp($type, "all")) {
            $userScore['fillsum'] = FillService::instance()->doRejudgeFillByExamIdAndUserId(
                $this->eid, $userId, $examInfo);
        }

        if (!strcmp($type, "program") || !strcmp($type, "all")) {
            $startTime = strftime("%Y-%m-%d %X", strtotime($examInfo['start_time']));
            $endTime = strftime("%Y-%m-%d %X", strtotime($examInfo['end_time']));
            $userScore['programsum'] = ProblemService::instance()->doRejudgeProgramByExamIdAndUserId(
                $this->eid, $userId, $examInfo['programscore'], $startTime, $endTime
            );
            ProblemService::instance()->doFixStuAnswerProgramRank(
                $this->eid, $userId, $startTime, $endTime
            );
        }
    }
}
