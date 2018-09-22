<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 15/10/2017 21:49
 */

namespace Teacher\Controller;


use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\QuestionBaseModel;
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
        $query = "SELECT `title`,`exam`.`exam_id`,`score`,`choosesum`,`judgesum`,`fillsum`,`programsum` " .
            "FROM `exam`,`ex_student` WHERE `ex_student`.`user_id`='" . $userId .
            "' AND `ex_student`.`exam_id`=`exam`.`exam_id` AND score >= 0";
        $scoreList = M()->query($query);

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

        $query = "select user_id, count(distinct question_id) as cnt" .
            " from ex_stuanswer where exam_id = ".  $this->eid .
            " and type = 4 and answer_id = 1 and answer = 4 group by user_id order by cnt desc";
        $acCount = M()->query($query);
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

        $totalnum = M('ex_privilege')->where("rightstr='e$this->eid' $sqladd")->count();
        $realnum = M('ex_student')->where("exam_id=$this->eid $sqladd and score>=0")->count();

        $query = "SELECT COUNT(*) as `realnum`,MAX(`choosesum`) as `choosemax`,MAX(`judgesum`) as `judgemax`,MAX(`fillsum`) as `fillmax`,".
            "MAX(`programsum`) as `programmax`,MIN(`choosesum`) as `choosemin`,MIN(`judgesum`) as `judgemin`,MIN(`fillsum`) as `fillmin`,".
            "MIN(`programsum`) as `programmin`,MAX(`score`) as `scoremax`,MIN(`score`) as `scoremin`, SUM(`choosesum`) / $realnum as `chooseavg`,".
            "SUM(`judgesum`) / $realnum as `judgeavg`,SUM(`fillsum`) / $realnum as `fillavg`,SUM(`programsum`) / $realnum as `programavg`,".
            "SUM(`score`) / $realnum as `scoreavg` FROM `ex_student` WHERE `exam_id`='$this->eid' $sqladd AND `score` >= 0";
        $row = M()->query($query);

        $fd[] = M('ex_student')->where("score>=0  and score<60 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=60 and score<70 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=70 and score<80 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=80 and score<90 and exam_id=$this->eid $sqladd")->count();
        $fd[] = M('ex_student')->where("score>=90 and score<=100 and exam_id=$this->eid $sqladd")->count();

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

        $this->auto_display();
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

            $allScore = ExamService::instance()->getBaseScoreByExamId($examId);
            $examBase = ExamBaseModel::instance()->getById($examId);
            $sTime = $examBase['start_time'];
            $eTime = $examBase['end_time'];

            $programScore = $allScore['programscore'];

            $sql = "select (sum(rate) * $programScore / $personCnt) as r from (" .
                "select user_id, if(max(pass_rate)=0.99, 1, max(pass_rate)) as rate from solution " .
                "where problem_id=$programId and pass_rate > 0 and " .
                "in_date>='$sTime' and in_date<='$eTime' and user_id in (select user_id from ex_privilege where rightstr='e$examId') " .
                "$sqladd group by user_id" .
                ") t";

            $res = M()->query($sql);
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
        $row = ExamBaseModel::instance()->getExamInfoById($eid, array('title'));

        $_res = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($users, $eid);
        if (empty($_res)) {
            $this->echoError("The student have no privilege to take part in it");
        }

        $allscore = ExamService::instance()->getBaseScoreByExamId($eid);

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
        $this->zadd('allscore', $allscore);
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
