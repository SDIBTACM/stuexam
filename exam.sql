# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.5.58-0ubuntu0.14.04.1-log)
# Database: jol
# Generation Time: 2017-12-08 02:41:43 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table ex_choose
# ------------------------------------------------------------

CREATE TABLE `ex_choose` (
  `choose_id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text,
  `ams` varchar(255) NOT NULL DEFAULT '',
  `bms` varchar(255) NOT NULL DEFAULT '',
  `cms` varchar(255) NOT NULL DEFAULT '',
  `dms` varchar(255) NOT NULL DEFAULT '',
  `answer` char(1) NOT NULL DEFAULT '',
  `addtime` datetime DEFAULT NULL,
  `creator` varchar(255) NOT NULL DEFAULT '',
  `easycount` tinyint(4) NOT NULL DEFAULT '0' COMMENT '难易程度1-10',
  `isprivate` tinyint(4) NOT NULL DEFAULT '0' COMMENT '权限类型:1公开;2私有;3隐藏',
  `question_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '题目版本类型0旧版1新版非考试2新版考试',
  `private_code` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`choose_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table ex_fill
# ------------------------------------------------------------

CREATE TABLE `ex_fill` (
  `fill_id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text,
  `answernum` tinyint(4) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  `creator` varchar(255) NOT NULL DEFAULT '',
  `easycount` tinyint(4) NOT NULL DEFAULT '0' COMMENT '难易程度',
  `kind` tinyint(4) NOT NULL DEFAULT '1' COMMENT '填空题类型',
  `isprivate` tinyint(4) NOT NULL DEFAULT '0' COMMENT '权限类型:1公开;2私有;3隐藏',
  `question_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '题目版本类型0旧版1新版非考试2新版考试',
  `private_code` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`fill_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table ex_judge
# ------------------------------------------------------------

CREATE TABLE `ex_judge` (
  `judge_id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text,
  `answer` char(1) NOT NULL DEFAULT '',
  `addtime` datetime DEFAULT NULL,
  `creator` varchar(255) NOT NULL DEFAULT '',
  `easycount` tinyint(4) NOT NULL DEFAULT '0' COMMENT '难易程度1-10',
  `isprivate` tinyint(4) NOT NULL DEFAULT '0' COMMENT '权限类型:1公开;2私有;3隐藏',
  `question_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '题目版本类型0旧版1新版非考试2新版考试',
  `private_code` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`judge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table ex_key_point
# ------------------------------------------------------------

CREATE TABLE `ex_key_point` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `chapter_id` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `chapterId` (`chapter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table ex_privilege
# ------------------------------------------------------------

CREATE TABLE `ex_privilege` (
  `user_id` varchar(20) NOT NULL DEFAULT '',
  `rightstr` varchar(30) NOT NULL DEFAULT '',
  `randnum` int(11) NOT NULL DEFAULT '0',
  `extrainfo` int(11) NOT NULL DEFAULT '0' COMMENT '扩展字段,记录学生倒计时',
  PRIMARY KEY (`user_id`,`rightstr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='考试权限表';



# Dump of table ex_question_point
# ------------------------------------------------------------

CREATE TABLE `ex_question_point` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL DEFAULT '0' COMMENT '题目编号',
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '题目类型',
  `chapter_id` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '所属章节,冗余字段',
  `point_id` int(11) NOT NULL DEFAULT '0' COMMENT '知识点编号',
  `point_parent_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '上级知识点编号,冗余',
  PRIMARY KEY (`id`),
  KEY `pid_type` (`question_id`,`type`),
  KEY `chapterId` (`chapter_id`),
  KEY `pointId` (`point_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='题目的知识点关联表';



# Dump of table ex_stuanswer
# ------------------------------------------------------------

CREATE TABLE `ex_stuanswer` (
  `user_id` varchar(20) NOT NULL DEFAULT '',
  `exam_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '题目类型:1:选择题;2判断题;3:填空题;4:编程题',
  `question_id` int(11) NOT NULL COMMENT '题目id',
  `answer_id` tinyint(4) NOT NULL DEFAULT '1' COMMENT '答案序号',
  `answer` varchar(255) DEFAULT NULL COMMENT '答案内容',
  PRIMARY KEY (`exam_id`,`user_id`,`type`,`question_id`,`answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='考生考试题目答案关联表';



# Dump of table ex_student
# ------------------------------------------------------------

CREATE TABLE `ex_student` (
  `user_id` varchar(20) NOT NULL DEFAULT '',
  `exam_id` int(11) NOT NULL COMMENT '考试id',
  `score` float NOT NULL DEFAULT '-1' COMMENT '总分',
  `choosesum` float NOT NULL DEFAULT '-1' COMMENT '选择题总分',
  `judgesum` float NOT NULL DEFAULT '-1' COMMENT '判断题总分',
  `fillsum` float NOT NULL DEFAULT '-1' COMMENT '填空题总分',
  `programsum` float NOT NULL DEFAULT '-1' COMMENT '编程题总分',
  PRIMARY KEY (`user_id`,`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='考生成绩表';



# Dump of table exam
# ------------------------------------------------------------

CREATE TABLE `exam` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '考试名称',
  `start_time` datetime DEFAULT NULL COMMENT '考试开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '考试结束时间',
  `creator` varchar(255) NOT NULL DEFAULT '' COMMENT '创建者',
  `choosescore` float NOT NULL DEFAULT '2' COMMENT '选择题每题分值',
  `judgescore` float NOT NULL DEFAULT '1' COMMENT '判断题每题分值',
  `fillscore` float NOT NULL DEFAULT '1' COMMENT '填空题每空分值',
  `prgans` float NOT NULL DEFAULT '4' COMMENT '程序写答案每空分值',
  `prgfill` float NOT NULL DEFAULT '5' COMMENT '程序填空每空分值',
  `programscore` float NOT NULL DEFAULT '10' COMMENT '程序设计每题分值',
  `isvip` char(1) NOT NULL DEFAULT 'Y' COMMENT '是否限制当天登陆次数',
  `visible` char(1) NOT NULL DEFAULT 'Y' COMMENT '比赛是否被删除',
  `isprivate` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是私有考试',
  PRIMARY KEY (`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='考试列表';



# Dump of table exp_question
# ------------------------------------------------------------

CREATE TABLE `exp_question` (
  `exp_qid` int(11) NOT NULL AUTO_INCREMENT COMMENT '考题id',
  `exam_id` int(11) NOT NULL COMMENT '考试编号',
  `question_id` int(11) NOT NULL COMMENT '题目id',
  `type` tinyint(4) NOT NULL COMMENT '题目类型:1:选择题;2判断题;3:填空题;4:编程题',
  PRIMARY KEY (`exp_qid`),
  UNIQUE KEY `examId_qId_type` (`exam_id`,`question_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='考题和考试关联表';



# Dump of table fill_answer
# ------------------------------------------------------------

CREATE TABLE `fill_answer` (
  `fill_id` int(11) NOT NULL COMMENT '填空题id',
  `answer_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '填空题空数id',
  `answer` varchar(255) NOT NULL DEFAULT '' COMMENT '每空答案',
  PRIMARY KEY (`fill_id`,`answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='填空题每空的答案关联表';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
