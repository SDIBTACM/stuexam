<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 12/11/2016 17:22
 */

namespace Teacher\Controller;


use Basic\Log;
use Teacher\Convert\GenerateExamConvert;
use Teacher\Model\KeyPointBaseModel;
use Teacher\Model\QuestionPointBaseModel;
use Teacher\Service\ExamService;
use Think\Upload;

class ConfigurationController extends TemplateController {
    public function _initialize() {
        parent::_initialize();
        if (!$this->isSuperAdmin()) {
            if (!strcmp($this->action, "getparentpointbychapterid") ||
                !strcmp($this->action, "getchildrenpointbyparentid")) {
                // omit
            } else {
                $this->echoError("only admin can do this!");
            }
        }
    }

    public function keyPoint() {

        $this->ZaddChapters();

        $points = KeyPointBaseModel::instance()->getAllPoint();
        $pointMap = array();
        foreach ($points as $point) {
            $point['children'] = array();
            $chapterId = $point['chapter_id'];
            $pointId = $point['id'];
            $parentId = $point['parent_id'];
            if (!isset($pointMap[$chapterId])) {
                $pointMap[$chapterId] = array();
            }
            if ($parentId == 0) {
                if (!isset($pointMap[$chapterId][$pointId])) {
                    $pointMap[$chapterId][$pointId] = $point;
                } else {
                    $pointMap[$chapterId][$pointId] = array_merge($point, $pointMap[$chapterId][$pointId]);
                }
            } else {
                if (!isset($pointMap[$chapterId][$parentId]['children'])) {
                    $pointMap[$chapterId][$parentId]['children'] = array();
                }
                $pointMap[$chapterId][$parentId]['children'][] = $point;
            }
        }
        $this->zadd('points', $pointMap);
        $this->auto_display('point', 'configlayout');
    }

    public function removePoint() {
        if (IS_AJAX) {
            $pointId = I('post.pointid', 0, 'intval');
            Log::info("user id: {}, remove key point id :{}", $this->userInfo['user_id'], $pointId);
            KeyPointBaseModel::instance()->delById($pointId);
            $res = KeyPointBaseModel::instance()->delByParentId($pointId);
            QuestionPointBaseModel::instance()->delPoint($pointId);
            echo $res;
        }
    }

    public function addPoint() {
        $chapterId = I('post.chapterId', 0, 'intval');
        $parentId = I('post.parentId', 0, 'intval');
        $name = I('post.name', '');

        if (empty($name) || empty($chapterId)) {
            $this->echoError("章节或知识点内容不能为空!");
        }

        $point = array(
            'chapter_id' => $chapterId,
            'parent_id' => $parentId,
            'name' => $name
        );
        $res = KeyPointBaseModel::instance()->insertData($point);
        if ($res <= 0) {
            Log::warn("user id: {}, require: add key point, result: FAIL! sql data:{}, sql result: {}",
                $this->userInfo['user_id'], $point, $res);
            $this->echoError("添加失败");
        } else {
            Log::info("user id: {}, require: add key point, result: success!", $this->userInfo['user_id']);
            redirect(U('keyPoint'));
        }
    }

    public function updatePointDescById() {
        $pointId = I('post.pointid', 0, 'intval');
        $name = I('post.name', '');
        if ($pointId < 0 || empty($name)) {
            echo 1;
        }

        $res = KeyPointBaseModel::instance()->updateById($pointId, array('name' => $name));
        if (empty($res)) {
            echo 0;
        } else {
            echo 1;
        }
    }

    public function getParentPointByChapterId() {
        $chapterId = I('get.chapterId', 0, 'intval');
        $parentPoint = KeyPointBaseModel::instance()->getParentNodeByChapterId($chapterId);
        $this->ajaxReturn($parentPoint, 'JSON');
    }

    public function getChildrenPointByParentId() {
        $parentId = I('get.parentId', 0, 'intval');
        $childrenPoint = KeyPointBaseModel::instance()->getChildrenNodeByParentId($parentId);
        $this->ajaxReturn($childrenPoint, 'JSON');
    }

    public function generateExam() {
        $data = S("generatorExamResult");

        if (empty($data)) {
            $data = array(
                'total' => array(0, 0, 0, 0, 0),
                'failCount' => 0,
                'failDetail' => array(
                    0 => array('count' => 0, 'message' => '---'),
                    1 => array('count' => 0, 'message' => '---'),
                    2 => array('count' => 0, 'message' => '---'),
                    3 => array('count' => 0, 'message' => '---')
                ),
                'examId' => 0,
                'examUrl' => "---"
            );
        }

        $this->zadd("lastGenerateData", $data);

        $this->auto_display('generate', 'configlayout');
    }

    public function doGenerate() {
        if (!IS_AJAX) {
            $this->ajaxCodeReturn(4004, "请求错误");
            return;
        }

        $shellPath = 'Public/generator.sh';
        $codePath = RUNTIME_PATH . "ExamTemp";

        $result = trim(shell_exec($shellPath . " " . $codePath));

        if ($result == 500) {
            $this->ajaxCodeReturn(4004, "参数错误请联系管理员");
        } else if ($result == 404) {
            $this->ajaxCodeReturn(4004, "生成算法文件不存在, 请先上传");
        } else if ($result != 0) {
            $this->ajaxCodeReturn(4004, "生成算法编译时发生错误");
        } else {
            $generateResult = ExamService::instance()->autoGenerateExam();
            if (!$generateResult->getStatus()) {
                $this->ajaxCodeReturn(4004, $generateResult->getMessage());
            } else {
                $data = $generateResult->getData();
                $option = array("type" => "File");
                S("generatorExamResult", $data, $option);

                shell_exec("rm -f " . $codePath . "/generateCode*");

                if ($data['failCount'] > 0) {
                    $this->ajaxCodeReturn(2002, "部分题目添加失败", $data);
                } else {
                    $this->ajaxCodeReturn(1001, "题目全部添加成功", $data);
                }
            }
        }
    }

    public function uploadGenerator() {

        if (!IS_AJAX) {
            $return = array();
            $return['code'] = 4004;
            $return['message'] = "请求不合法";
            $this->ajaxReturn($return, 'JSON');
        }

        $config = array(
            'maxSize' => 5242880,
            'rootPath' => RUNTIME_PATH,
            'savePath' => 'ExamTemp/',
            'saveName' => 'generateCode',
            'exts' => array('c', 'cpp'),
            'autoSub' => false,
            'replace' => true, //存在同名是否覆盖
        );

        $upload = new Upload($config);
        $res = $upload->upload();
        if (!$res) {
            $this->ajaxCodeReturn(4004, $upload->getError());
        } else {
            $this->ajaxCodeReturn(1001, '上传成功');
        }
    }

    public function testGenerate() {
        $this->ajaxCodeReturn(1001, GenerateExamConvert::generateProblem());
    }
}
