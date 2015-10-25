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
<h2>成绩单</h2>
<div style='padding-bottom:7px'>
<ul class="nav nav-pills">
<li id='exam_index'><a href="<?php echo U('Teacher/Exam/index',array('eid'=>$eid));?>">试卷一览</a></li>
<li id='exam_choose'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>1));?>">选择题</a></li>
<li id='exam_judge'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>2));?>">判断题</a></li>
<li id='exam_fill'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>3));?>">填空题</a></li>
<li id='exam_program'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>4));?>">程序题</a></li>
<li id='exam_adduser'><a href="<?php echo U('Teacher/Exam/adduser',array('eid'=>$eid));?>">添加考生</a></li>
<li id='exam_userscore'><a href="<?php echo U('Teacher/Exam/userscore',array('eid'=>$eid));?>">考生成绩</a></li>
<li id='exam_analysis'><a href="<?php echo U('Teacher/Exam/analysis',array('eid'=>$eid));?>">考试分析</a></li>
<li id='exam_rejudge'><a href="<?php echo U('Teacher/Exam/rejudge',array('eid'=>$eid));?>">REJUDGE</a></li>
</ul>
</div>
<input type="hidden" name="eid" id="eid" value="<?php echo ($eid); ?>" />

<table class="table table-hover table-bordered table-condensed">
<thead>
<tr>
<th width=5%>Rank</th>
<th width=7%>账号</th>
<th width=7%>姓名</th>
<th width=7%>选择题成绩</th>
<th width=7%>判断题成绩</th>
<th width=7%>填空题成绩</th>
<th width=7%>程序题成绩</th>
<th width=7%>总成绩</th>
<th width=7%>试卷</th>
<th width=7%>操作</th>
</tr>
</thead>
<tbody>
<tr class='first-tr'>
	<form class='form-inline'>
	<td></td>
	<td class='form-group'><input class='form-control' type="text" id="xs_userid" name="xs_userid" placeholder="查询账号"/></td>
	<td class='form-group'><input class='form-control' type="text" id="xs_name" name="xs_name" placeholder="查询姓名"/></td>
	<td class='form-group'><select class='form-control' name="xs_choose" id="xs_choose">
		<option value='0' >请选择排序方式</option>
		<option value='1' >升序排序</option>
		<option value='2' >降序排序</option>
	</select></td>
	<td class='form-group'><select class='form-control' name = "xs_judge" id="xs_judge">
		<option value='0' >请选择排序方式</option>
		<option value='1' >升序排序</option>
		<option value='2' >降序排序</option>
	</select></td>
	<td class='form-group'><select class='form-control' name = "xs_fill" id="xs_fill">
		<option value='0' >请选择排序方式</option>
		<option value='1' >升序排序</option>
		<option value='2' >降序排序</option>
	</select></td>
	<td class='form-group'><select class='form-control' name = "xs_program" id="xs_program">
		<option value='0' >请选择排序方式</option>
		<option value='1' >升序排序</option>
		<option value='2' >降序排序</option>
	</select></td>
	<td class='form-group'><select class='form-control' name = "xs_score" id="xs_score">
		<option value='0' >请选择排序方式</option>
		<option value='1' >升序排序</option>
		<option value='2' >降序排序</option>
	</select></td>
	<td colspan='2'><button type='button' class='btn btn-info' onclick="xs_search()">
	Search</button></td>
	</form>
</tr>

<?php if(is_array($row)): foreach($row as $k=>$r): ?><tr>
<td><?php echo ($k+1); ?></td>
<td><?php echo ($r['user_id']); ?></td>
<td><?php echo ($r['nick']); ?></td>
<td><?php echo ($r['choosesum']); ?></td>
<td><?php echo ($r['judgesum']); ?></td>
<td><?php echo ($r['fillsum']); ?></td>
<td><?php echo ($r['programsum']); ?></td>
<td><?php echo ($r['score']); ?></td>
<?php if($r['score'] == ''): if(isset($isonline[$r['user_id']])): if(time() > $end_timeC): ?><td><span class='label label-warning'>未交卷</span></td>
		<?php else: ?>
			<td><span class='label label-danger'>正在考试</span></td><?php endif; ?>
	<?php else: ?>
	<td><span class='label label-default'>未参加</span></td><?php endif; ?>
<?php else: ?>
	<td><a class='label label-info' href="<?php echo U('Teacher/Info/showpaper',array('users'=>$r['user_id'],'eid'=>$eid));?>">查看试卷>></a></td><?php endif; ?>

<?php if($r['score'] == ''): if(isset($isonline[$r['user_id']]) and time() > $end_timeC): ?><td><a class='label label-success' href="<?php echo U('Teacher/Info/submitpaper',array('users'=>$r['user_id'],'eid'=>$eid));?>">提交试卷>></a></td>
	<?php else: ?>
		<td>无</td><?php endif; ?>
<?php else: ?>
<td><a class='label label-danger' href="<?php echo U('Teacher/Info/delscore',array('users'=>$r['user_id'],'eid'=>$eid));?>" onclick="return suredo('','是否要删除该考生成绩,让考生重新参加考试？')">删除分数  X</a></td><?php endif; ?>
</tr><?php endforeach; endif; ?>

</tbody>
</table>
</div>
<script type="text/javascript">
	var scoreurl = "<?php echo U('Teacher/Exam/userscore');?>";
	$(function(){
		$("#exam_userscore").addClass('active');
	});
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