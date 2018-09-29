<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 15/10/2017 21:49
 */

namespace Teacher\Controller;


use Home\Helper\SqlExecuteHelper;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\QuestionBaseModel;
use Teacher\Model\StudentAnswerModel;
use Teacher\Model\StudentBaseModel;
use Teacher\Service\ExamService;
use Teacher\Service\ProblemService;

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
        $scoreList = SqlExecuteHelper::Teacher_GetUserScoreList($userId);
        $this->ajaxReturn($scoreList, 'JSON');
    }

    public function programRank() {

        $this->isCanWatchInfo($this->eid);

        $where = array(
            'exam_id' => $this->eid,
            'type' => 4,
            'answer_id' => 1,
//            'answer' => 4
        );
        $field = array('user_id', 'question_id', 'answer');
        $programRank = M('ex_stuanswer')->field($field)->where($where)->select();
        $userRank = array();
        $users = array();
        $unames = array();
        $programCount = array();

        $acCount = SqlExecuteHelper::Teacher_GetUserAcceptProgramCnt4Exam($this->eid);
        foreach($acCount as $ac) {
            $programCount[$ac['user_id']] = $ac['cnt'];
            $users[] = $ac['user_id'];
        }

        foreach ($programRank as $p) {
            $userRank[$p['user_id']][$p['question_id']] = $p['answer'];
            if (!in_array($p['user_id'], $users)) {
                $users[] = $p['user_id'];
            }
        }

        $userIds_chunk = array_chunk($users, 50);
        foreach ($userIds_chunk as $_userIds) {
            $where = array(
                'user_id' => array('in', $_userIds)
            );
            $field = array('user_id', 'nick');
            $_unames = M('users')->field($field)->where($where)->select();
            foreach ($_unames as $_uname) {
                $unames[$_uname['user_id']] = $_uname['nick'];
            }
            usleep(10000);
        }

        $query = array(
            'exam_id' => $this->eid,
            'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
            'order' => 'exp_qid'
        );
        $programs = QuestionBaseModel::instance()->queryData($query, array('question_id'));

        $this->zadd('unames', $unames);
        $this->zadd('userIds', $users);
        $this->zadd('programIds', $programs);
        $this->zadd('userRank', $userRank);
        $this->zadd('programCount', $programCount);
        $this->auto_display("ranklist");
    }

    public function analysis() {
        $this->isCanWatchInfo($this->eid);
        $student = I('get.student', '', 'htmlspecialchars');
        $sqladd = '';
        if (!empty($student)) {
            $sqladd = " AND `user_id` like '$student%'";
        }

        $where = "rightstr='e$this->eid' $sqladd";
        $totalnum = PrivilegeBaseModel::instance()->countNumber($where);
        $where = "exam_id=$this->eid $sqladd and score>=0";
        $realnum = StudentBaseModel::instance()->countNumber($where);
        $row = SqlExecuteHelper::Teacher_GetEachScoreDistribution($realnum, $this->eid, $sqladd);

        $where = "score>=0  and score<60 and exam_id=$this->eid $sqladd";
        $fd[] = StudentBaseModel::instance()->countNumber($where);
        $where = "score>=60 and score<70 and exam_id=$this->eid $sqladd";
        $fd[] = StudentBaseModel::instance()->countNumber($where);
        $where = "score>=70 and score<80 and exam_id=$this->eid $sqladd";
        $fd[] = StudentBaseModel::instance()->countNumber($where);
        $where = "score>=80 and score<90 and exam_id=$this->eid $sqladd";
        $fd[] = StudentBaseModel::instance()->countNumber($where);
        $where = "score>=90 and score<=100 and exam_id=$this->eid $sqladd";
        $fd[] = StudentBaseModel::instance()->countNumber($where);

        $query = array(
            'exam_id' => $this->eid,
            'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
            'order' => 'exp_qid'
        );
        $programIds = QuestionBaseModel::instance()->queryData($query, array('question_id'));

        $programAvgScore = $this->getEachProgramAvgScore($programIds, $realnum, $sqladd);

        $this->zadd('totalnum', $totalnum);
        $this->zadd('row', $row[0]);
        $this->zadd('fd', $fd);
        $this->zadd('student', $student);
        $this->zadd("programIds", $programIds);
        $this->zadd("programAvgScore", $programAvgScore);

        $chooseProblem = ProblemService::instance()->getProblemsAndAnswer4Exam($this->eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $judgeProblem = ProblemService::instance()->getProblemsAndAnswer4Exam($this->eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $fillProblem = ProblemService::instance()->getProblemsAndAnswer4Exam($this->eid, FillBaseModel::FILL_PROBLEM_TYPE);

        $chooseQuestionIds = array();
        $judgeQuestionIds = array();
        $fillQuestionIds = array();

        $chooseResultMap = array();
        $judgeResultMap = array();

        foreach ($chooseProblem as $_choose) {
            $chooseQuestionIds[] = $_choose['choose_id'];
            $_resultMap = array(
                'id' => $_choose['choose_id'],
                'rightPerson' => $this->getEachQuestionRightPerson(
                    $this->eid, $_choose['choose_id'], ChooseBaseModel::CHOOSE_PROBLEM_TYPE, $_choose['answer']
                ),
                'privateCode' => $_choose['private_code']
            );
            $chooseResultMap[] = $_resultMap;
        }
        foreach ($judgeProblem as $_judge) {
            $judgeQuestionIds[] = $_judge['judge_id'];

            $_resultMap = array(
                'id' => $_judge['judge_id'],
                'rightPerson' => $this->getEachQuestionRightPerson(
                    $this->eid, $_judge['judge_id'], JudgeBaseModel::JUDGE_PROBLEM_TYPE, $_judge['answer']
                ),
                'privateCode' => $_judge['private_code']
            );

            $judgeResultMap[] = $_resultMap;
        }
        foreach ($fillProblem as $_fill) {
            $fillQuestionIds[] = $_fill['fill_id'];
        }

        $this->zadd('chooseResultMap', $chooseResultMap);
        $this->zadd('judgeResultMap', $judgeResultMap);
        $this->zadd('fillQuestionIds', $fillQuestionIds);

        $this->zadd('choosePointMap', ProblemService::instance()->getQuestionPoint(
            $chooseQuestionIds, ChooseBaseModel::CHOOSE_PROBLEM_TYPE
        ));
        $this->zadd('judgePointMap', ProblemService::instance()->getQuestionPoint(
            $judgeQuestionIds, JudgeBaseModel::JUDGE_PROBLEM_TYPE
        ));
        $this->zadd('fillPointMap', ProblemService::instance()->getQuestionPoint(
            $fillQuestionIds, FillBaseModel::FILL_PROBLEM_TYPE
        ));

        $this->auto_display();
    }

    private function getEachQuestionRightPerson($examId, $questionId, $type, $rightAnswer) {
        $where = array(
            'exam_id' => $examId,
            'question_id' => $questionId,
            'type' => $type,
            'answer' => $rightAnswer
        );
        return StudentAnswerModel::instance()->countNumber($where, "distinct user_id");
    }

    private function getEachProgramAvgScore($programIds, $personCnt, $sqladd) {

        $examId = $this->eid;
        $ans = array();

        foreach($programIds as $_programId) {

            $programId = $_programId['question_id'];

            if ($personCnt == 0) {
                $ans[$programId] = 0;
                continue;
            }

            $examBase = ExamBaseModel::instance()->getById($examId);
            $sTime = $examBase['start_time'];
            $eTime = $examBase['end_time'];

            $programScore = $examBase['programscore'];

            $res = SqlExecuteHelper::Teacher_GetEachProgramAvgScore(
                $programScore, $personCnt, $programId, $sTime, $eTime, $examId, $sqladd
            );
            if (empty($res)) {
                $ans[$programId] = 0;
            } else {
                $ans[$programId] = isset($res[0]['r']) ? $res[0]['r'] : 0;
            }
        }
        return $ans;
    }

    public function showPaper() {
        if (!(isset($_GET['eid']) && isset($_GET['users']))) {
            $this->echoError('Wrong Path');
            return;
        }

        $eid = intval(trim($_GET['eid']));
        $this->isCanWatchInfo($eid);

        $users = trim($_GET['users']);
        $row = ExamBaseModel::instance()->getById($eid);

        $_res = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($users, $eid);
        if (empty($_res)) {
            $this->echoError("The student have no privilege to take part in it");
        }

        $choosearr = ExamService::instance()->getUserAnswer($eid, $users, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $judgearr = ExamService::instance()->getUserAnswer($eid, $users, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $fillarr = ExamService::instance()->getUserAnswer($eid, $users, FillBaseModel::FILL_PROBLEM_TYPE);

        $chooseans = ProblemService::instance()->getProblemsAndAnswer4Exam($eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
        $judgeans = ProblemService::instance()->getProblemsAndAnswer4Exam($eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $fillans = ProblemService::instance()->getProblemsAndAnswer4Exam($eid, FillBaseModel::FILL_PROBLEM_TYPE);
        $fillans2 = array();

        if ($fillans) {
            foreach ($fillans as $key => $value) {
                $fillans2[$value['fill_id']] = ProblemService::instance()
                    ->getProblemsAndAnswer4Exam($value['fill_id'], ProblemService::PROBLEMANS_TYPE_FILL);
            }
        }
        $this->zadd('title', $row['title']);
        $this->zadd('allscore', $row);
        $this->zadd('choosearr', $choosearr);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('fillarr', $fillarr);
        $this->zadd('chooseans', $chooseans);
        $this->zadd('judgeans', $judgeans);
        $this->zadd('fillans', $fillans);
        $this->zadd('fillans2', $fillans2);

        $this->auto_display('paper');
    }
}
