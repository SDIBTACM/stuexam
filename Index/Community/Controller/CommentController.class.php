<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:25
 */

namespace Community\Controller;


use Community\Model\TopicModel;

class CommentController extends TemplateController
{
    public function add() {
        if (IS_AJAX) {
            $data['tid'] = I('post.tid', '', 'intval');
            $Topic = D('Topic');
            if (!TopicModel::instance()->checkTid($data['tid'])) {
                $this->ajaxReturn('no');
            }
            $data['content'] = I('post.content', '', 'trim,htmlspecialchars');
            if (empty($data['content'])) {
                $this->ajaxReturn('no');
            }
            $data['publish_time'] = date('Y-m-d H:i:s', time());
            $data['uid'] = $this->userInfo['uid'];
            $data['to_uid'] = I('post.toUid', 0, 'intval');
            switch (I('post.type', 0, 'intval')) {
                case 0:                //评论
                    $data['type'] = '评论';
                    break;
                case 1:                //回复
                    $data['type'] = '回复';
                    break;
                default:
                    $this->ajaxReturn('no');
                    break;
            }
            if (M('Comment')->add($data)) {
                $this->trigger($Topic, $data);
                $this->ajaxReturn('yes');
            } else {
                $this->ajaxReturn('no');
            }
        }
    }

    /**
     * 触发更新
     */
    public function trigger($Topic, $data) {
        M('siteinfo')->where(['id' => 1])->setInc('comment_num');
        $Topic->where(['id' => $data['tid']])->setInc('comments');
        $Topic->last_comment_user = $this->userInfo['user_id'];
        $Topic->last_comment_time = $data['publish_time'];
        $Topic->where(['id' => $data['tid']])->save();
    }
}