<?php
return array(
    'LOAD_EXT_CONFIG' => 'log,database',

	'OJ_VIP_CONTEST'=>false,

	'URL_HTML_SUFFIX' => '', //伪静态后缀名设置
	'TMPL_VAR_IDENTIFY' => 'array', // 点语法的解析
	'URL_MODEL' => 2,

	'EACH_PAGE' => 20, //每一页显示的数目
	'PAGE_NUM' => 10, //最多显示的页数

	//'SHOW_PAGE_TRACE' => true,
	'URL_CASE_INSENSITIVE'  =>  false,
    'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR',

	'EXAM_VERSION' => '1.0.2',

	'TEACHER_LIST_CACHE_TIME' => 86400 // 默认一天
);
