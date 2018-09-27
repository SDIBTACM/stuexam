<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 14/11/2016 17:52
 */

namespace Teacher\Controller;


use Teacher\Model\KeyPointBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Service\ProblemService;

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

        $this->zadd('teacherList', PrivilegeBaseModel::instance()->getTeacherListWithCache());
    }

    protected function getQuestionChapterAndPoint($questionIds, $type) {
        $questionPointMap = ProblemService::instance()->getQuestionPoint($questionIds, $type);
        $this->zadd('questionPointMap', $questionPointMap);
    }
}
