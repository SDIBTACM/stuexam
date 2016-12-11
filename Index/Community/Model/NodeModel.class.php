<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:47
 */

namespace Community\Model;


use Teacher\Model\GeneralModel;

class NodeModel extends GeneralModel
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
        return "node";
    }

    protected function getTableFields() {
        // TODO: Implement getTableFields() method.
    }

    protected function getPrimaryId() {
        return 'id';
    }

    public function getHotNodes() {
        $where = array(
            'id' => array('gt', 0),
            'order' => 'hits desc',
            'limit' => 10
        );
        return $this->queryData($where, array('node_name'));
    }

    /**
     * 获取全部节点
     */
    public function getAllNodes() {
        return $this->queryAll(array('id' => array('gt', 0)), array('id','node_name'));
    }

    /**
     * 根据分类名获取节点
     */
    public function getNodeByCatId($categoryId) {
        return $this->queryAll(array('cid' => $categoryId), array('node_name'));
    }

    /**
     * 获取节信息
     */
    public function getNodeInfo($nodeName) {
        return $this->queryAll(array('node_name' => $nodeName),
            array('id', 'desc', 'logo_path', 'topic_num', 'desc'));
    }

    /**
     * 根据分类id获取分类节点id
     * @param $nodeId
     * @return mixed
     */
    public function getCatIdByNodeId($nodeId) {
        $catId = $this->getDao()->where(array('id' => $nodeId))->getField('cid');
        return $catId;
    }

    /**
     * 根据tid获取其节点名
     */
    public function getNodeByTid($tid) {
        $node = $this->getDao()->join('discuss_topic as t on t.node_id = discuss_node.id')
            ->where(array('t.id' => $tid))
            ->getField('node_name');
        return $node;
    }

    public function incTopicNum($nodeId) {
        return $this->getDao()->where(array('id' => $nodeId))->setInc('topic_num');
    }

    public function decTopicNum($nodeId) {
        return $this->getDao()->where(array('id' => $nodeId))->setDec('topic_num');
    }
}