<?php

namespace Teacher\Convert;

use Constant\ReqResult\Result;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Service\ProblemService;

/**
 * 通过生成算法生成好的题号文件列表, 自动生成试卷
 * 在此解析
 * Class GenerateExamConvert
 *
 * @package \Teacher\Convert
 */
class GenerateExamConvert {

    public static function generateProblem() {

        $generateFilePath = RUNTIME_PATH . "ExamTemp/examList.txt";
        $generateProgramPath = RUNTIME_PATH . "ExamTemp/generateCode";

        $lastProblemType = 0;
        $problemMap = array();

        if (!file_exists($generateProgramPath)) {
            return Result::errorResult("生成算法不存在! 每次生成必须使用最新上传的生成文件");
        }

        if (!file_exists($generateFilePath)) {
            return Result::errorResult("题目列表文件不存在, 无法生成试卷");
        }

        $fp = @fopen($generateFilePath, "r");
        if ($fp) {
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                if (self::isProblemSplitter($line) || strpos($line, '题') !== false) {
                    $lastProblemType = self::getProblemType($line);
                } else if (self::isConcernedLine($line)) {
                    if ($lastProblemType <= 0) {
                        continue;
                    }
                    if (!isset($problemMap[$lastProblemType])) {
                        $problemMap[$lastProblemType] = array();
                    }
                    if (!in_array($line, $problemMap[$lastProblemType])) {
                        array_push($problemMap[$lastProblemType], $line);
                    }
                }
            }
            fclose($fp);
        } else {
            return Result::errorResult("读取题目列表文件失败");
        }

        return Result::successResultWithData($problemMap);
    }

    private static function isProblemSplitter($line) {
        if (strpos($line, "A:") !== false || strpos($line, "B:") !== false ||
            strpos($line, "C:") !== false || strpos($line, "D:") !== false) {
            return true;
        }
        return false;
    }

    private static function getProblemType($line) {
        if (strpos($line, "A:") !== false ||
            strpos($line, ChooseBaseModel::CHOOSE_PROBLEM_NAME) !== false) {
            return ChooseBaseModel::CHOOSE_PROBLEM_TYPE;
        } else if (strpos($line, "B:") !== false ||
            strpos($line, JudgeBaseModel::JUDGE_PROBLEM_NAME) !== false) {
            return JudgeBaseModel::JUDGE_PROBLEM_TYPE;
        } else if (strpos($line, "C:") !== false ||
            strpos($line, FillBaseModel::FILL_PROBLEM_NAME) !== false) {
            return FillBaseModel::FILL_PROBLEM_TYPE;
        } else if (strpos($line, "D:") !== false ||
            strpos($line, ProblemService::PROGRAM_PROBLEM_NAME) !== false) {
            return ProblemService::PROGRAM_PROBLEM_TYPE;
        } else {
            return 0;
        }
    }

    private static function isConcernedLine($line) {
        return preg_match("/^[_]?([0-9]+)$/", $line);
    }
}
