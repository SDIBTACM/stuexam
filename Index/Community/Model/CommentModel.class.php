<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:22
 */

namespace Community\Model;


use Teacher\Model\GeneralModel;

class CommentModel extends GeneralModel
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

    /**
     * 根据tid获取相应评论
     * @param  [type] $tid [description]
     * @return [type]      [description]
     */
    public function getCommentByTid($tid) {
        $commentInfo = $this
            ->where(array('tid' => $tid))
            ->field('user_name,content,publish_time,imgpath,discuss_comment.id as cid,u.id as cuid')
            ->join('discuss_user as u on u.id = discuss_comment.uid')
            ->order('publish_time asc')//按照回复时间正序排列
            ->select();
        return $commentInfo;
    }

    /* 根据用户名获取评论
    * @param  string $username [description]
    * @return [type]           [description]
    */
    public function getCommentByUser($username, $limit = '') {
        $comments['lists'] = M('user as u')->where(array('u.user_name' => $username))
            ->join('discuss_comment as c on c.uid = u.id')
            ->join('discuss_topic as t on t.id = c.tid')
            ->join('discuss_user as u1 on u1.id = t.uid')
            ->field('tid,c.publish_time as publish_time,c.content as content,t.title as title,u1.user_name as user_name')
            ->order('c.publish_time desc')
            ->limit('0,' . $limit)
            ->select();
        return $comments;
    }
}