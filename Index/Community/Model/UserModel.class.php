<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:47
 */

namespace Community\Model;


use Teacher\Model\GeneralModel;

class UserModel extends GeneralModel
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
        return "user";
    }

    protected function getTableFields() {
        // TODO: Implement getTableFields() method.
    }

    protected function getPrimaryId() {
        // TODO: Implement getPrimaryId() method.
    }

    public function getSidebarUserInfo() {
        $uid = session('uid');
        $data = $this->getDao()->where(array('id' => $uid))
            ->field('imgpath,attentions,topics,wealth,nodes')
            ->select()[0];
        $data['notifications'] = M('reply')->where(array('to_uid' => $uid, 'is_read' => '否'))
            ->count();
        return $data;
    }
}