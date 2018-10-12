<?php
namespace Teacher\Service;

use Basic\Log;
use Constant\Constants\Chapter;
use Home\Helper\SqlExecuteHelper;
use Home\Model\AnswerModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\KeyPointBaseModel;
use Teacher\Model\QuestionPointBaseModel;

class ProblemService
{
    const PROGRAM_PROBLEM_TYPE = 4;
    const PROBLEMANS_TYPE_FILL = 100;
    const PROGRAM_PROBLEM_NAME = "编程题";

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function addProgram2Exam($eid, $problemIds) {
        if ($eid <= 0) {
            return false;
        }
        $len = count($problemIds);
        $sql = "DELETE FROM `exp_question` WHERE `exam_id`={$eid} AND `type`='4'";
        M()->execute($sql);
        $dataList = array();
        for ($i = 0; $i < $len; $i++) {
            $programId = $problemIds[$i];
            $data = array(
                'exam_id' => $eid,
                'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
                'question_id' => $programId
            );
            $dataList[] = $data;
            M('problem')->where('problem_id=%d', $programId)
                ->data(array("defunct" => "Y"))->save();
        }
        M('exp_question')->addAll($dataList);
        return true;
    }

    public function getProblemsAndAnswer4Exam($eid, $problemType) {
        switch ($problemType) {
            case ChooseBaseModel::CHOOSE_PROBLEM_TYPE:
                return ChooseBaseModel::instance()->getChooseProblems4Exam($eid);
                break;

            case JudgeBaseModel::JUDGE_PROBLEM_TYPE:
                return JudgeBaseModel::instance()->getJudgeProblems4Exam($eid);
                break;

            case FillBaseModel::FILL_PROBLEM_TYPE:
                return FillBaseModel::instance()->getFillProblems4Exam($eid);
                break;

            case self::PROGRAM_PROBLEM_TYPE:
                return $this->getProgramProblems4Exam($eid);
                break;

            case self::PROBLEMANS_TYPE_FILL:
                return FillBaseModel::instance()->getFillAnswerByFillId($eid);
                break;
        }
    }

    public function getProgramProblems4Exam($eid) {
        return SqlExecuteHelper::Teacher_GetProgramProblem4Exam($eid);
    }

    public function syncProgramAnswer($userId, $eid, $pid, $judgeResult, $passRate, $sTime, $eTime) {
        Log::info("user id: {} , exam id: {} , problem id: {} , answer: {} , pass rate: {}", $userId, $eid, $pid, $judgeResult, $passRate === null ? "1.00" : $passRate);
        $dao = M('ex_stuanswer');
        $where = array(
            'user_id' => $userId,
            'exam_id' => $eid,
            'type' => ProblemService::PROGRAM_PROBLEM_TYPE,
            'question_id' => $pid,
            'answer_id' => 1
        );

        $field = array('answer');
        $res = $dao->field($field)->where($where)->find();
        // 如果沒有保存
        if (empty($res)) {
            Log::warn("empty record need to add");
            if ($judgeResult != 4) {
                $where['answer'] = strval($passRate);
            } else {
                $where['answer'] = "4";
            }
            return $dao->add($where);
        } else {
            Log::info("Program result need to update");
            $_ans = $res['answer'];
            $data = array();
            // 如果原先的是 Accept
            if (strcmp($_ans, "4") == 0) {
                if ($judgeResult == 4) {
                    // 现在的也是 Accept, 则不处理
                    return 1;
                } else {
                    // 如果现在不是 Accept, 反查一下, 判断是 rejudge 还是学生重复提交了
                    $_judgeWhere = array(
                        'problem_id' => $pid, 'user_id' => $userId, 'result' => 4,
                        'in_date' => array(array('gt', $sTime), array('lt', $eTime))
                    );
                    $row_cnt = M('solution')->field(array('result'))->where($_judgeWhere)->find();
                    if (!empty($row_cnt)) {
                        //先查询是否有 result = 4的, 有则返回
                        return 1;
                    } else {
                        // 没有则取 passRate 最高值
                        $_programJudge = SqlExecuteHelper::Home_GetProgramResultData(strval($pid), $userId, $sTime, $eTime);
                        if (is_array($_programJudge)) {
                            $data['answer'] = $_programJudge[0]['rate'] >= 0.98 ? "4" : strval($_programJudge['rate']);
                        }
                    }
                }
            } else {
                // 如果原先的就不是 Accept, 对比现在跟之前, 存储的最高值
                if ($judgeResult == 4) {
                    $data['answer'] = "4";
                } else if ($passRate > doubleval($_ans)) {
                    $data['answer'] = strval($passRate);
                }
            }
            if (!empty($data)) {
                return $dao->where($where)->data($data)->save();
            }
        }
        return 1;
    }

    public function doRejudgeProgramByExamIdAndUserId($eid, $userId, $programScore, $start_timeC, $end_timeC) {
        $row_cnt = AnswerModel::instance()->getRightProgramCount($userId, $eid, $start_timeC, $end_timeC);
        $programsum = formatToFloatScore($row_cnt * $programScore);
        //$program over
        return $programsum;
    }

    public function doFixStuAnswerProgramRank($eid, $userId, $startTime, $endTime) {
        $programStatus = AnswerModel::instance()->getExamProgramStatus($userId, $eid, $startTime, $endTime);
        if (empty($programStatus)) {
            return;
        }

        foreach($programStatus as $pid => $value) {
            if ($value == 1) {
                ProblemService::instance()->syncProgramAnswer(
                    $userId, $eid, $pid, 4, null, $startTime, $endTime
                );
            } else {
                ProblemService::instance()->syncProgramAnswer(
                    $userId, $eid, $pid, -1, $value, $startTime, $endTime
                );
            }
        }
    }

    public function getByPrivateCode($problemType, $privateCode) {
        switch ($problemType) {
            case ChooseBaseModel::CHOOSE_PROBLEM_TYPE:
                return ChooseBaseModel::instance()->getByPrivateCode($privateCode);

            case JudgeBaseModel::JUDGE_PROBLEM_TYPE:
                return JudgeBaseModel::instance()->getByPrivateCode($privateCode);

            case FillBaseModel::FILL_PROBLEM_TYPE:
                return FillBaseModel::instance()->getByPrivateCode($privateCode);

            default:
                return null;
        }
    }

    public function getQuestionPoint($questionIds, $type) {
        $questionPoints = QuestionPointBaseModel::instance()->getQuestionsPoint($questionIds, $type);
        $questionPointMap = array();

        $pointIds = array();
        foreach($questionPoints as $questionPoint) {
            $pointIds[] = $questionPoint['point_id'];
            $pointIds[] = $questionPoint['point_parent_id'];
        }
        $pointIds = array_unique($pointIds);
        $pointMap = array();
        $points = KeyPointBaseModel::instance()->getByIds($pointIds);
        foreach ($points as $point) {
            $pointMap[$point['id']] = $point['name'];
        }

        foreach ($questionPoints as $questionPoint) {
            if (!isset($questionPointMap[$questionPoint['question_id']])) {
                $questionPointMap[$questionPoint['question_id']] = array();
            }
            $questionPointMap[$questionPoint['question_id']][] = array(
                'chapter' => Chapter::getById($questionPoint['chapter_id'])->getPriority(),
                'chapterName' => Chapter::getById($questionPoint['chapter_id'])->getName(),
                'parent_point' => $pointMap[$questionPoint['point_parent_id']],
                'point' => $pointMap[$questionPoint['point_id']]
            );
        }
        return $questionPointMap;
    }
}
