<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 11/12/2016 01:04
 */

namespace Community\Controller;


use Community\Model\CommentModel;
use Community\Model\TopicModel;
use Community\Model\UserModel;

class UserController extends TemplateController
{
    function __construct() {
        parent::__construct();
    }

    /**
     * AJAX检查占用Email接口
     */
    public function checkEmail() {
        //$User = new \Home\Model\UserModel(
        $email = I('post.email');
        echo UserModel::instance()->checkEmail($email);
    }

    /**
     * 用户登出
     */
    public function logout() {
        session('user', null);
        session('uid', null);
        $this->redirect("User/login", '', 0);
    }

    /**
     * 用户信息页
     */
    public function info($member = null) {
        //$User = new \Home\Model\UserModel();
        if (empty($member)) {
            $member = $this->userInfo['user_id'];
        }
        $data = UserModel::instance()->getUserInfo($member);
        if ($data) {
            $topics = TopicModel::instance()->getTopicsByUser($member, 5);//根据用户名获取文章
            $comments = CommentModel::instance()->getCommentByUser($member, 10); //获取10条最新评论
            $this->assign('topics', $topics);
            $this->assign('comments', $comments);
            $this->assign('data', $data);
            $this->showSidebar();//展示侧边栏
            $this->display();
        } else {
            $this->error('用户不存在');
        }
    }

    /**
     * 用户特别关注列表页
     */
    public function attentions() {
        $data = UserModel::instance()->getUserInfo(I('session.user'));
        $this->assign('data', $data);
        $attentions = UserModel::instance()->getUserAttentions();
        $topics = TopicModel::instance()->getTopicsbyUserID($attentions);
        $this->assign('topics', $topics);
        $this->showSidebar();//展示侧边栏
        $this->display();
    }

    /**
     * AJAX用户加入特别关注
     */
    public function add_attention() {
        if (!IS_POST) {
            $this->error('非法访问');
        } else {
            $targetUserID = I('post.userID');
            if ($targetUserID) {
                if (UserModel::instance()->addAttention($targetUserID)) {
                    $data['status'] = 1; //成功
                    $this->ajaxReturn($data);
                } else {
                    $data['status'] = 0; //失败
                    $this->ajaxReturn($data);
                }
            } else {
                $this->error('非法访问');
            }
        }
    }

    /**
     * AJAX用户取消特别关注
     */
    public function remove_attention() {
        if (!IS_POST) {
            $this->error('非法访问');
        } else {
            $targetUserID = I('post.userID');
            if ($targetUserID) {
                if (UserModel::instance()->removeAttention($targetUserID)) {
                    $data['status'] = 1; //成功
                    $this->ajaxReturn($data);
                } else {
                    $data['status'] = 0; //失败
                    $this->ajaxReturn($data);
                }
            } else {
                $this->error('非法访问');
            }
        }
    }

    /**
     * 用户所有主题列表页
     */
    public function topic($member) {
        $data = UserModel::instance()->getUserInfo($member);
        if ($data) {
            $topics = TopicModel::instance()->getTopicsByUser($member);//根据用户名获取文章
            $this->assign('topics', $topics);
            $this->assign('data', $data);
            $this->showSidebar();//展示侧边栏
            $this->display();
        } else {
            $this->error('此用户不存在！');
        }

    }

    /**
     * 用户所有回复列表页
     */
    public function reply($member) {
        //$User = new \Home\Model\UserModel();
        $data = UserModel::instance()->getUserInfo($member);
        if ($data) {
            $this->assign('data', $data);
            $comments = CommentModel::instance()->getCommentByUser($member);
            $this->assign('comments', $comments);
            $this->showSidebar();//展示侧边栏
            $this->display();
        } else {
            $this->error('此用户不存在！');
        }
    }

    /**
     * 用户主题收藏列表页
     */
    public function coltopic() {
        $data = UserModel::instance()->getUserInfo($this->userInfo['user_id']);
        $this->assign('data', $data);
        $uid = $this->userInfo['uid'];
        $coltopic_tid = TopicModel::instance()->getColTopicByID($uid);
        $topics = TopicModel::instance()->getTopicByTID($coltopic_tid);
        $this->assign('topics', $topics);
        $this->showSidebar();//展示侧边栏
        $this->display();
    }
}