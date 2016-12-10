<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:44
 */

namespace Community\Controller;


class NodeController extends TemplateController
{
    public $Node;

    function __construct() {
        parent::__construct();
        $this->Node = D('node');
    }

    /**
     * 获取文章
     */
    public function topics() {
        $node = I('get.node');
        if (!nodeValidate($node)) {
            $this->error('传输参数错误');
        }
        $nodeInfo = $this->Node->getNodeInfo($node);
        $topics = D('Topic')->getTopicsByNode($node);
        $this->assign('nodeInfo', $nodeInfo);
        $this->assign('topics', $topics);
        //var_dump($topics);
        $this->assign('node', $node);
        $this->showSidebar('all');//展示侧边栏
        $this->display('Topic/node');
    }
}