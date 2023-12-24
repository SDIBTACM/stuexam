<?php

namespace Home\Helper;

/**
 * 统一获取 session 中绑定信息的地方，防止后续 session 名称修改需要关联修改很多地方
 * 修改 session key统一到配置文件中 Index/Common/Conf/config.php 修改
 * Class SessionHelper
 *
 * @package \Home\Helper
 */
class SessionHelper {

    public static function getUserId() {
        return session(C("EXAM_SESSION_KEY.USER_ID"));
    }

    public static function getAdministrator() {
        return session(C("EXAM_SESSION_KEY.ADMINISTRATOR"));
    }

    public static function getContestCreator() {
        return session(C("EXAM_SESSION_KEY.CONTEST_CREATOR"));
    }

    public static function getProblemEditor() {
        return session(C("EXAM_SESSION_KEY.PROBLEM_EDITOR"));
    }

    public static function getCsrfGetKey() {
        return $_SESSION[C("EXAM_SESSION_KEY.GET_KEY")];
    }

    public static function getCsrfPostKey() {
        return $_SESSION[C("EXAM_SESSION_KEY.POST_KEY")];
    }

    public static function setCsrfGetKey($value) {
        $_SESSION[C("EXAM_SESSION_KEY.GET_KEY")] = $value;
    }

    public static function setCsrfPostKey($value) {
        $_SESSION[C("EXAM_SESSION_KEY.POST_KEY")] = $value;
    }
}
