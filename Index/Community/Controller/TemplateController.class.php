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
    }

    public function showSidebar($mode) {
        $sidebar['userInfo'] = UserModel::instance()->getSidebarUserInfo();//用户信息
        if ($mode == 'all') {
            $sidebar['hotNodes'] = NodeModel::instance()->getHotNodes();//热门节点
            //$sidebar['siteInfo'] = D('Index')->getSiteInfo();// TODO 站点信息
        }
        $this->assign('sidebar', $sidebar);
    }
}