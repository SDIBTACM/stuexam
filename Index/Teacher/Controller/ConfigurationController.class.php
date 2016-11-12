<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 12/11/2016 17:22
 */

namespace Teacher\Controller;


use Constant\Constants\Chapter;
use Teacher\Model\KeyPointBaseModel;

class ConfigurationController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
        if (!$this->isSuperAdmin()) {
            $this->echoError("only admin can do this!");
        }
    }

    public function keyPoint() {
        $chapters = Chapter::getConstant();
        $chapterMap = array();
        foreach ($chapters as $chapter) {
            if ($chapter instanceof Chapter) {
                $chapterMap[$chapter->getId()] = $chapter->getName();
            }
        }

        $points = KeyPointBaseModel::instance()->getAllPoint();
        $pointMap = array();
        foreach ($points as $point) {
            $chapterId = $point['chapter_id'];
            if (!isset($pointMap[$chapterId])) {
                $pointMap[$chapterId] = array();
            }
            $pointMap[$chapterId][] = $point;
        }

        //dbg($pointMap);
        //dbg($chapterMap);

        $this->zadd('chapters', $chapterMap);
        $this->zadd('points', $pointMap);
        $this->auto_display('point', 'configlayout');
    }

    public function removePoint() {
        if (IS_AJAX) {
            $pointId = I('post.pointid', 0, 'intval');
            $res = KeyPointBaseModel::instance()->delById($pointId);
            echo $res;
        }
    }

    public function addPoint() {
        $chapterId = I('post.chapterId', 0, 'intval');
        $name = I('post.name', '');
        if (empty($name) || empty($chapterId)) {
            $this->echoError("章节或知识点内容不能为空!");
        }

        $point = array(
            'chapter_id' => $chapterId,
            'name' => $name
        );
        $res = KeyPointBaseModel::instance()->insertData($point);
        if ($res <= 0) {
            $this->echoError("添加失败");
        } else {
            redirect(U('keyPoint'));
        }
    }
}