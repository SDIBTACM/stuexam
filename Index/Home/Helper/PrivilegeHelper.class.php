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
        return !empty(session('administrator'));
    }

    public static function isCreator() {
        return self::isSuperAdmin() || !empty(session('contest_creator'));
    }

    public static function isProblemSetter() {
        return self::isSuperAdmin() || !empty(session('problem_editor'));
    }

    public static function isExamOwner($creatorId) {
        return self::isSuperAdmin() || $creatorId == $_SESSION['user_id'];
    }
}
