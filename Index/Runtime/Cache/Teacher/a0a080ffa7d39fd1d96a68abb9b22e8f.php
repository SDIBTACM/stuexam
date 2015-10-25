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

<div class="container exam_content" >
	<div class="alert alert-danger alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	目前题目的知识点还不支持添加多个，以及批量删除等～^_^先看看帮忙测试一下其他功能吧
	</div>
	<div class="row">
	<div class="col-md-1">
	<button class='btn btn-info' id='candry' onclick="candry()">点击可以拖动</button>
	<button class="btn btn-info" id='okdry' onclick="okdry()" style="display:none">确认拖动修改</button>
	</div>
	  <div class="col-md-offset-2 col-md-6">
	    <div class="input-group">
	      <input type="text" id="pointtext" class="form-control" placeholder="输入知识点">
	      <span class="input-group-btn">
	        <button class="btn btn-info" type="button" onclick="addpoint()">添加知识点</button>
	      </span>
	    </div>
	  </div>
	</div>
	<div id="sortable">
		<?php if(is_array($pnt)): foreach($pnt as $k=>$p): ?><li class="ui-state-default" id="point<?php echo ($p['point_id']); ?>"><?php echo ($p['point']); ?><button class='close' onclick="delpoint(<?php echo ($p['point_id']); ?>)"><span>&times;</span></button></li><?php endforeach; endif; ?>
	</div>
</div>
<script type="text/javascript" src="/JudgeOnline/stuexam/Public/Js/jquery-ui.min.js"></script>
<script>
	var ajaxurl = "<?php echo U('Teacher/Add/point');?>";
	var dosorturl = "<?php echo U('Teacher/Index/dosort');?>";
</script>
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