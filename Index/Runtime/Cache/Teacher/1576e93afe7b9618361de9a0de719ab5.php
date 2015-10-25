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

<div class="container exam_content" style='text-align:left'>
	<div style="text-align:center">
		<h1><?php echo ((isset($title) && ($title !== ""))?($title):""); ?></h1>
		<h3>考生账号:<?php echo ($_GET['users']); ?></h3>
	</div>

	<h4>一.选择题</h4>
	<?php if(is_array($chooseans)): foreach($chooseans as $numofchoose=>$cans): ?><div class="panel panel-default">
		<div class="panel-heading"><font color='red'>(<?php echo ($allscore['choosescore']); ?> 分)</font><?php echo ($numofchoose+1); ?>.<?php echo ($cans['question']); ?></div>
		<div class="panel-body">
			<p>(A) <?php echo ($cans['ams']); ?><br/>(B) <?php echo ($cans['bms']); ?><br/>(C) <?php echo ($cans['cms']); ?><br/>(D) <?php echo ($cans['dms']); ?></p>
		</div>
		<div class="panel-footer"><strong>我的答案:<?php echo ((isset($choosearr[$cans['choose_id']]) && ($choosearr[$cans['choose_id']] !== ""))?($choosearr[$cans['choose_id']]):""); ?><br/>正确答案:<?php echo ($cans['answer']); ?></strong></div>
	</div><?php endforeach; endif; ?>

	<h4>二.判断题</h4>
	<?php if(is_array($judgeans)): foreach($judgeans as $numofjudge=>$jans): ?><div class="panel panel-default">
			<div class="panel-heading"><font color='red'>(<?php echo ($allscore['judgescore']); ?> 分)</font><?php echo ($numofjudge+1); ?>.<?php echo ($jans['question']); ?></div>
			<div class="panel-body"><strong>我的答案:<?php echo ((isset($judgearr[$jans['judge_id']]) && ($judgearr[$jans['judge_id']] !== ""))?($judgearr[$jans['judge_id']]):""); ?></strong></div>
			<div class="panel-footer"><strong>正确答案:<?php echo ($jans['answer']); ?></strong></div>
		</div><?php endforeach; endif; ?>

	<h4>三.填空题</h4>
	<?php if(is_array($fillans)): foreach($fillans as $numoffill=>$fans): ?><div class="panel panel-default">
			<div class="panel-heading">
				<?php if($fans['kind'] == 1): ?><font color='red'>(<?php echo ($allscore['fillscore']*$fans['answernum']); ?> 分)</font>
				<?php elseif($fans['kind'] == 2): ?>
					<font color='red'>(<?php echo ($allscore['prgans']); ?> 分)</font>
				<?php else: ?>
					<font color='red'>(<?php echo ($allscore['prgfill']); ?> 分)</font><?php endif; ?>
			<?php echo ($numoffill+1); ?>.<?php echo ($fans['question']); ?></div>
			<div class="panel-body">
				<?php $__FOR_START_1325374367__=1;$__FOR_END_1325374367__=$fans['answernum']+1;for($i=$__FOR_START_1325374367__;$i < $__FOR_END_1325374367__;$i+=1){ if($i == 1): ?><strong>我的答案:</strong><?php endif; ?>
					<strong>答案(<?php echo ($i); ?>) <?php echo ((isset($fillarr[$fans['fill_id']][$i]) && ($fillarr[$fans['fill_id']][$i] !== ""))?($fillarr[$fans['fill_id']][$i]):''); ?></strong><?php } ?>
			</div>
			<div class="panel-footer">
				<?php if(is_array($fillans2[$fans['fill_id']])): foreach($fillans2[$fans['fill_id']] as $k=>$tmprow): if($k == 0): ?><strong>正确答案:</strong><?php endif; ?>
					<strong>答案(<?php echo ($tmprow['answer_id']); ?>) <?php echo ((isset($tmprow['answer']) && ($tmprow['answer'] !== ""))?($tmprow['answer']):''); ?>    </strong><?php endforeach; endif; ?>
			</div>
		</div><?php endforeach; endif; ?>
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