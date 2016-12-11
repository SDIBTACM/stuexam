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
        return 'id';
    }

    public function getSidebarUserInfo($uid) {
        $data = $this->queryOne(array('id' => $uid),
            array('imgpath', 'attentions', 'topics', 'wealth', 'nodes'));
        $data['notifications'] = CommentModel::instance()->getReplyCountByToUid($uid);
        return $data;
    }

    public function decTopics($uid) {
        return $this->getDao()->where(array('id' => $uid))->setDec('topics', 1);
    }

    public function incTopics($uid) {
        return $this->getDao()->where(array('id' => $uid))->setInc('topics', 1);
    }

    /**
     * AJAX检查占用Email
     * @param  string $email [description]
     * @return json
     */
    public function checkEmail($email) {

        $data = $this->getDao()->where("email='$email'")->find();
        if ($data) {
            $msg = array('occupied' => 1);
            return json_encode($msg);
        } else {
            $msg = array('occupied' => 0);
            return json_encode($msg);
        }

    }

    /**
     * 用户信息页 获取用户信息
     * @return array 获取的用户数据
     */
    public function getUserInfo($username, $loginUserId) {
        $data = $this->getDao()->where(array('user_name' => $username))
            ->field('id,url,resume,user_name,imgpath,gender,create_time')
            ->find();
        if ($data) {
            $attention = M('attention');
            if ($attention->where('uid=' . $loginUserId . ' AND atten_uid=' . $data['id'])->find()) {
                $data['attention'] = 1;
            } else {
                $data['attention'] = 0;
            }
        } else {
            $data['attention'] = 0;
        }
        return $data;
    }

    /**
     * 添加用户特别关注
     */
    public function addAttention($targetUserId, $loginUserId) {
        $attention = M('attention');
        $data['uid'] = $loginUserId;
        $data['atten_uid'] = $targetUserId;
        if ($attention->data($data)->add()) {
            $this->getDao()->where('id=' . $loginUserId)->setInc('attentions', 1);
            return true;
        }
        return false;
    }

    /**
     * 取消用户特别关注
     */
    public function removeAttention($targetUserId, $loginUserId) {
        $attention = M('attention');
        $data['uid'] = $loginUserId;
        $data['atten_uid'] = $targetUserId;
        if ($attention->where('uid=' . $loginUserId . ' AND atten_uid=' . $targetUserId)->delete()) {
            $this->getDao()->where('id=' . $loginUserId)->setDec('attentions', 1);
            return true;
        }
        return false;
    }

    /**
     * 获取用户的特别关注
     * @param
     * @return array [特别关注的用户UID数组]
     */
    public function getUserAttentions($uid) {
        $attention = M('attention');
        $attentions = $attention->where('uid=' . $uid)->getField('atten_uid', TRUE);
        return $attentions;
    }

    /**
     * 用户设置页 获取用户信息
     * @return array 获取的用户数据
     */
    public function getSettingUserInfo($uid) {
        $data['userInfo'] = $this->getDao()
            ->where(array('id' => $uid))
            ->field('url,resume,email,gender,imgpath,attentions,topics,wealth,nodes')
            ->find();
        $data['notifications'] = CommentModel::instance()->getReplyCountByToUid($uid);
        return $data;
    }

    public function incSiteInfoKey($key) {
        $name = 'site_' . $key;
        $value = F($name);
        if (empty($value)) {
            F($name, 1);
        } else {
            F($name, $value + 1);
        }
    }

    public function decSiteInfoKey($key) {
        $name = 'site_' . $key;
        $value = F($name);
        if (!empty($value)) {
            F($name, $value - 1);
        }
    }

    public function getSiteInfo() {
        $keyList = array(
            'comment_num', 'topic_num', 'member_num'
        );
        $values = array();
        foreach ($keyList as $key) {
            $values[$key] = intval(F('site_' . $key));
        }
        return $values;
    }
}