<?php
namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamadminModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\ProblemServiceModel;
use Think\Exception;

class ExamController extends QuestionController
{
    public function _initialize() {
        parent::_initialize();
    }

    public function showquestion() {
        $this->addExamBaseInfo();
        $widgets = array();
        $userId = $this->userInfo['user_id'];
        try {
            $widgets['allscore'] = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);

            $widgets['choosearr'] = ExamServiceModel::instance()->getUserAnswer($this->examId, $userId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $widgets['judgearr'] = ExamServiceModel::instance()->getUserAnswer($this->examId, $userId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $widgets['fillarr'] = ExamServiceModel::instance()->getUserAnswer($this->examId, $userId, FillBaseModel::FILL_PROBLEM_TYPE);

            $widgets['chooseans'] = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $widgets['judgeans'] = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $widgets['fillans'] = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, FillBaseModel::FILL_PROBLEM_TYPE);
            $widgets['programans'] = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, ProblemServiceModel::PROGRAM_PROBLEM_TYPE);

            $widgets['choosesx'] = ExamadminModel::instance()->getproblemsx($this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, $this->randnum);
            $widgets['judgesx'] = ExamadminModel::instance()->getproblemsx($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, $this->randnum);
            $widgets['fillsx'] = ExamadminModel::instance()->getproblemsx($this->examId, FillBaseModel::FILL_PROBLEM_TYPE, $this->randnum);

            $data = array(
                'extrainfo' => $this->leftTime + 1
            );
            PrivilegeBaseModel::instance()->updatePrivilegeByUserIdAndExamId($userId, $this->examId, $data);
        } catch (Exception $e) {
        }
        $this->ZaddWidgets($widgets);
        $this->auto_display(null, false);
    }

    public function saveanswer() {
        AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);//choose over
        usleep(1000);
        AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);//judge over
        usleep(1000);
        AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, FillBaseModel::FILL_PROBLEM_TYPE);//fillover
        usleep(30000);
        echo "ok";
    }

    public function submitpaper() {
        $userId = $this->userInfo['user_id'];
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $cright = AnswerModel::instance()->answersave($userId, $this->examId, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, false);
        $jright = AnswerModel::instance()->answersave($userId, $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, false);
        $fscore = AnswerModel::instance()->answersave($userId, $this->examId, FillBaseModel::FILL_PROBLEM_TYPE, false);
        $pright = AnswerModel::instance()->getrightprogram($userId, $this->examId, $start_timeC, $end_timeC);
        $inarr['user_id'] = $userId;
        $inarr['exam_id'] = $this->examId;
        $inarr['choosesum'] = $cright * $allscore['choosescore'];
        $inarr['judgesum'] = $jright * $allscore['judgescore'];
        $inarr['fillsum'] = $fscore;
        $inarr['programsum'] = $pright * $allscore['programscore'];
        $inarr['score'] = $inarr['choosesum'] + $inarr['judgesum'] + $inarr['fillsum'] + $inarr['programsum'];
        M('ex_student')->add($inarr);
        $this->success('试卷已成功提交！', U("Home/Index/score"), 3);
    }

    public function updresult() {
        $id = I('get.id', 0, 'intval');
        if (!empty($id)) {
            $userId = $this->userInfo['user_id'];
            $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
            $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));

            $row_cnt = M('solution')//->where($where)->find();
            ->where("problem_id=%d and user_id='%s' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'", $id, $userId)->find();
            if (!empty($row_cnt)) {
                echo "<font color='blue' size='3px'>此题已正确,请不要重复提交</font>";
            } else {
                $trow = M('solution')->field('result')
                    ->where("problem_id=%d and user_id='%s' and in_date>'$start_timeC' and in_date<'$end_timeC'", $id, $userId)
                    ->order('solution_id desc')
                    ->find();
                if (empty($trow)) {
                    echo "<font color='green' size='5px'>未提交</font>";
                } else {
                    $ans = $trow['result'];
                    if ($ans == 4) {
                        ProblemServiceModel::instance()->syncProgramAnswer($userId, $this->examId, $id, $ans);
                    }
                    $colorarr = C('judge_color');
                    $resultarr = C('judge_result');
                    $color = $colorarr[$ans];
                    $result = $resultarr[$ans];
                    echo "<font color=$color size='5px'>$result</font>";
                }
            }
        } else {
            echo "参数错误";
        }
    }

    public function prgsubmit() {
        $id = I('post.id', 0, 'intval');
        $user_id = $this->userInfo['user_id'];
        $language = I('post.language', 0, 'intval');
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
            echo "Source Code Too Short!";
            exit(0);
        }
        if ($len > 65536) {
            echo "Source Code Too Long!";
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
        echo "<font color=$color size='5px'>$result</font>";
    }

    public function programSave() {
        $pid = I('post.pid', 0, 'intval');
        $userId = $this->userInfo['user_id'];
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->examBase['end_time']));
        $row_cnt = M('solution')
            ->where("problem_id=%d and user_id='%s' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'", $pid, $userId)
            ->count();
        if ($row_cnt) {
            ProblemServiceModel::instance()->syncProgramAnswer($userId, $this->examId, $pid, 4);
            echo 4;
        } else {
            echo -1;
        }
    }
}