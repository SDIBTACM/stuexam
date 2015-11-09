<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/9 01:02
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\ProblemServiceModel;

class ProgramController extends QuestionController
{

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $programans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, 5);

        $this->zadd('allscore', $allscore);
        $this->zadd('programans', $programans);

        $this->auto_display('Exam:program', false);
    }

    public function submitPaper() {
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));
        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $pright = AnswerModel::instance()->getrightprogram($this->userInfo['user_id'], $this->examId, $start_timeC, $end_timeC);
        $inarr['programsum'] = $pright * $allscore['programscore'];
        $inarr['score'] = $inarr['choosesum'] + $inarr['judgesum'] + $inarr['fillsum'] + $inarr['programsum'];
        // TODO update program score and total score
    }

    public function prgsubmit() {
        $id = I('post.id', 0, 'intval');
        $user_id = $this->userInfo['user_id'];
        $language = intval($_POST['language']);
        if ($language > 9 || $language < 0) $language = 0;
        $language = strval($language);

        $source = $_POST['source'];
        if (get_magic_quotes_gpc()) {
            $source = stripslashes($source);
        }
        $source = addslashes($source);
        $len = strlen($source);
        $OJ_DATA = C('OJ_DATA');
        $OJ_APPENDCODE = C('OJ_APPENDCODE');
        $extarr = C('language_ext');
        $ext = $extarr[$language];
        $prefix_file = "$OJ_DATA/$id/prefix.$ext";
        $append_file = "$OJ_DATA/$id/append.$ext";
        if ($OJ_APPENDCODE && file_exists($prefix_file)) {
            $source = addslashes(file_get_contents($prefix_file) . "\n") . $source;
        }
        if ($OJ_APPENDCODE && file_exists($append_file)) {
            $source .= addslashes("\n" . file_get_contents($append_file));
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($len <= 2) {
            echo 'Source Code Too Short!';
            exit(0);
        }
        if ($len > 65536) {
            echo 'Source Code Too Long!';
            exit(0);
        }
        $sql = "SELECT `in_date` FROM `solution` WHERE `user_id`='" . $user_id . "' AND `in_date`>NOW()-10 ORDER BY `in_date` DESC LIMIT 1";
        $row = M()->query($sql);
        if ($row) {
            echo "You should not submit more than twice in 10 seconds.....<br>";
            exit(0);
        }
        $arr['problem_id'] = $id;
        $arr['user_id'] = $user_id;
        $arr['in_date'] = date('Y-m-d H:i:s');
        $arr['language'] = $language;
        $arr['ip'] = $ip;
        $arr['code_length'] = $len;
        $insert_id = M('solution')->add($arr);
        $sql = "INSERT INTO `source_code`(`solution_id`,`source`) VALUES('$insert_id','$source')";
        M()->execute($sql);
        $sql = "UPDATE `problem` SET `in_date`=NOW() WHERE `problem_id`=$id";
        M()->execute($sql);
        $colorarr = C('judge_color');
        $resultarr = C('judge_result');
        $color = $colorarr[0];
        $result = $resultarr[0];
        echo "<span color=$color size='5px'>$result</span>";
    }

    public function updresult() {
        $id = intval($_GET['id']);
        $user_id = $this->userInfo['user_id'];

        $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));
        $row_cnt = M('solution')
            ->where("problem_id=%d and user_id='%s' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'", $id, $user_id)
            ->count();
        if ($row_cnt) {
            echo "<span color='blue' size='3px'>此题已正确,请不要重复提交</span>";
        } else {
            $trow = M('solution')->field('result')
                ->where("problem_id=%d and user_id='%s' and in_date>'$start_timeC' and in_date<'$end_timeC'", $id, $user_id)
                ->order('solution_id desc')
                ->find();
            if (!$trow) {
                echo "<span color='green' size='5px'>未提交</span>";
            } else {
                $ans = $trow['result'];
                $colorarr = C('judge_color');
                $resultarr = C('judge_result');
                $color = $colorarr[$ans];
                $result = $resultarr[$ans];
                echo "<span color=$color size='5px'>$result</span>";
            }
        }
    }
}