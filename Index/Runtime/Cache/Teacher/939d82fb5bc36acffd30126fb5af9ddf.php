<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="程序设计考试 山东工商学院">
	<meta name="keywords" content="Exam,SDIBT,山东工商学院,程序设计考试">
	<!-- yours css -->
	<link rel="stylesheet" type="text/css" href="/JudgeOnline/stuexam/Public/Css/examsys.min.css" />
	<!-- Bootstrap -->
	<link rel="stylesheet" type="text/css" href="/JudgeOnline/stuexam/Public/Css/bootstrap.min.css" />
	<!--[if lt IE 9]>
		<script src="/JudgeOnline/stuexam/Public/Js/html5shiv.min.js"></script>
		<script src="/JudgeOnline/stuexam/Public/Js/respond.min.js"></script>
	<![endif]-->
</head>
<body>
<div class="navbar navbar-fixed-top navbar-default exam_header" role="navigation">
  <div class="container">
  	<div class="navbar-header">
  	  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" 
  	  data-target="#header-navbar">
  	  	<span class="sr-only">header toggle</span>
  	  	<span class="icon-bar"></span>
  	  	<span class="icon-bar"></span>
  	  	<span class="icon-bar"></span>
  	  </button>
  	  <a href="#" class="navbar-brand exam_navbar-brand">程序设计考试后台管理</a>
  	</div> <!-- navbar-header-end -->
	<div class="collapse navbar-collapse" id="header-navbar">
	<ul class="nav navbar-nav">
	  <li id='navexam'><a href="<?php echo U('/Teacher');?>">考试管理</a></li>
	  <li id='navchoose'><a href="<?php echo U('Teacher/Index/choose');?>">选择题管理</a></li>
	  <li id='navjudge'><a href="<?php echo U('Teacher/Index/judge');?>">判断题管理</a></li>
	  <li id='navfill'><a href="<?php echo U('Teacher/Index/fill');?>">填空题管理</a></li>
	  <li id='navpoint'><a href="<?php echo U('Teacher/Index/point');?>">知识点管理</a></li>
	  <li><a href="<?php echo U('/Home');?>">退出管理页面</a></li>
	</ul> <!-- first ul end -->
	<ul class="nav navbar-nav navbar-right">
		<li><a href="javascript:;">欢迎您： <?php echo (session('user_id')); ?></a></li>
	</ul> <!-- the second ul end -->
   </div> <!-- collapse navbar-collapse end -->
  </div> <!-- container-fluid end -->
</div> <!-- navbar end -->

<script type="text/javascript" src="/JudgeOnline/stuexam/Public/Js/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="/JudgeOnline/stuexam/Public/Js/Teacher.min.js"></script>
<script>
	$(function(){
		var url = window.location.href;
		if(url.indexOf('fill')!=-1){
			$("#navfill").addClass('active');
		}
		else if(url.indexOf('choose')!=-1){
			$("#navchoose").addClass('active');
		}
		else if(url.indexOf('judge')!=-1){
			$("#navjudge").addClass('active');
		}
		else if(url.indexOf('point')!=-1){
			$("#navpoint").addClass('active');
		}
		else{
			$("#navexam").addClass('active');
		}
	});
</script>

