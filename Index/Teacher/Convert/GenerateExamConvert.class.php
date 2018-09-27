<?php

namespace Teacher\Convert;

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

    private static $generateFilePath = TEMP_PATH . "examList.txt";

    public static function generateProblem() {
        $lastProblemType = 0;
        $problemMap = array();

        if (!file_exists(self::$generateFilePath)) {
            return $problemMap;
        }

        $fp = @fopen(self::$generateFilePath, "r");
        if ($fp) {
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                if (strpos($line, '题') !== false) {
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
        }

        return $problemMap;
    }

    private static function getProblemType($line) {
        if (strpos($line, ChooseBaseModel::CHOOSE_PROBLEM_NAME)) {
            return ChooseBaseModel::CHOOSE_PROBLEM_TYPE;
        } else if (strpos($line, JudgeBaseModel::JUDGE_PROBLEM_NAME)) {
            return JudgeBaseModel::JUDGE_PROBLEM_TYPE;
        } else if (strpos($line, FillBaseModel::FILL_PROBLEM_NAME)) {
            return FillBaseModel::FILL_PROBLEM_TYPE;
        } else if (strpos($line, ProblemService::PROGRAM_PROBLEM_NAME)) {
            return ProblemService::PROGRAM_PROBLEM_TYPE;
        } else {
            return 0;
        }
    }

    private static function isConcernedLine($line) {
        return preg_match("/^[_]?([0-9]+)$/", $line);
    }
}
