<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="程序设计考试 山东工商学院">
	<meta name="keywords" content="Exam,SDIBT,山东工商学院,程序设计考试">
	<!-- yours css -->
	<link rel="stylesheet" type="text/css" href="/stuexam/Public/Css/examsys.min.css" />
	<!-- Bootstrap -->
	<link rel="stylesheet" type="text/css" href="/stuexam/Public/Css/bootstrap.min.css" />
	<!--[if lt IE 9]>
		<script src="/stuexam/Public/Js/html5shiv.min.js"></script>
		<script src="/stuexam/Public/Js/respond.min.js"></script>
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
  	  <a href="#" class="navbar-brand exam_navbar-brand">程序设计考试系统</a>
  	</div> <!-- navbar-header-end -->
	<div class="collapse navbar-collapse" id="header-navbar">
	  <ul class="nav navbar-nav">
	  	<li><a href="/JudgeOnline/">Online Judge</a></li>
		<li id="indexnavindex"><a href="<?php echo U('/Home');?>">Exam List</a></li>
		<?php if(isset($_SESSION['administrator']) or isset($_SESSION['contest_creator']) or isset($_SESSION['problem_editor'])): ?><li><a href="<?php echo U('/Teacher');?>">教师管理</a></li>
		<?php else: ?>
		<li id="indexnavscore"><a href="<?php echo U('Home/Index/score');?>">个人中心</a></li><?php endif; ?>
	</ul> <!-- first ul end -->
	<ul class="nav navbar-nav navbar-right">
		<li class='active'><a href="javascript:void(0);" id='nowdate'></a></li>
		<li><a href="javascript:;">欢迎您： <?php echo (session('user_id')); ?></a></li>
	</ul> <!-- the second ul end -->
   </div> <!-- collapse navbar-collapse end -->
  </div> <!-- container-fluid end -->
</div> <!-- navbar end -->
<script type="text/javascript" src="/stuexam/Public/Js/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="/stuexam/Public/Js/Home.min.js"></script>
<script>
$(function(){
	var url = window.location.href;
	if(url.indexOf('score')!=-1){
		$("#indexnavscore").addClass('active');
	}
	else{
		$("#indexnavindex").addClass('active');
	}
	clock();
});
var diff=new Date("<?=date("Y/m/d H:i:s")?>").getTime()-new Date().getTime();
</script>

<div class="container exam_content" style='text-align:left'>

<div class="panel panel-default">
	<div class="panel-heading">
		<h2>考试名称:<?php echo ($row['title']); ?><h2>
		<p><small>Start Time:<?php echo ($row['start_time']); ?> End Time:<?php echo ($row['end_time']); ?></small></p>	
	</div>
	<div class="panel-body">
		<h3>考生信息:</h3>
		<p>姓名: <?php echo ($name); ?>
		<p>账号: <?php echo (session('user_id')); ?></p>
		<h3>考生须知:</h3>
		<ol>
			<li>请认真检查左侧个人真实信息,关系最后考试成绩,姓名为个人真实姓名,
			<br>账号格式为专业缩写+学号后八位,如jk11171101.</li>
			<li>请各位将手机关闭，考试期间手机响按作弊处理.</li>
			<li>考场内凡交头接耳、抄袭、打手势或大声喧哗的按作弊处理.</li>
			<li>考试过程中，一旦提交试卷，将显示得分，并且考试结束，请谨慎选择.<br>
			考试时间到，未提交试卷的，考试系统将自动提交试卷.</li>
			<li>采取各种方式抄录试题的按照严重作弊取理.</li>
			<li>请及时保存答案,防止特殊情况发生答案丢失.刷新界面前,请先保存答案</li>
		</ol>
	</div>
	<div class="panel-footer pull-right">
		<?php if($isruning == 0): ?><button class='btn btn-success' disabled>考试已结束</button>
			<?php elseif($isruning == 1): ?>
			<a class='btn btn-danger' href="<?php echo U('Home/Exam/showquestion',array('eid'=>$_GET['eid']));?>">开始考试</a>
		<?php else: ?>
			<button class='btn btn-info' disabled>考试未进行</button><?php endif; ?>
	</div>
	<div class="clearfix"></div>
</div>

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
<script type="text/javascript" src="/stuexam/Public/Js/bootstrap.min.js"></script>
</body>
</html>