<div class="container exam_content">
	<?php if(isset($row['exam_id'])): ?><h1>编辑考试</h1>
	<?php else: ?>
		<h1>添加考试</h1><?php endif; ?>

	<hr>
	<form class="form-horizontal" action="<?php echo U('Teacher/Add/exam');?>" method="post" onSubmit="return chkexam(this)">

	<div class="form-group">
	  <label for="examname" class="control-label col-md-3">考试名称:</label>
	  <div class="col-md-6">
	  <input type="text" id='examname' name="examname" class="form-control" value="<?php echo (htmlspecialchars((isset($row['title']) && ($row['title'] !== ""))?($row['title']):'')); ?>">
	  </div>
	</div>
	<?php if(isset($row['start_time'])): ?><div class="form-group">
		  <label class="control-label col-md-3">考试开始时间:</label>
		  <div class="col-md-1">
		  <input type="text" name="syear" class="form-control" value="<?php echo (substr($row['start_time'],0,4)); ?>">年-</div>
		  <div class="col-md-1">
		  <input type="text" name="smonth" class="form-control" value="<?php echo (substr($row['start_time'],5,2)); ?>">月-</div>
		  <div class="col-md-1">
		  <input type="text" name="sday" class="form-control" value="<?php echo (substr($row['start_time'],8,2)); ?>">日-</div>
		  <div class="col-md-1">
		  <input type="text" name="shour" class="form-control" value="<?php echo (substr($row['start_time'],11,2)); ?>">时-</div>
		  <div class="col-md-1">
		  <input type="text" name="sminute" class="form-control" value="<?php echo (substr($row['start_time'],14,2)); ?>">分</div>
		</div>
	<?php else: ?>	
		<div class="form-group">
		  <label class="control-label col-md-3">考试开始时间:</label>
		  <div class="col-md-1">
		  <input type="text" name="syear" class="form-control" value="<?php echo date('Y');?>">年-</div>
		  <div class="col-md-1">
		  <input type="text" name="smonth" class="form-control" value="<?php echo date('m');?>">月-</div>
		  <div class="col-md-1">
		  <input type="text" name="sday" class="form-control" value="<?php echo date('d');?>">日-</div>
		  <div class="col-md-1">
		  <input type="text" name="shour" class="form-control" value="<?php echo date('H');?>">时-</div>
		  <div class="col-md-1">
		  <input type="text" name="sminute" class="form-control" value="00">分</div>
		</div><?php endif; ?>
	

	<?php if(isset($row['end_time'])): ?><div class="form-group">
		  <label class="control-label col-md-3">考试结束时间:</label>
		  <div class="col-md-1">
		  <input type="text" name="eyear" class="form-control" value="<?php echo (substr($row['end_time'],0,4)); ?>">年-</div>
		  <div class="col-md-1">
		  <input type="text" name="emonth" class="form-control" value="<?php echo (substr($row['end_time'],5,2)); ?>">月-</div>
		  <div class="col-md-1">
		  <input type="text" name="eday" class="form-control" value="<?php echo (substr($row['end_time'],8,2)); ?>">日-</div>
		  <div class="col-md-1">
		  <input type="text" name="ehour" class="form-control" value="<?php echo (substr($row['end_time'],11,2)); ?>">时-</div>
		  <div class="col-md-1">
		  <input type="text" name="eminute" class="form-control" value="<?php echo (substr($row['end_time'],14,2)); ?>">分</div>
		</div>
	<?php else: ?>
		<div class="form-group">
		  <label class="control-label col-md-3">考试结束时间:</label>
		  <div class="col-md-1">
		  <input type="text" name="eyear" class="form-control" value="<?php echo date('Y');?>">年-</div>
		  <div class="col-md-1">
		  <input type="text" name="emonth" class="form-control" value="<?php echo date('m');?>">月-</div>
		  <div class="col-md-1">
		  <input type="text" name="eday" class="form-control" value="<?php echo date('d')+(date('H')+2>23?1:0);?>">日-</div>
		  <div class="col-md-1">
		  <input type="text" name="ehour" class="form-control" value="<?php echo (date('H')+2)%24;?>">时-</div>
		  <div class="col-md-1">
		  <input type="text" name="eminute" class="form-control" value="00">分</div>
		</div><?php endif; ?>
	

	<span class='label label-warning'>*以下数值只支持整数</span>
	
	<div class="form-group">
	  <label for="xzfs" class="control-label col-md-3">1.选择题每题分值:</label>
	   <div class="col-md-6">
	  	<input type="text" id='xzfs' name="xzfs" class="form-control" value="<?php echo ((isset($row['choosescore']) && ($row['choosescore'] !== ""))?($row['choosescore']):''); ?>">
	  </div>
	</div>

	<div class="form-group">
	  <label for="pdfs" class="control-label col-md-3">2.判断题每题分值:</label>
	   <div class="col-md-6">
	  	<input type="text" id='pdfs' name="pdfs" class="form-control" value="<?php echo ((isset($row['judgescore']) && ($row['judgescore'] !== ""))?($row['judgescore']):''); ?>">
	  </div>
	</div>

	<div class="form-group">
	  <label for="tkfs" class="control-label col-md-3">3.基础填空题每空分值:</label>
	   <div class="col-md-6">
	  	<input type="text" id='tkfs' name="tkfs" class="form-control" value="<?php echo ((isset($row['fillscore']) && ($row['fillscore'] !== ""))?($row['fillscore']):''); ?>">
	  </div>
	</div>

	<div class="form-group">
	  <label for="yxjgfs" class="control-label col-md-3">4.写运行结果题每题分值:</label>
	   <div class="col-md-6">
	  	<input type="text" id='yxjgfs' name="yxjgfs" class="form-control" value="<?php echo ((isset($row['prgans']) && ($row['prgans'] !== ""))?($row['prgans']):''); ?>">
	  </div>
	</div>

	<div class="form-group">
	  <label for="cxtkfs" class="control-label col-md-3">5.程序填空题每题分值:</label>
	   <div class="col-md-6">
	  	<input type="text" id='cxtkfs' name="cxtkfs" class="form-control" value="<?php echo ((isset($row['prgfill']) && ($row['prgfill'] !== ""))?($row['prgfill']):''); ?>">
	  </div>
	</div>

	<div class="form-group">
	  <label for="cxfs" class="control-label col-md-3">6.程序设计题每题分值:</label>
	   <div class="col-md-6">
	  	<input type="text" id='cxfs' name="cxfs" class="form-control" value="<?php echo ((isset($row['programscore']) && ($row['programscore'] !== ""))?($row['programscore']):''); ?>">
	  </div>
	</div>

	<div class="form-group">
	  <label for="cxfs" class="control-label col-md-3">是否限定一个账号只能在一台机器登陆:</label>
	   <div class="col-md-6">
	  	<select name="isvip" class="form-control">
	  		<?php if(isset($row['isvip']) and $row['isvip'] == 'Y'): ?><option value="Y" selected>Yes</option>
	  		<?php else: ?>
	  			<option value="Y">Yes</option><?php endif; ?>
			<?php if(isset($row['isvip']) and $row['isvip'] == 'N'): ?><option value="N" selected>No</option>
	  		<?php else: ?>
	  			<option value="N">No</option><?php endif; ?>
		</select>
	  </div>
	</div>

	<input type='hidden' name="postkey" value="<?php echo ($mykey); ?>">
	<input type='hidden' name='page' value="<?php echo ($page); ?>">
	<?php if(isset($row['exam_id'])): ?><input type="hidden" name="examid" value="<?php echo ($row['exam_id']); ?>"><?php endif; ?>

	<div class="col-md-offset-3 col-md-6">
		<button class="btn btn-primary col-md-6" type="submit">Submit</button>
		<?php if(isset($row['exam_id'])): ?><button class="btn btn-danger col-md-6" type="button" onclick="javascript:history.go(-1);">Back</button>
		<?php else: ?>
			<button class="btn btn-danger col-md-6" type="reset">Reset</button><?php endif; ?>
	</div>
	</form>
