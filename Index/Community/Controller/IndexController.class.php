<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:44
 */

namespace Community\Controller;


use Community\Model\NodeModel;
use Community\Model\TopicModel;
use Constant\Constants\DiscussCategory;

class IndexController extends TemplateController
{
    function __construct() {
        parent::__construct();
    }

    /**
     * 主页
     */
    public function index() {
        $catName = I('get.cat');
        $category = DiscussCategory::getByName($catName);
        if (!empty($catName) && $category == null) {
            $this->error('传输参数错误');
        }
        if ($catName == null) {
            $categoryId = 1;
        } else {
            $categoryId = $category->getId();
        }

        $categories = DiscussCategory::getAllCategoryName();  //获取导航栏分类
        $nodes = NodeModel::instance()->getNodeByCatId($categoryId);      //根据分类获取节点
        $topics = TopicModel::instance()->getTopicsByCat($categoryId);      //根据分类获取文章
        $this->assign('categories', $categories);
        $this->assign('nodes', $nodes);
        $this->assign('activeCat', $catName);                   //当前分类
        $this->assign('topics', $topics);
        $this->showSidebar('all');//展示侧边栏
        $this->display();
    }
}