<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 20:08
 */

namespace Community\Model;


use Constant\ReqResult\Result;
use Teacher\Model\GeneralModel;
use Think\Model;

class TopicModel extends GeneralModel
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

    protected function getTableName() {
        return "topic";
    }

    protected function getTableFields() {
        // TODO: Implement getTableFields() method.
    }

    protected function getPrimaryId() {
        return 'id';
    }

    /**
     * 添加主题
     */
    public function addTopic($data) {
        $result = $this->checkInvalidTopicData($data);
        if ($result->getStatus()) {
            $data['publish_time'] = date('Y-m-d H:i:s', time());
            $this->insertData($data);
            if ($this->addTrigger($data['node_id'])) {
                return $result;
            } else {
                return new Result(false, "节点更新失败~");
            }
        } else {
            return $result;
        }
    }

    private function checkInvalidTopicData($data) {
        if (empty($data['title'])) {
            return new Result(false, "主题标题不能为空");
        }

        $titleLength = mb_strlen($data['title']);
        if ($titleLength > 120) {
            return new Result(false, "标题不要超过120个字符");
        }

        $contentLength = mb_strlen($data['content']);
        if ($contentLength > 2000) {
            return new Result(false, "话题内容不要超过2000个字符");
        }

        $nodeId = $data['node_id'];
        $data = NodeModel::instance()->getById($nodeId);
        if (empty($data)) {
            return new Result(false, "请不要随意修改节点的值");
        }
        return new Result(true);
    }

    /**
     * 追加主题内容
     */
    public function appendContent($tid, $content) {
        $originContent = $this->getDao()->where(array('id' => $tid))->getField('content');
        $newContent = $originContent . '<span class=\'append\'><hr><p class=\'small\' style=\'background-color:#F0F0F0\'>' . $content . '</p></span>';
        if ($this->getDao()->where(array('id' => $tid))->setField('content', $newContent)) {
            return true;
        }
        return false;
    }

    /**
     * 根据tid获取主题详情
     */
    public function getDataById($tid) {
        $topicInfo = $this
            ->getDao()
            ->where(array('discuss_topic.id' => $tid))
            ->join('discuss_node as n on n.id = discuss_topic.node_id')
            ->field('title,content,publish_time,user_name,discuss_topic.hits as hits,collections,comments,node_name,imgpath,last_comment_time')
            ->join('discuss_user as u on u.id = discuss_topic.uid')
            ->find();
        if (CollectionModel::instance()->isCollected(session('uid'), $tid, CollectionModel::topicCollectionType)) {
            $topicInfo['collected'] = 1;
        } else {
            $topicInfo['collected'] = 0;
        }
        return $topicInfo;
    }

    /**
     * 根据tid获得主题简单信息
     * @param [int or array] tid
     * @return array topics
     */
    public function getTopicByTids($tids) {
        if (empty($tids)) {
            return array();
        }
        $tidStr = implode('\',\'', $tids);
        $tidStr = '\'' . $tidStr . '\'';
        $sql = 'discuss_topic.id IN(' . $tidStr . ')';
        $topics['lists'] = $this
            ->getDao()
            ->where($sql)
            ->join('discuss_node as n on n.id = discuss_topic.node_id')
            ->join('discuss_user as u on u.id = discuss_topic.uid')
            ->field('publish_time,title,imgpath,discuss_topic.id as tid,comments,node_name,user_name,last_comment_user')
            ->order('discuss_topic.publish_time desc')
            ->select();
        return $topics;
    }

    /**
     * 检查tid是否存在
     */
    public function checkTid($tid) {
        $data = $this->queryOne(array('id' => $tid), array('id'));
        return !empty($data);
    }

    /**
     * 根据分类获取相应主题
     * @param  [type] $cat [description]
     * @return [type]      [description]
     */
    public function getTopicsByCat($categoryId) {
        $p = I('get.p') ? I('get.p') : 0;
        $count = $this->getDao()->where(array('cat_id' => $categoryId))->count();
        $limit = C('PAGE_SIZE');
        $Page = new \Think\Page($count, $limit);
        //获取分页数据
        $topics['lists'] = M('topic as t')->where(array('cat_id' => $categoryId))
            ->join('discuss_user as u on u.id = t.uid')
            ->field('publish_time,title,imgpath,comments,user_name,node_name,t.id
												as tid,t.hits as hits,last_comment_user,last_comment_time')
            ->join('discuss_node as n on n.id = t.node_id')
            ->page($p . ',' . $limit)
            ->order('t.last_comment_time desc')
            ->select();
        $show = $Page->show();
        if ($Page->totalPages > 1) {
            $topics['show'] = $show;
        } else {
            $topics['show'] = null;
        }
        return $topics;
    }

    /**
     * 根据节点获取主题
     * @param  string $nodeName [description]
     * @return [type]           [description]
     */
    public function getTopicsByNode($nodeName = '') {
        $p = I('get.p') ? I('get.p') : 0;
        $limit = C('PAGE_SIZE');
        $count = M('node as n')->join('discuss_topic as t on t.node_id = n.id')
            ->where(array('node_name' => $nodeName))
            ->count();
        $Page = new \Think\Page($count, $limit);
        $topics['lists'] = M('Node as n')->where(['n.node_name' => $nodeName])
            ->join('discuss_topic as t on t.node_id = n.id')
            ->join('discuss_user as u on u.id = t.uid')
            ->field('publish_time,title,imgpath,comments,user_name,node_name,t.id as tid,t.hits
						 		as hits,last_comment_user,last_comment_time')
            ->page($p . ',' . $limit)
            ->order('t.last_comment_time desc')
            ->select();
        $show = $Page->show();
        if ($Page->totalPages > 1) {
            $topics['show'] = $show;
        } else {
            $topics['show'] = null;
        }
        return $topics;
    }

    /**
     * 根据用户名获取主题
     * @param  string $username [description]
     * @return [array] topics           [description]
     */
    public function getTopicsByUser($username, $limit = '') {
        $topics['lists'] = M('user as u')->where(array('user_name' => $username))
            ->join('discuss_topic as t on t.uid = u.id')
            ->join('discuss_node as n on n.id = t.node_id')
            ->field('publish_time,title,imgpath,comments,node_name,user_name,t.id as tid,t.hits as hits,last_comment_user')
            ->order('t.publish_time desc')
            ->limit('0,' . $limit)
            ->select();
        return $topics;
    }

    /**
     * 根据用户ID获取主题
     */
    public function getTopicsByUserIds($uids) {
        if (empty($uids)) {
            return array();
        }
        $uidStr = implode('\',\'', $uids);
        $uidStr = '\'' . $uidStr . '\'';
        $sql = 'uid IN(' . $uidStr . ')';
        $topics['lists'] = M('Topic as t')->where($sql)
            ->join('discuss_user as u on u.id = uid')
            ->join('discuss_node as n on n.id = node_id')
            ->field('publish_time,title,u.imgpath as imgpath,comments,n.node_name as
										node_name,u.user_name as user_name,t.id as tid,last_comment_user')
            ->order('publish_time desc')
            ->select();
        return $topics;
    }

    /**
     * 触发更新
     */
    public function addTrigger($nodeId) {
        if (!NodeModel::instance()->incTopicNum($nodeId)) {
            return false;
        }
        UserModel::instance()->incSiteInfoKey('topic_num');
        return true;
    }

    /**
     * 根据tid获取字段信息
     */
    public function getFieldByTid($tid, $fields) {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        $result = $this->getDao()
            ->where(array('id' => $tid))
            ->field($fields)
            ->find();
        return $result;
    }

    /**
     * 收藏主题
     */
    public function collectTopic($tid, $uid) {
        if (CollectionModel::instance()->collect($uid, $tid, CollectionModel::topicCollectionType)) {
            UserModel::instance()->incTopics($uid);
            return true;
        }
        return false;
    }

    /**
     * 取消收藏主题
     */
    public function removeColTopic($tid, $uid) {
        if (CollectionModel::instance()->cancelCollect($uid, $tid, CollectionModel::topicCollectionType)) {
            UserModel::instance()->decTopics($uid);
            return true;
        }
        return false;
    }

    /**
     * 通过用户ID取得用户收藏的主题
     */
    public function getColTopicByUid($uid) {
        return CollectionModel::instance()->getCollection($uid,
            CollectionModel::topicCollectionType);
    }

    public function incComments($tid) {
        return $this->getDao()->where(array('id' => $tid))->setInc('comments', 1);
    }

    public function decComments($tid) {
        return $this->getDao()->where(array('id' => $tid))->setDec('comments', 1);
    }
}