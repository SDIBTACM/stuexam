<?php
namespace Home\Controller;

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
        $userId = session('user_id');
        if (!empty($userId)) {
            // 目前只存userId,之后等新版会更新父类主要信息
            $this->userInfo['user_id'] = session('user_id');
        }
        if (empty($userId) && $this->isNeedLogin) {
            redirect('/JudgeOnline/loginpage.php', 1, 'Please Login First!!');
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
        $isadmin = session('administrator');
        return !empty($isadmin);
    }

    protected function isCreator() {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $isCreator = session('contest_creator');
        return !empty($isCreator);
    }

    protected function isProblemSetter() {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $isSetter = session('problem_editor');
        return !empty($isSetter);
    }

    protected function isTeacher() {
        if ($this->isSuperAdmin() || $this->isCreator() || $this->isProblemSetter()) {
            return true;
        } else {
            return false;
        }
    }
}
