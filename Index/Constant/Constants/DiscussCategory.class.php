<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 10/12/2016 21:17
 */

namespace Constant\Constants;


class DiscussCategory
{
    private $id;

    private $name;

    private static $category = null;

    private function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public static function initConstant() {
        if (self::$category == null) {
            self::$category = array(
                new DiscussCategory(1, "技术"),
                new DiscussCategory(2, "创意"),
                new DiscussCategory(3, "好玩"),
                new DiscussCategory(4, "工作"),
                new DiscussCategory(5, "问答"),
                new DiscussCategory(6, "系统")
            );
        }
    }

    public static function getConstant() {
        self::initConstant();
        return self::$category;
    }

    public static function getById($id) {
        self::initConstant();
        foreach (self::$category as $res) {
            if ($res->getId() == $id) {
                return $res;
            }
        }
        return null;
    }

    public static function getByName($name) {
        self::initConstant();
        foreach (self::$category as $res) {
            if (strcmp($res->getName(), $name) == 0) {
                return $res;
            }
        }
        return null;
    }
}