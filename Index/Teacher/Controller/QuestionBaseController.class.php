<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 14/11/2016 17:52
 */

namespace Teacher\Controller;


use Constant\Constants\Chapter;
use Teacher\Model\KeyPointBaseModel;
use Teacher\Model\QuestionPointBaseModel;

class QuestionBaseController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
    }

    protected function buildSearch() {
        $chapterId = I('get.chapterId', 0, 'intval');
        $parentId = I('get.parentId', 0, 'intval');
        $pointId = I('get.pointId', 0, 'intval');
        $problem = I('get.problem', 0, 'intval');
        $creator = I('get.creator', '', 'htmlspecialchars');
        $questionType = I('get.questionType', 0, 'intval');

        $this->zadd('chapterId', $chapterId);
        $this->zadd('parentId', $parentId);
        $this->zadd('pointId', $pointId);
        $this->zadd('problem', $problem);
        $this->zadd('creator', $creator);
        $this->zadd('questionType', $questionType);

        $extraQuery = 'problem=' . $problem . '&chapterId=' . $chapterId .
            '&parentId=' . $parentId . '&pointId=' . $pointId . '&creator=' . $creator . '&questionType='.$questionType;
        $this->zadd('extraQuery', $extraQuery);

        $parentNode = KeyPointBaseModel::instance()->getParentNodeByChapterId($chapterId);
        $childrenNode = KeyPointBaseModel::instance()->getChildrenNodeByParentId($parentId);

        $this->zadd('parentNode', $parentNode);
        $this->zadd('childrenNode', $childrenNode);

        $this->zadd('teacherList', C('TEACHER_LIST'));
    }

    protected function getQuestionChapterAndPoint($questionIds, $type) {
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
                'chapter' => $questionPoint['chapter_id'],
                'parent_point' => $pointMap[$questionPoint['point_parent_id']],
                'point' => $pointMap[$questionPoint['point_id']]
            );
        }
        $this->zadd('questionPointMap', $questionPointMap);
    }
}