<?php
/**
 *
 * Created by Dream.
 * User: Boxjan
 * Datetime: 18-9-22 下午3:54
 */

namespace Home\Controller;


use Basic\Log;
use Teacher\Model\ExamBaseModel;

class InterceptorController extends TemplateController
{
    public function _initialize() {
        parent::_initialize();
        $this->checkLoginIp();
    }

    private function checkLoginIp() {
        if ($this->isTeacher()) return true;
        $allowLoginIpList = $this->getAllowLoginIpList();
        Log::debug("{}", $allowLoginIpList);
        if (0 == count($allowLoginIpList)) return true;

        foreach ($allowLoginIpList as $item) {
            $item = explode("/", $item);
            if (compareIpWithSubnetMask($_SERVER['REMOTE_ADDR'], $item[0], $item[1])) return true;
        }
        return $this->alertError("You not allow login from this ip, Please contact to your teacher");
    }

    private function getAllowLoginIpList() {
        $res = ExamBaseModel::instance()->getById(I('get.eid'), array('allow_login_ip_list'));
        $arr = json_decode($res['allow_login_ip_list']);
        return !is_array($arr) ? array() : $arr;
    }
}
