<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 08/11/2016 22:07
 */

namespace Community\Controller;

/**
 * 本控制器主要用于一些特殊情况下要显示的页面, just for fun
 * 就不按照框架的设计去做了,所有的数据层也写在这里.
 * Class ExtraController
 * @package Community\Controller
 */
class ExtraController extends TemplateController
{
    public function _initialize() {
        $this->isNeedLogin = false;
        self::$RANK_CONTEST_ID = I('get.cid', 1);
        parent::_initialize();
    }

    private static $DEFAULTRATE = 0;

    private static $RANK_CONTEST_ID;

    // 计分比例
    private $scorePercent = array(
        'english' => 7,
        'chinese' => 1,
        'person'  => 2
    );

    public function rank() { // 获取所有注册的学生
        $students = $this->getAllSignUpStudent();
        $userIds = array();
        foreach ($students as $_student) {
            $userIds[] = $_student['user_id'];
        }

        // 获取所有通过的学生
        $allUserAccept = $this->getAllAcceptStudent();

        // 获取这些学生所有答对的题的个数
        $userAllSolved = $this->getUserSolved($userIds);

        // 获取第二页做的题目数量
        $userEnProblemSolved = $this->getEnglish2ndPageSolved($userIds);

        // 获取本周做的题数
        $weekProblemSolved = $this->getRecentWeekSolved($userIds);

        foreach ($students as &$student) {
            $_userId = $student['user_id'];
            $englishNum = isset($userEnProblemSolved[$_userId]) ? intval($userEnProblemSolved[$_userId]) : 0;
            $chineseNum = $userAllSolved[$_userId] - $englishNum;
            $student['chineseNum'] = $chineseNum;
            $student['englishNum'] = $englishNum;
            $student['person'] = empty($student['seatnum']) ? self::$DEFAULTRATE : $student['seatnum'];
            $student['week'] = isset($weekProblemSolved[$_userId]) ? $weekProblemSolved[$_userId] : 0;
            $score = $chineseNum * $this->scorePercent['chinese']
                + $englishNum * $this->scorePercent['english']
                + $student['person'] * $this->scorePercent['person'];
            $student['score'] = $score;
            if ($student['stusex'] == 'F') {
                $student['score'] = $student['score'] * 1.05;
            }
        }
        unset($student);

        $students = myMultiSort($students, 'score', SORT_DESC);
        $rank = 0; $preScore = -1; $cnt = 0;
        foreach ($students as &$student) {
            if ($student['score'] != $preScore) {
                $rank = $rank + $cnt + 1;
                $preScore = $student['score'];
                $cnt = 0;
            } else {
                $cnt++;
            }
            $student['rank'] = $rank;
        }
        unset($student);

        $this->zadd("students", $students);
        $this->zadd("acceptUsers", $allUserAccept);
        $this->auto_display(null, false);
    }

    private function getAllAcceptStudent() {
        $sql = "select user_id, sturealname as `name`,studepartment,stumajor from contestreg where contest_id = " . self::$RANK_CONTEST_ID ." and ispending=1 order by seatnum asc";
        $students = M()->query($sql);
        return $students;
    }

    private function getAllSignUpStudent() {
        // contest id is $RANK_CONTEST_ID
        $sql = "select user_id, sturealname as `name`, seatnum, stusex from contestreg where contest_id = ". self::$RANK_CONTEST_ID ." and ispending=0";
        $students = M()->query($sql);
        return $students;
    }

    private function getUserSolved($userIds) {
        $userAllSolved = array();
        if (empty($userIds)) {
            return $userAllSolved;
        }

        $userStr = implode('\',\'', $userIds);
        $userStr = '\'' . $userStr . '\'';
        $sql = "select user_id, solved from users where user_id in ( $userStr )";
        $userSolved = M()->query($sql);
        foreach ($userSolved as $solved) {
            $userAllSolved[$solved['user_id']] = $solved['solved'];
        }

        $sql = "select user_id, count(distinct(problem_id)) as num from solution where " .
            "problem_id >= 3501 and result = 4 and " .
            "user_id in ($userStr) group by user_id";
        $notContainedProblem = M()->query($sql);

        foreach ($notContainedProblem as $problem) {
            if (isset($userAllSolved[$problem['user_id']])) {
                $userAllSolved[$problem['user_id']] -= $problem['num'];
            }
        }

        return $userAllSolved;
    }

    private function getEnglish2ndPageSolved($userIds) {
        $userEnProblemSolved = array();
        if (empty($userIds)) {
            return $userEnProblemSolved;
        }

        $userStr = implode('\',\'', $userIds);
        $userStr = '\'' . $userStr . '\'';
        $sql = "select user_id, count(distinct(problem_id)) as num from solution where " .
            "problem_id >= 1145 and problem_id <= 1191 and result = 4 and " .
            "user_id in ($userStr) group by user_id";
        $problemSolved = M()->query($sql);

        foreach ($problemSolved as $solved) {
            $userEnProblemSolved[$solved['user_id']] = $solved['num'];
        }
        return $userEnProblemSolved;
    }

    private function getRecentWeekSolved($userIds) {
        $userWeekSolved = array();
        if (empty($userIds)) {
            return $userWeekSolved;
        }
        $userStr = implode('\',\'', $userIds);
        $userStr = '\'' . $userStr . '\'';
        $monday = mktime(0, 0, 0, date('m'), date('d') - (date('w') + 6) % 7, date('Y'));
        $mondayStr = strftime("%Y-%m-%d", $monday);
        $sql = "select count(distinct problem_id) as solved, user_id ".
            "from solution where user_id in ($userStr) and result = 4 and in_date>= '$mondayStr' group by user_id";
        $weekSolved = M()->query($sql);
        foreach ($weekSolved as $_week) {
            $userWeekSolved[$_week['user_id']] = $_week['solved'];
        }
        return $userWeekSolved;
    }
}
