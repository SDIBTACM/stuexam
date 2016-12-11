<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 9/28/16 00:17
 */

namespace Community\Controller;


use Community\Model\NodeModel;
use Community\Model\UserModel;

class TemplateController extends \Home\Controller\TemplateController
{
    public function _initialize() {
        parent::_initialize();
        $this->initDiscussLoginUser();
    }

    private function initDiscussLoginUser() {
        $uid = session('uid');
        if (empty($uid)) {

        }
        $this->userInfo['uid'] = $uid;
    }

    public function showSidebar($mode = '') {
        $sidebar['userInfo'] = UserModel::instance()->getSidebarUserInfo($this->userInfo['uid']);//用户信息
        if ($mode == 'all') {
            $sidebar['hotNodes'] = NodeModel::instance()->getHotNodes();//热门节点
            $sidebar['siteInfo'] = UserModel::instance()->getSiteInfo();// 站点信息
        }
        $this->assign('sidebar', $sidebar);
    }
}