<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 08/11/2016 22:07
 */

namespace Community\Controller;
use Teacher\Model\StudentBaseModel;


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
        parent::_initialize();
    }

    // 计分比例
    private $scorePercent = array(
        'english' => 7,
        'chinese' => 1,
        'personal' => 2
        //'score'   =>
    );

    /*
     * 第一周：英文题目（1145-1191）数量（占40%）+上次机考成绩（40%）+主观评价（20%）=总成绩！
     * 以后：英文题目（1145-1191）数量（占70%）+主观评价（30%）=总成绩！
     * */
    public function rank() {
        // 获取所有注册的学生
        $students = $this->getAllSignUpStudent();
        $userIds = array();
        foreach ($students as $_student) {
            $userIds[] = $_student['user_id'];
        }

        // 获取这些学生所有答对的题的个数
        $userAllSolved = $this->getUserSolved($userIds);

        // 获取第二页做的题目数量
        $userEnProblemSolved = $this->getEnglish2ndPageSolved($userIds);

        foreach ($students as &$student) {
            $_userId = $student['user_id'];
            $englishNum = isset($userEnProblemSolved[$_userId]) ? $userEnProblemSolved[$_userId] : 0;
            $chineseNum = $userAllSolved[$_userId] - $englishNum;
            $score = $chineseNum * $this->scorePercent['chinese'] + $englishNum * $this->scorePercent['english'];
            $student['score'] = $score;
        }
        unset($student);
        echo json_encode($students);

    }

    private function getAllSignUpStudent() {
        // contest id is 1753
        $sql = "select user_id, sturealname as `name` from contestreg where contest_id = 1753";
        $students = M()->query($sql);
        return $students;
    }

    private function getUserSolved($userIds) {
        $userStr = implode('\',\'', $userIds);
        $userStr = '\'' . $userStr . '\'';
        $sql = "select user_id, solved from users where user_id in ( $userStr )";
        $userSolved = M()->query($sql);
        $userAllSolved = array();
        foreach ($userSolved as $solved) {
            $userAllSolved[$solved['user_id']] = $solved['solved'];
        }
        return $userAllSolved;
    }

    private function getEnglish2ndPageSolved($userIds) {
        $userStr = implode('\',\'', $userIds);
        $userStr = '\'' . $userStr . '\'';
        $sql = "select user_id, count(distinct(problem_id)) as num from solution where " .
            "problem_id >= 1145 and problem_id <= 1191 and result = 4 and " .
            "user_id in ($userStr) group by user_id";
        $problemSolved = M()->query($sql);

        $userEnProblemSolved = array();
        foreach ($problemSolved as $solved) {
            $userEnProblemSolved[$solved['user_id']] = $solved['num'];
        }
        return $userEnProblemSolved;
    }
}