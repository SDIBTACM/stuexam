<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 12/11/2016 11:03
 */

namespace Constant\Constants;

/**
 * 章基础枚举信息
 * Class Chapter
 * @package Constant\Constants
 */
class Chapter
{
    private $id; // 不可以变

    private $name;

    private $priority;

    private static $chapters = null;

    private function __construct($id, $name, $priority) {
        $this->id = $id;
        $this->name = $name;
        $this->priority = $priority;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getPriority() {
        return $this->priority;
    }

    public static function initConstant() {
        if (self::$chapters == null) {
            self::$chapters = array(
                new Chapter(1, "第1章: 计算机基础知识", 1),
                new Chapter(2, "第2章: C程序和C编译器简介", 2),
                new Chapter(3, "第3章: C编程基础知识", 3),
                new Chapter(4, "第4章: 顺序结构程序设计", 4),
                new Chapter(5, "第5章: 选择结构程序设计", 5),
                new Chapter(6, "第6章: 循环结构程序设计", 6),
                new Chapter(7, "第7章: 函数", 7),
                new Chapter(8, "第8章: 变量的作用域和存储类别", 8),
                new Chapter(16, "第9章: 编译预处理", 9),
                new Chapter(9, "第10章: 用指针变量访问变量", 10),
                new Chapter(10, "第11章: 数组", 11),
                new Chapter(11, "第12章: 用指针变量访问下标变量", 12),
                new Chapter(12, "第13章: 指针综述", 13),
                new Chapter(13, "第14章: 数据类型的自定义", 14),
                new Chapter(14, "第15章: 位运算", 15),
                new Chapter(15, "第16章: 文件", 16)
            );
        }
    }

    public static function getConstant() {
        self::initConstant();
        return self::$chapters;
    }

    public static function getById($id) {
        self::initConstant();
        foreach (self::$chapters as $res) {
            if ($res->getId() == $id) {
                return $res;
            }
        }
        return null;
    }

    public static function getIdBiggerPriority($priority) {
        self::initConstant();
        $ans = array();
        foreach (self::$chapters as $res) {
            if ($res->getPriority() > $priority) {
                $ans[] = $res->getId();
            }
        }
        return $ans;
    }
}