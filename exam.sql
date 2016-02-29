DROP TABLE IF EXISTS `exam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam` (
	`exam_id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) DEFAULT NULL,
	`start_time` datetime DEFAULT NULL,
  	`end_time` datetime DEFAULT NULL,
  	`creator` varchar(255) DEFAULT NULL,
  	`choosescore` tinyint(4) DEFAULT '2',
  	`judgescore` tinyint(4) DEFAULT '1',
  	`fillscore` tinyint(4) DEFAULT '1',
  	`prgans` tinyint(4) DEFAULT '4',
  	`prgfill` tinyint(4) DEFAULT '5',
  	`programscore` tinyint(4) DEFAULT '10',
  	`isvip` char(1) DEFAULT 'Y',
  	`visible` char(1) DEFAULT 'Y',
	PRIMARY KEY (`exam_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ex_choose`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_choose` (
	`choose_id` int(11) NOT NULL AUTO_INCREMENT,
	`question` text,
	`ams` varchar(255) DEFAULT NULL,
	`bms` varchar(255) DEFAULT NULL,
	`cms` varchar(255) DEFAULT NULL,
	`dms` varchar(255) DEFAULT NULL,
	`answer` char(1) DEFAULT NULL,
	`point` varchar(100) DEFAULT '',
	`addtime` datetime DEFAULT NULL,
	`creator` varchar(255) DEFAULT NULL,
	`easycount` tinyint(4) DEFAULT '0',
	`isprivate` tinyint(4) DEFAULT '0',
	PRIMARY KEY (`choose_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ex_judge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_judge` (
	`judge_id` int(11) NOT NULL AUTO_INCREMENT,
	`question` text,
	`answer` char(1) DEFAULT NULL,
	`point` varchar(100) DEFAULT '',
	`addtime` datetime DEFAULT NULL,
	`creator` varchar(255) DEFAULT NULL,
	`easycount` tinyint(4) DEFAULT '0',
	`isprivate` tinyint(4) DEFAULT '0',
	PRIMARY KEY (`judge_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ex_fill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_fill` (
	`fill_id` int(11) NOT NULL AUTO_INCREMENT,
	`question` text,
	`answernum` tinyint(4) DEFAULT 0,
	`point` varchar(100) DEFAULT '',
	`addtime` datetime DEFAULT NULL,
	`creator` varchar(255) DEFAULT NULL,
	`easycount` tinyint(4) DEFAULT '0',
	`kind` tinyint(4) DEFAULT '1',
	`isprivate` tinyint(4) DEFAULT '0',
	PRIMARY KEY (`fill_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `fill_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fill_answer` (
	`fill_id` int(11) NOT NULL,
	`answer_id` tinyint(4) DEFAULT NULL,
	`answer` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`fill_id`,`answer_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ex_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_student` (
	`user_id` varchar(20) NOT NULL DEFAULT '',
	`exam_id` int(11) NOT NULL,
	`score` tinyint(4) DEFAULT '0',
	`choosesum` tinyint(4) DEFAULT '0',
	`judgesum` tinyint(4) DEFAULT '0',
	`fillsum` tinyint(4) DEFAULT '0',
	`programsum` tinyint(4) DEFAULT '0',
	PRIMARY KEY (`user_id`,`exam_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ex_privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_privilege` (
  `user_id` char(20) NOT NULL DEFAULT '',
  `rightstr` char(30) NOT NULL DEFAULT '',
  `randnum` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`rightstr`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `exp_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exp_question` (
	`type` tinyint(4) NOT NULL,
	`exam_id` int(11) NOT NULL,
	`question_id` int(11) NOT NULL
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ex_stuanswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_stuanswer` (
	`user_id` varchar(20) NOT NULL DEFAULT '',
	`exam_id` int(11) NOT NULL,
	`type` tinyint(4) NOT NULL,
	`question_id` int(11) NOT NULL,
	`answer_id` tinyint(4) NOT NULL DEFAULT '1',
	`answer` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`exam_id`,`user_id`,`type`,`question_id`,`answer_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ex_point`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_point` (
	`point_id` int(11) not null auto_increment,
	`point` varchar(100) DEFAULT '',
	`point_pos` int(11) not null
	PRIMARY KEY(`point_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `pro_point`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pro_point` (
	`point_id` int(11) not null,
     	`question_id` int(11) not null,
     	`type` tinyint(4) not null,
    	primary key(`type`,`question_id`,`point_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