</div>
<style>
#examFooter, #examFooter ul li {
	background-color: #252525;
	text-align: center;
}
#examFooter .container {
	padding-top: 30px;
}
#examFooter ul li {
border: none;
color:white;
      font-size: 20px;
}
#examFooter ul li a , #examFooter p{
	font-size: 15px;
color : #959595;
}
</style>
<footer id="examFooter">
<div class="container">
<ul class="list-group col-md-4">
<li class="list-group-item">下载链接</li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/download/Firefox.exe">Firefox</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/download/codeblocks-12.11mingw-setup.exe">CodeBlocks for Win</a></li>
</ul>
<ul class="list-group col-md-4">
<li class="list-group-item">主站导航</li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/faqs.php">F.A.Qs</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/contest.php">Contest</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/ranklist.php">Ranklist</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/problemset.php">ProblemSet</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/stuexam/">Examination</a></li>
</ul>
<ul class="list-group col-md-3">
<li class="list-group-item">关于我们</li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/blog/">博客</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/ranklist/">省赛排名</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn:8001">训练计划</a></li>
</ul>
<div class="col-md-offset-2 col-md-8">
<p>@Copyright&copy;SDIBT_ACM | Any Problems, Please Contact Admin:<a href="mailto:sdibtacm@126.com">admin</a></p>
</div>
</div>
</footer>
<script type="text/javascript" src="/JudgeOnline/stuexam/Public/Js/bootstrap.min.js"></script>
</body>
</html>