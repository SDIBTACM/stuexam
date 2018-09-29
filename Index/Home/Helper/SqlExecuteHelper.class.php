<?php

namespace Home\Helper;

/**
 * 代码中目前散落的直接 sql 运行整理, 后续方便迁移
 * Class SqlExecuteHelper
 *
 * @package \Home\Helper
 */
class SqlExecuteHelper {

    public static function Home_GetUserScore($user_id) {
        $sql = "SELECT `title`,`exam`.`exam_id`,`score`,`choosesum`,`judgesum`,`fillsum`,`programsum` " .
            "FROM `exam`,`ex_student` WHERE `ex_student`.`user_id`='" . $user_id . "' AND `exam`.`visible`='Y' " .
            "AND `ex_student`.`exam_id`=`exam`.`exam_id` AND score >= 0 ORDER BY `exam`.`exam_id` DESC";
        return M()->query($sql);
    }

    public static function Home_GetLastSubmitDate($user_id) {
        $sql = "SELECT " . "`in_date` FROM `solution` WHERE `user_id`='" . $user_id .
            "' AND `in_date`>NOW()-10 ORDER BY `in_date` DESC LIMIT 1";
        return M()->query($sql);
    }

    public static function Home_SubmitSourceCode($insert_id, $source) {
        $sql = "INSERT " . "INTO `source_code`(`solution_id`,`source`) VALUES('$insert_id','$source')";
        M()->execute($sql);
    }

    public static function Home_UpdateProblemInDate($pid) {
        $sql = "UPDATE " . "`problem` SET `in_date`=NOW() WHERE `problem_id` = " . $pid;
        M()->execute($sql);
    }

    public static function Home_GetFillQuestionForExam($eid, $type) {
        $sql = "SELECT " . "`fill_id`,`answer_id` FROM `fill_answer` WHERE `fill_id` IN " .
            "(SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='$type')";
        return M()->query($sql);
    }

    public static function Home_GetProgramResultData($questionIdStr, $user_id, $start_timeC, $end_timeC) {
        $sql = "SELECT " . "problem_id, max(pass_rate) as rate from solution where problem_id in ($questionIdStr) and " .
            "user_id='$user_id' and in_date>'$start_timeC' and in_date<'$end_timeC' group by problem_id";
        return M()->query($sql);
    }

    public static function Home_GetUserLoginLog($user_id, $today, $ip1) {
        $sql = "SELECT " . " user_id FROM loginlog WHERE user_id='$user_id' AND `time`>='$today' AND ip<>'$ip1' AND " .
            "user_id NOT IN( SELECT user_id FROM privilege WHERE rightstr='administrator' " .
            "or rightstr='contest_creator') ORDER BY `time` DESC limit 0,1";
        return M()->query($sql);
    }

    public static function Home_GetPerfectProgramResult($user_id, $questionIdStr, $start_timeC, $end_timeC) {
        $sql = "select distinct(problem_id) as problem_id " .
            "from solution where problem_id in ($questionIdStr) and " .
            "user_id='$user_id' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'";
        return M()->query($sql);
    }

    /*******************************************************************/

    public static function Teacher_GetUserScoreList($userId) {
        $sql = "SELECT `title`,`exam`.`exam_id`,`score`,`choosesum`,`judgesum`,`fillsum`,`programsum` " .
            "FROM `exam`,`ex_student` WHERE `ex_student`.`user_id`='" . $userId .
            "' AND `ex_student`.`exam_id`=`exam`.`exam_id` AND score >= 0";
        return M()->query($sql);
    }

    public static function Teacher_GetEachScoreDistribution($realnum, $eid, $sqladd) {
        $sql = "SELECT COUNT(*) as `realnum`,MAX(`choosesum`) as `choosemax`,MAX(`judgesum`) as `judgemax`,MAX(`fillsum`) as `fillmax`," .
            "MAX(`programsum`) as `programmax`,MIN(`choosesum`) as `choosemin`,MIN(`judgesum`) as `judgemin`,MIN(`fillsum`) as `fillmin`," .
            "MIN(`programsum`) as `programmin`,MAX(`score`) as `scoremax`,MIN(`score`) as `scoremin`, SUM(`choosesum`) / $realnum as `chooseavg`," .
            "SUM(`judgesum`) / $realnum as `judgeavg`,SUM(`fillsum`) / $realnum as `fillavg`,SUM(`programsum`) / $realnum as `programavg`," .
            "SUM(`score`) / $realnum as `scoreavg` FROM `ex_student` WHERE `exam_id`='$eid' $sqladd AND `score` >= 0";
        return M()->query($sql);
    }

