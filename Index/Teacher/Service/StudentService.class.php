<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/9/16 03:32
 */

namespace Teacher\Service;

use Teacher\Model\StudentBaseModel;

class StudentService
{
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

    public function submitExamPaper($userId, $examId, $scores) {
        $oldData = StudentBaseModel::instance()->getStudentScoreInfoByExamAndUserId($examId, $userId);
        if (empty($oldData)) {
            $scores['user_id'] = $userId;
            $scores['exam_id'] = $examId;
            StudentBaseModel::instance()->insertData($scores);
        } else {
            StudentBaseModel::instance()->updateStudentScore($examId, $userId, $scores);
        }
    }
}