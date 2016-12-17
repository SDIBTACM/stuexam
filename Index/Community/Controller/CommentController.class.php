<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:25
 */

namespace Community\Controller;


use Community\Model\CommentModel;
use Community\Model\TopicModel;
use Community\Model\UserModel;

class CommentController extends TemplateController
{
    public function add() {
        if (IS_AJAX) {
            $data['tid'] = I('post.tid', '', 'intval');
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
                case 1:            //回复
                    $_toUser = UserModel::instance()->getById($data['to_uid'], array('user_name'));
                    $toUser = $_toUser['user_name'];
                    $userUrl = U('Community/User/info', array('member' => $toUser));
                    $link = "<a href='$userUrl' title=''>@{$toUser}</a>: ";

                    $data['content'] = $link . $data['content'];
                    $data['type'] = '回复';
                    break;
                default:
                    $this->ajaxReturn('no');
                    break;
            }
            if (CommentModel::instance()->insertData($data)) {
                $this->trigger($data['tid'], $data['publish_time']);
                $this->ajaxReturn('yes');
            } else {
                $this->ajaxReturn('no');
            }
        } else {
            $this->ajaxReturn('no');
        }
    }

    /**
     * 触发更新
     */
    public function trigger($tid, $last_comment_time) {
        UserModel::instance()->incSiteInfoKey('comment_num');
        TopicModel::instance()->incComments($tid);
        TopicModel::instance()->updateById($tid, array(
            'last_comment_user' => $this->userInfo['user_id'],
            'last_comment_time' => $last_comment_time
        ));
    }
}