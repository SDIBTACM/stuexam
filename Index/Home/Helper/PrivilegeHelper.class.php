<?php

namespace Home\Helper;

/**
 * Class PrivilegeHelper
 *
 * @package \Home\Helper
 */
class PrivilegeHelper {

    public static function isTeacher() {
        return self::isSuperAdmin() || self::isCreator() || self::isProblemSetter();
    }

    public static function isSuperAdmin() {
        return !empty(SessionHelper::getAdministrator());
    }

    public static function isCreator() {
        return self::isSuperAdmin() || !empty(SessionHelper::getContestCreator());
    }

    public static function isProblemSetter() {
        return self::isSuperAdmin() || !empty(SessionHelper::getProblemEditor());
    }

    public static function isExamOwner($creatorId) {
        return self::isSuperAdmin() || $creatorId == SessionHelper::getUserId();
    }
}
