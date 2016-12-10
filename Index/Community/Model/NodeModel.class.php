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
        // TODO: Implement getTableName() method.
        return "node";
    }

    protected function getTableFields() {
        // TODO: Implement getTableFields() method.
    }

    protected function getPrimaryId() {
        // TODO: Implement getPrimaryId() method.
    }

    public function getHotNodes() {
        $nodes = $this->getDao()->field('node_name')->order('hits desc')->limit(10)->select();
        return $nodes;
    }

    /**
     * 获取全部节点
     */
    public function getAllNodes() {
        $nodes = $this->getDao()->field('id,node_name')->select();
        return $nodes;
    }

    /**
     * 根据分类名获取节点
     */
    public function getNodeByCatName($categoryId) {
        $nodes = $this->getDao()->field('node_name')
            ->where(array('cid' => $categoryId))
            ->select();
        return $nodes;
    }

    /**
     * 获取节信息
     * @param  [type] $node [description]
     * @return [type]       [description]
     */
    public function getNodeInfo($node) {
        $data = $this->getDao()
            ->field('id,desc,logo_path,topic_num,desc')
            ->where(array('node_name' => $node))
            ->find();
        return $data;
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
}