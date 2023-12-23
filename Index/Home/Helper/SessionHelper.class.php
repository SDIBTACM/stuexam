<?php

namespace Home\Helper;

/**
 * 统一获取 session 中绑定信息的地方，防止后续 session 名称修改需要关联修改很多地方
 * Class SessionHelper
 *
 * @package \Home\Helper
 */
class SessionHelper {

    public static function getUserId() {
        return session("user_id");
    }

    public static function getAdministrator() {
        return session("administrator");
    }

    public static function getContestCreator() {
        return session("contest_creator");
    }

    public static function getProblemEditor() {
        return session("problem_editor");
    }

    public static function getCsrfGetKey() {
        return $_SESSION['getkey'];
    }

    public static function getCsrfPostKey() {
        return $_SESSION['postkey'];
    }

    public static function setCsrfGetKey($value) {
        $_SESSION['getkey'] = $value;
    }

    public static function setCsrfPostKey($value) {
        $_SESSION['postkey'] = $value;
    }
}
