<?php
namespace Home\Controller;

use Home\Helper\PrivilegeHelper;
use Home\Helper\SessionHelper;
use Think\Controller;

class TemplateController extends Controller
{

    protected $userInfo = null;

    public $module = null;
    public $controller = null;
    public $action = null;

    protected $isNeedLogin = true;
    protected $isNeedFilterSql = false;

    public function _initialize() {
        header("Pragma: no-cache");
        // HTTP/1.0
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
        // HTTP/1.1

        $this->module = strtolower(MODULE_NAME);
        $this->controller = strtolower(CONTROLLER_NAME);
        $this->action = strtolower(ACTION_NAME);

        $this->initSqlInjectionFilter();
        $this->initLoginUserInfo();
    }

    private function initLoginUserInfo() {
        $userId = SessionHelper::getUserId();
        if (!empty($userId)) {
            // 目前只存userId,之后等新版会更新父类主要信息
            $this->userInfo['user_id'] = $userId;
        }
        if (empty($userId) && $this->isNeedLogin) {
            redirect('/loginpage.php', 1, 'Please Login First!!');
        }
    }

    private function initSqlInjectionFilter() {
        if (function_exists('sqlInjectionFilter') && $this->isNeedFilterSql) {
            sqlInjectionFilter();
        }
    }

    protected function alertError($errmsg, $url = '') {
        $url = empty($url) ? "window.history.back();" : "location.href=\"{$url}\";";
        echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        echo "<script>function Mytips(){alert('{$errmsg}');{$url}}</script>";
        echo "</head><body onload='Mytips()'></body></html>";
        exit;
    }

    protected function echoError($errmsg) {
        if (IS_AJAX) {
            echo $errmsg;
            exit(0);
        } else {
            $this->error($errmsg);
        }
    }

    protected function auto_display($view = null, $layout = true) {
        layout($layout);
        $this->display($view);
    }

    protected function zadd($name, $data) {
        $this->assign($name, $data);
    }

    protected function ZaddWidgets($widgets) {
        foreach ($widgets as $name => $data) {
            $this->zadd($name, $data);
        }
    }

    protected function isSuperAdmin() {
        return PrivilegeHelper::isSuperAdmin();
    }

    protected function isCreator() {
        return PrivilegeHelper::isCreator();
    }

    protected function isProblemSetter() {
        return PrivilegeHelper::isProblemSetter();
    }

    protected function isTeacher() {
        return PrivilegeHelper::isTeacher();
    }

    protected function ajaxCodeReturn($code, $message, $data = array()) {
        $return = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );
        $this->ajaxReturn($return, "JSON");
    }


    protected function getUserAgent() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) return null;
        return $_SERVER['HTTP_USER_AGENT'];
    }
}
