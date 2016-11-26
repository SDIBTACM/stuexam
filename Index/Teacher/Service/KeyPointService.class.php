<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 13/11/2016 20:11
 */

namespace Teacher\Service;


use Teacher\Model\KeyPointBaseModel;
use Teacher\Model\QuestionPointBaseModel;

class KeyPointService
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

    public function getExPointList() {
        $pnt = KeyPointBaseModel::instance()->getAllPoint();
        return $pnt;
    }

    public function saveExamPoint($pointIds, $questionId, $type) {

        if ($questionId <= 0 || $type <= 0 || $type >= 4) {
            return 0;
        }

        QuestionPointBaseModel::instance()->delByQuestion($questionId, $type);
        if (empty($pointIds)) return 1;

        $dataList = array();
        $pointList = KeyPointBaseModel::instance()->getByIds($pointIds);
        foreach ($pointList as $_point) {
            $data = array();
            $data['question_id'] = $questionId;
            $data['type'] = $type;
            $data['chapter_id'] = $_point['chapter_id'];
            $data['point_id'] = $_point['id'];
            $data['point_parent_id'] = $_point['parent_id'];
            $dataList[] = $data;
        }
        return QuestionPointBaseModel::instance()->insertDataList($dataList);
    }

    public function getQuestionPoints($questionId, $type) {
        $pointList = QuestionPointBaseModel::instance()->getQuestionPoint($questionId, $type);
        $pointIds = array();
        foreach ($pointList as $point) {
            $pointIds[] = $point['point_id'];
        }

        $points = KeyPointBaseModel::instance()->getByIds($pointIds);
        return $points;
    }
}