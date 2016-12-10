<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:44
 */

namespace Community\Controller;


use Community\Model\NodeModel;
use Community\Model\TopicModel;

class NodeController extends TemplateController
{
    function __construct() {
        parent::__construct();
    }

    /**
     * 获取文章
     */
    public function topics() {
        $node = I('get.node');
        if (!nodeValidate($node)) {
            $this->error('传输参数错误');
        }
        $nodeInfo = NodeModel::instance()->getNodeInfo($node);
        $topics = TopicModel::instance()->getTopicsByNode($node);
        $this->assign('nodeInfo', $nodeInfo);
        $this->assign('topics', $topics);
        $this->assign('node', $node);
        $this->showSidebar('all');//展示侧边栏
        $this->display('Topic/node');
    }
}