    public static function Teacher_GetEachProgramAvgScore($programScore, $personCnt, $programId, $sTime, $eTime, $examId, $sqladd) {
        $sql = "select (sum(rate) * $programScore / $personCnt) as r " .
            "from ( select user_id, if(max(pass_rate)=0.99, 1, max(pass_rate)) as rate from solution " .
            "where problem_id=$programId and pass_rate > 0 and " .
            "in_date>='$sTime' and in_date<='$eTime' and user_id in (select user_id from ex_privilege where rightstr='e$examId') " .
            "$sqladd group by user_id" .
            ") t";
        return M()->query($sql);
    }

    public static function Teacher_GetUserScoreList4Exam($eid, $sqladd) {
        $sql = "SELECT `stu`.`user_id`,`stu`.`nick`,`choosesum`,`judgesum`,`fillsum`,`programsum`,`score`,`extrainfo` " .
            "FROM (SELECT `users`.`user_id`,`users`.`nick`,`extrainfo` FROM `ex_privilege`,`users` WHERE `ex_privilege`.`user_id`=`users`.`user_id` AND " .
            "`ex_privilege`.`rightstr`=\"e$eid\" )stu left join `ex_student` on `stu`.`user_id`=`ex_student`.`user_id` AND " .
            "`ex_student`.`exam_id`='$eid' $sqladd";
        return M()->query($sql);
    }

    public static function Teacher_GetProgramList($pList) {
        $sql = "select defunct, author, problem_id " .
            "from problem where problem_id in ($pList)";
        return M()->query($sql);
    }

    public static function Teacher_GetUserAcceptProgramCnt4Exam($eid) {
        $sql = "select user_id, count(distinct question_id) as cnt" .
            " from ex_stuanswer where exam_id = " . $eid .
            " and type = 4 and answer_id = 1 and answer = 4 group by user_id order by cnt desc";
        return M()->query($sql);
    }

    public static function Teacher_GetChooseProblem4Exam($eid, $type) {
        $sql = "SELECT `ex_choose`.`choose_id`,`question`,`ams`,`bms`,`cms`,`dms`,`answer`,`private_code`" .
            " FROM `ex_choose`,`exp_question`" .
            " WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_choose`.`choose_id`=`exp_question`.`question_id`" .
            " ORDER BY `choose_id`";
        return M()->query($sql);
    }

    public static function Teacher_GetChooseAnswer4Exam($eid) {
        $sql = "SELECT `choose_id`,`answer` " .
            "FROM `ex_choose` WHERE `choose_id` IN " .
            "(SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='1')";
        return M()->query($sql);
    }

    public static function Teacher_GetJudgeProblem4Exam($eid, $type) {
        $sql = "SELECT `ex_judge`.`judge_id`,`question`,`answer`,`private_code`" .
            " FROM `ex_judge`,`exp_question`" .
            " WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_judge`.`judge_id`=`exp_question`.`question_id`" .
            " ORDER BY `judge_id`";
        return M()->query($sql);
    }

    public static function Teacher_GetJudgeAnswer4Exam($eid) {
        $sql = "SELECT `judge_id`,`answer` " .
            "FROM `ex_judge` WHERE `judge_id` IN " .
            "(SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='2')";
        return M()->query($sql);
    }

    public static function Teacher_GetFillProblem4Exam($eid, $type) {
        $sql = "SELECT `ex_fill`.`fill_id`,`question`,`answernum`,`kind`,`private_code`" .
            " FROM `ex_fill`,`exp_question`" .
            " WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_fill`.`fill_id`=`exp_question`.`question_id`" .
            " ORDER BY `fill_id`";
        return M()->query($sql);
    }

    public static function Teacher_GetFillAnswer4Exam($eid) {
        $sql = "SELECT `fill_answer`.`fill_id`,`answer_id`,`answer`,`answernum`,`kind` " .
            "FROM `fill_answer`,`ex_fill` " .
            "WHERE `fill_answer`.`fill_id`=`ex_fill`.`fill_id` AND `fill_answer`.`fill_id` IN " .
            "( SELECT `question_id` FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='3')";
        return M()->query($sql);
    }

    public static function Teacher_GetProgramProblem4Exam($eid) {
        $sql = "SELECT `question_id` as `program_id`,`title`,`description`,`input`,`output`,`sample_input`,`sample_output` " .
            "FROM `exp_question`,`problem` " .
            "WHERE `exam_id`='$eid' AND `type`='4' AND `question_id`=`problem_id` order by exp_qid asc";
        return M()->query($sql);
    }

    public static function Teacher_AddUserPrivilege($userIdList, $rightStr) {
        $query = "";
        $flag = true;
        foreach ($userIdList as $userId) {
            if ($flag) {
                $flag = false;
                $query = "INSERT " . "INTO `ex_privilege`(`user_id`,`rightstr`,`randnum`, `extrainfo`) VALUES('" .
                    trim($userId) . "','" . $rightStr . "',0,0)";
            } else {
                $query = $query . ",('" . trim($userId) . "','" . $rightStr . "',0,0)";
            }
        }
        M()->execute($query);
    }

}
