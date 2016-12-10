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
    protected function getDao() {
        // TODO: Implement getDao() method.
    }

    protected function getTableName() {
        // TODO: Implement getTableName() method.
    }

    protected function getTableFields() {
        // TODO: Implement getTableFields() method.
    }

    protected function getPrimaryId() {
        // TODO: Implement getPrimaryId() method.
    }

    public function getHotNodes() {
        $nodes = $this->field('node_name')->order('hits desc')->limit(10)->select();
        return $nodes;
    }

    /**
     * 获取全部节点
     * @return [type] [description]
     */
    public function getAllNodes() {
        $nodes = $this->field('id,node_name')->select();
        return $nodes;
    }

    /**
     * 根据分类名获取节点
     * @param  [type] $catName [description]
     * @return [type]          [description]
     */
    public function getNodeByCatName($catName = '') {
        if ($catName == '') {
            $catId = $this->getField('id');
            $nodes = $this->where(array('cid' => $catId))
                ->field('node_name')
                ->select();
            return $nodes;
        } else {
            $nodes = $this->field('node_name')
                ->join('discuss_category as c on c.id = cid')
                ->where(array('cat_name' => $catName))
                ->select();
            return $nodes;
        }
    }

    /**
     * 获取节信息
     * @param  [type] $node [description]
     * @return [type]       [description]
     */
    public function getNodeInfo($node) {
        $data = $this->field('id,desc,logo_path,topic_num,desc')
            ->where(array('node_name' => $node))
            ->select()[0];
        return $data;
    }

    /**
     * 根据分类id获取分类节点id
     * @param  [type] $nodeId [description]
     * @return [type]         [description]
     */
    public function getCatIdByNodeId($nodeId) {
        $catId = $this->where(array('id' => $nodeId))->getField('cid');
        return $catId;
    }

    /**
     * 根据tid获取其节点名
     * @param  [type] $tid [description]
     * @return [type]      [description]
     */
    public function getNodeByTid($tid) {
        $node = $this->join('discuss_topic as t on t.node_id = discuss_node.id')
            ->where(array('t.id' => $tid))
            ->getField('node_name');
        return $node;
    }
}