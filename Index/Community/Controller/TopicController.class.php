<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:45
 */

namespace Community\Controller;


use Community\Model\CommentModel;
use Community\Model\NodeModel;
use Community\Model\TopicModel;

class TopicController extends TemplateController
{

    function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->redirect("Index/index", 0);
    }

    /**
     * 发布新主题
     */
    public function add($nid = "") {
        if (IS_POST) {
            $data['title'] = I('post.title', '', 'trim');
            $data['content'] = I('post.content', '', 'trim');
            $data['node_id'] = I('post.node_id', '', 'intval');
            $data['cat_id'] = NodeModel::instance()->getCatIdByNodeId($data['node_id']);
            $data['uid'] = $this->userInfo['uid'];
            if (TopicModel::instance()->addTopic($data)) {
                $this->success('发布主题成功', U("Index/index"));
            } else {
                $this->error('发布新主题失败,请稍后重试');
            }
        } else {
            $nodes = NodeModel::instance()->getAllNodes();
            $hotNodes = NodeModel::instance()->getHotNodes();
            $this->assign('nid', $nid);
            $this->assign('nodes', $nodes);
            $this->assign('hotNodes', $hotNodes);
            $this->display('new');
        }
    }

    /**
     * 主题详情
     */
    public function detail() {
        $tid = I('get.tid', '', 'intval');
        if (!TopicModel::instance()->checkTid($tid)) {
            $this->error('传输参数错误');
        }
        $topicInfo = TopicModel::instance()->getDataById($tid);        //根据tid获取详情
        $commentInfo = CommentModel::instance()->getCommentByTid($tid);    //根据tid获取评论
        $this->assign('topicInfo', $topicInfo);
        $this->assign('commentInfo', $commentInfo);
        $this->assign('tid', $tid);
        $this->showSidebar('all');//展示侧边栏
        $this->display();
    }

    /**
     * 追加主题内容
     */
    public function append() {
        if (IS_POST) {
            $content = I('post.content', '', 'trim,htmlspecialchars') == '' ?
                $this->error('追加信息不能为空') :
                I('post.content', '', 'trim');
            $tid = I('post.tid', '', 'intval');
            if (!TopicModel::instance()->checkTid($tid)) {
                $this->error('不要修改tid值');
            }
            if (!TopicModel::instance()->appendContent($tid, $content)) {
                $this->error($this->Topic->getError());
            }
            $this->success('追加信息成功!', U('Topic/detail', array('tid' => I('get.tid'))));
        } else {
            $data['tid'] = I('get.tid', '', 'intval');
            $data['title'] = TopicModel::instance()->getFieldByTid($data['tid'], 'title');
            $data['node'] = NodeModel::instance()->getNodeByTid($data['tid']);
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 收藏主题
     */
    public function collect_topic() {
        if (!IS_AJAX) {
            $this->error('非法访问');
        } else {
            $tid = I('post.tid');
            if ($tid) {
                if (TopicModel::instance()->collectTopic($tid)) {
                    //成功
                    $this->ajaxReturn('1');
                } else {
                    //失败
                    $this->ajaxReturn('0');
                }
            } else {
                $this->ajaxReturn('0');
            }
        }
    }

    /**
     * 取消收藏主题
     */
    public function remove_col_topic() {
        if (!IS_AJAX) {
            $this->error('非法访问');
        } else {
            $tid = I('post.tid');
            if ($tid) {
                if (TopicModel::instance()->removeColTopic($tid)) {
                    //成功
                    $this->ajaxReturn('1');
                } else {
                    //失败
                    $this->ajaxReturn('0');
                }
            } else {
                //失败 没有接受到tid值
                $this->ajaxReturn('0');
            }
        }

    }
}