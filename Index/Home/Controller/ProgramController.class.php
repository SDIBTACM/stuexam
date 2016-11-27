<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/9 01:02
 */

namespace Home\Controller;

use Home\Model\AnswerModel;

use Teacher\Service\ExamService;
use Teacher\Service\ProblemService;
use Teacher\Service\StudentService;
use Think\Log;

class ProgramController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        if ($this->programSumScore != -1) {
            $this->success('该题型你已经交卷,不能再查看了哦', $this->navigationUrl, 1);
            exit;
        }

        if ($this->programCount == 0) {
            redirect($this->navigationUrl);
        }

        if (!$this->checkOtherProblemHasSubmit()) {
            $this->alertError("只有其他题型全部完成之后才能做编程题!", $this->navigationUrl);
        }
    }

    private function checkOtherProblemHasSubmit() {
        if (($this->chooseCount && $this->chooseSumScore == -1) ||
            ($this->judgeCount  && $this->judgeSumScore == -1)  ||
            ($this->fillCount   && $this->fillSumScore == -1)) {
            return false;
        }
        return true;
    }

    public function index() {

        $this->start2Exam();

        $allBaseScore = ExamService::instance()->getBaseScoreByExamId($this->examId);
        $programans = ProblemService::instance()->getProblemsAndAnswer4Exam($this->examId, ProblemService::PROGRAM_PROBLEM_TYPE);

        foreach ($programans as &$pans) {
            $pans['pFillNum'] = $this->getProgramFillNum($pans['program_id']);
        }
        unset($pans);

        $this->zadd('allscore', $allBaseScore);
        $this->zadd('programans', $programans);
        $this->zadd('questionName', ProblemService::PROGRAM_PROBLEM_NAME);
        $this->zadd('problemType', ProblemService::PROGRAM_PROBLEM_TYPE);

        $this->auto_display('Exam:program', 'exlayout');
    }

    public function submitPaper() {
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));
        $allscore = ExamService::instance()->getBaseScoreByExamId($this->examId);
        $inarr['choosesum'] = ($this->chooseSumScore == -1 ? 0 : $this->chooseSumScore);
        $inarr['judgesum'] = ($this->judgeSumScore == -1 ? 0 : $this->judgeSumScore);
        $inarr['fillsum'] = ($this->fillSumScore == -1 ? 0 : $this->fillSumScore);
        $pright = AnswerModel::instance()->getRightProgramCount($this->userInfo['user_id'], $this->examId, $start_timeC, $end_timeC);
        $inarr['programsum'] = round($pright * $allscore['programscore']);
        $inarr['score'] = $inarr['choosesum'] + $inarr['judgesum'] + $inarr['fillsum'] + $inarr['programsum'];
        StudentService::instance()->submitExamPaper(
            $this->userInfo['user_id'], $this->examId, $inarr);
        redirect(U('Home/Index/score'));
    }

    public function prgsubmit() {
        $id = I('post.id', 0, 'intval');
        if ($id <= 0) {
            $this->echoError("No Such Problem");
        }
        $languagePostName = 'language' . $id;
        $language = intval($_POST[$languagePostName]);
        if ($language > 9 || $language < 0) $language = 0;
        $language = strval($language);

        $OJ_DATA = C('OJ_DATA');
        $extarr = C('language_ext');
        $ext = $extarr[$language];
        $extendsPrefix = C('EXTEND_PREFIX');
        $extendsLimit = C('EXTEND_LIMIT');
        $filePrefix = $OJ_DATA . '/' . $id . '/' . $extendsPrefix;

        // for medium extends
        $source = "";
        for ($extendsIndex = 1; $extendsIndex <= $extendsLimit; $extendsIndex++) {
            $fileName = $filePrefix . $extendsIndex .'.c';
            $postName = 'code' . $id . '_' . $extendsIndex;
            if (file_exists($fileName)) {
                if (isset($_POST[$postName])) {
                    $_source = $_POST[$postName];
                    if (get_magic_quotes_gpc()) {
                        $_source = stripslashes($_source);
                    }
                    $_source = addslashes($_source);
                    $source = $source . "\n" . $_source;
                }
                $source = $source . "\n" . addslashes(file_get_contents($fileName) . "\n");
            } else {
                if (isset($_POST[$postName])) {
                    $_source = $_POST[$postName];
                    if (get_magic_quotes_gpc()) {
                        $_source = stripslashes($_source);
                    }
                    $_source = addslashes($_source);
                    $source = $source . "\n" . $_source;
                }
                break;
            }
        }

        // for prefix and last append
        $OJ_APPENDCODE = C('OJ_APPENDCODE');
        $prefix_file = "$OJ_DATA/$id/prefix.$ext";
        $append_file = "$OJ_DATA/$id/append.$ext";
        if ($OJ_APPENDCODE && file_exists($prefix_file)) {
            $source = addslashes(file_get_contents($prefix_file) . "\n") . $source;
        }
        if ($OJ_APPENDCODE && file_exists($append_file)) {
            $source .= addslashes("\n" . file_get_contents($append_file));
        }

        $len = strlen($source);

        if ($len <= 2) {
            echo 'Source Code Too Short!';
            exit(0);
        }
        if ($len > 65536) {
            echo 'Source Code Too Long!';
            exit(0);
        }

        $this->_submitCode($id, $language, $len, $source);
    }

    public function updresult() {
        sleep(1);
        $id = intval($_GET['id']);
        $userId = $this->userInfo['user_id'];
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));
        $where = array(
            'problem_id' => $id,
            'user_id' => $userId,
            'result' => 4,
            'in_date' => array(array('gt', $start_timeC), array('lt', $end_timeC))
        );
        $row_cnt = M('solution')
            ->field('result')
            ->where($where)
            ->find();
        if (!empty($row_cnt)) {
            Log::record("updresult method, trace 1, userId: $userId , problemId: $id, hasResult4: " . json_encode($row_cnt));
            $_res = ProblemService::instance()->syncProgramAnswer($userId, $this->examId, $id, 4, null);
            Log::record("updresult method, trace 2, userId: $userId, problemId: $id, sync answer res: $_res");
            if ($_res > 0) {
                echo "<font color='blue' size='3px'>此题已正确,请不要重复提交</font>";
            } else {
                echo "<font color='blue' size='3px'>不知道发生了什么, 请刷新页面重试哦~~</font>";
            }
        } else {
            Log::record("updresult method, trace 1, userId: $userId , problemId: $id, hasResult4: null");
            $where = array(
                'problem_id' => $id,
                'user_id' => $userId,
                'in_date' => array(array('gt', $start_timeC), array('lt', $end_timeC))
            );
            $trow = M('solution')
                ->field(array('result', 'pass_rate'))
                ->where($where)
                ->order('solution_id desc')
                ->find();
            if (empty($trow)) {
                Log::record("updresult method, trace 3, userId: $userId, problemId: $id, solution: null");
                echo "<font color='green' size='3px'>未提交</font>";
            } else {
                Log::record("updresult method, trace 3, userId: $userId, problemId: $id, solution: " . json_encode($trow));
                $ans = $trow['result'];
                $_res = ProblemService::instance()->syncProgramAnswer($userId, $this->examId, $id, $ans, $trow['pass_rate']);
                Log::record("updresult method, trace 4, userId: $userId, problemId: $id, sync answer res: $_res");
                if ($_res < 0) {
                    $this->echoError("<font color='blue' size='3px'>不知道发生了什么,请刷新页面重试~</font>");
                }
                $colorarr = C('judge_color');
                $resultarr = C('judge_result');
                $color = $colorarr[$ans];
                $result = $resultarr[$ans];
                if ($ans == 6 && $trow['pass_rate'] * 100 == 0) {
                    $result = "答案错误了, 一个都不对";
                }
                echo "<font color=$color size='3px'>$result</font>";
            }
        }
    }

    public function programSave() {
        $pid = I('post.pid', 0, 'intval');
        $userId = $this->userInfo['user_id'];
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));
        $where = array(
            'problem_id' => $pid,
            'user_id' => $userId,
            'result' => 4,
            'in_date' => array(array('gt', $start_timeC), array('lt', $end_timeC))
        );
        $row_cnt = M('solution')
            ->field(array('result'))
            ->where($where)
            ->find();
        if (!empty($row_cnt)) {
            Log::record("programSave method, trace 1, userId: $userId , problemId: $pid, programAnswer: " . json_encode($row_cnt));
            $res = ProblemService::instance()->syncProgramAnswer($userId, $this->examId, $pid, 4, null);
            Log::record("programSave method, trace 2, userId: $userId, problemId: $pid, sync answer res: $res");
            $this->echoError(4);
        } else {
            Log::record("programSave method, trace 1, userId: $userId , problemId: $pid, programAnswer: null");
            $this->echoError(-1);
        }
    }

    private function _submitCode($pid, $language, $codeLength, $source) {

        $user_id = $this->userInfo['user_id'];

        $sql = "SELECT `in_date` FROM `solution` WHERE `user_id`='" . $user_id . "' AND `in_date`>NOW()-10 ORDER BY `in_date` DESC LIMIT 1";
        $row = M()->query($sql);
        if ($row) {
            echo "You should not submit more than twice in 10 seconds.....<br>";
            exit(0);
        }

        $sourceCode = array(
            'problem_id' => $pid,
            'user_id' => $user_id,
            'in_date' => date('Y-m-d H:i:s'),
            'language' => $language,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'code_length' => $codeLength
        );
        $insert_id = M('solution')->add($sourceCode);

        $sql = "INSERT INTO `source_code`(`solution_id`,`source`) VALUES('$insert_id','$source')";
        M()->execute($sql);
        $sql = "UPDATE `problem` SET `in_date`=NOW() WHERE `problem_id`=$pid";
        M()->execute($sql);
        $colorarr = C('judge_color');
        $resultarr = C('judge_result');
        $color = $colorarr[0];
        $result = $resultarr[0];
        echo "<span color=$color size='5px'>$result</span>";
    }

    private function getProgramFillNum($id) {
        $programFillNum = 0;
        $OJ_DATA = C('OJ_DATA');
        $extendsPrefix = C('EXTEND_PREFIX');
        $extendsLimit = C('EXTEND_LIMIT');
        $filePrefix = $OJ_DATA . '/' . $id . '/' . $extendsPrefix;
        for ($extendsIndex = 1; $extendsIndex <= $extendsLimit; $extendsIndex++) {
            $fileName = $filePrefix . $extendsIndex . '.c';
            if (!file_exists($fileName)) {
                break;
            }
            $programFillNum++;
        }
        if (0 == $programFillNum) {
            $programFillNum = 1;
        }
        return $programFillNum;
    }
}
