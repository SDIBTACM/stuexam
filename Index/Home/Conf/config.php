<?php
return array(
	//'配置项'=>'配置值'
	'judge_color'		=>	array("gray","gray","orange","orange","green","red","red","red","red","red","red","#004488","#004488"),
	'judge_result'		=>	array('正在努力的判题中...','还在判题呢,不要急~~','再等等就好了~','正在努力的判题中...',
								'厉害了我的同学, 答对了!','格式错误了,你离正确不远啦~','sad, 部分答案错误了, 快再查查吧~','天哪,时间超限,它运行的时间都能喝杯茶了','我的天哪,居然内存超限了',
								'输出超限,它输出的都停不下来了~','运行错误,真是个棘手的问题','编译错误,快去找找你的编译器在哪儿','Compile OK'),
	'language_ext'		=>	array( "c", "cc", "pas", "java" ),
	'OJ_APPENDCODE'		=>	true,
	'OJ_DATA'			=>	"/home/judge/data",
	'EXTEND_PREFIX'		=>  "exam_",
	'EXTEND_LIMIT'		=>  6,

	'LOG_RECORD'		=>  true,

);
