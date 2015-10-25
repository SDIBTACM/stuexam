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

<div class="container exam_content">
	<header class='clearfix'>
		<h1>&nbsp;&nbsp;&nbsp;Start Time</h1>
		<h1>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h1>
		<h1>Exam Title</h1>
	</header>
	<div class="main">
		<ul class="cbp_tmtimeline">
			<?php if(is_array($row)): foreach($row as $key=>$r): ?><li>
				<time class="cbp_tmtime" datetime="<?php echo ($r['start_time']); ?>"><span><?php echo (date('Y/m/d',strtotime($r['start_time']))); ?></span> <span><?php echo (date('H:i',strtotime($r['start_time']))); ?></span></time>
				<div class="cbp_tmicon"></div>
				<div class="cbp_tmlabel" onclick="window.location.href='<?php echo U('Home/Index/about',array('eid'=>$r['exam_id']));?>'">
					<h2><?php echo ($r['title']); ?></h2>
					<p>Start From:<?php echo ($r['start_time']); ?> To <?php echo ($r['end_time']); ?>
				<?php if(time() > strtotime($r['end_time'])): ?><label class='label label-success'>已结束</label>
				<?php else: ?>
				  <?php if(time() < strtotime($r['start_time'])): ?><label class='label label-info'>未开始</label>
				<?php else: ?>
					<label class='label label-danger'>正在进行</label><?php endif; endif; ?></p>
				</div>
				</li><?php endforeach; endif; ?>
		</ul>
	</div>
	<?php echo (showpagelast($mypage,U("Home/Index/index"))); ?>
</div>
<script type="text/javascript" src="/stuexam/Public/Js/modernizr.custom.js"></script>
<link rel="stylesheet" type="text/css" href="/stuexam/Public/Css/component.min.css" />
<link rel="stylesheet" type="text/css" href="/stuexam/Public/Css/default.min.css" />
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