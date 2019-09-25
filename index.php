<?php
	// 检测PHP环境
	if(version_compare(PHP_VERSION,'5.3.0','<'))  
		die('require PHP > 5.3.0 !');

	define("APP_NAME","Index");
	define("APP_PATH","./Index/");
	
	// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
	define('APP_DEBUG', true);
	define('IS_DEBUG', false); // log 模块 debug 控制

    $gitHead = file_get_contents('.git/HEAD', false, NULL, 5);
    if ($gitHead != '') {
        $gitHeadHashFile = '.git/' . trim($gitHead);
        $gitHash = trim(file_get_contents($gitHeadHashFile));
    }
    if ($gitHash == '') {
        $gitHash = md5(time());
    }

    define("STATIC_FILE_VERSION", $gitHash);
    define("STATIC_FILE_VERSION_SHORT", substr($gitHash, 0, 6));
    define("SFV", STATIC_FILE_VERSION_SHORT);

	require './ThinkPHP/ThinkPHP.php';
?>
