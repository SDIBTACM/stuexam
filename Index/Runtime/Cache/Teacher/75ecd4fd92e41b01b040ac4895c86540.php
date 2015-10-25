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

<div class="container exam_content papershow" style='text-align:left'>
<h2>试卷一览</h2>
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

<h4>一.选择题</h4>
<?php if(is_array($chooseans)): foreach($chooseans as $numofchoose=>$cans): ?><div class="panel panel-default">
	<div class="panel-heading"><font color='red'>(<?php echo ($allscore['choosescore']); ?> 分)</font><?php echo ($numofchoose+1); ?>.<?php echo (nl2br($cans['question'])); ?></div>
	<div class="panel-body">
		<p>(A) <?php echo ($cans['ams']); ?><br/>(B) <?php echo ($cans['bms']); ?><br/>(C) <?php echo ($cans['cms']); ?><br/>(D) <?php echo ($cans['dms']); ?></p>
		<p><strong>正确答案:<?php echo ($cans['answer']); ?></strong></p>
	</div>
	<div class="panel-footer"><a href="javascript:void(0);" onclick="deltoexam(this,<?php echo ($eid); ?>,<?php echo ($cans['choose_id']); ?>,1)">[去除该题]</a></div>
</div><?php endforeach; endif; ?>

<h4>二.判断题</h4>
<?php if(is_array($judgeans)): foreach($judgeans as $numofjudge=>$jans): ?><div class="panel panel-default">
		<div class="panel-heading"><font color='red'>(<?php echo ($allscore['judgescore']); ?> 分)</font><?php echo ($numofjudge+1); ?>.<?php echo (nl2br($jans['question'])); ?></div>
		<div class="panel-body"><strong>正确答案:<?php echo ($jans['answer']); ?></strong></div>
		<div class="panel-footer"><a href="javascript:void(0);" onclick="deltoexam(this,<?php echo ($eid); ?>,<?php echo ($jans['judge_id']); ?>,2)">[去除该题]</a></div>
	</div><?php endforeach; endif; ?>

<h4>三.填空题</h4>
<?php if(is_array($fillans)): foreach($fillans as $numoffill=>$fans): ?><div class="panel panel-default">
		<div class="panel-heading">
			<?php if($fans['kind'] == 1): ?><font color='red'>(<?php echo ($allscore['fillscore']*$fans['answernum']); ?> 分)</font>
				<?php $fillnum+=$fans['answernum']; ?>
			<?php elseif($fans['kind'] == 2): ?>
				<font color='red'>(<?php echo ($allscore['prgans']); ?> 分)</font>
				<?php $prgansnum+=1; ?>
			<?php else: ?>
				<font color='red'>(<?php echo ($allscore['prgfill']); ?> 分)</font>
				<?php $prgfillnum+=1; endif; ?>
		<?php echo ($numoffill+1); ?>.<?php echo (nl2br($fans['question'])); ?></div>
		<div class="panel-body">
			<?php if(is_array($fillans2[$fans['fill_id']])): foreach($fillans2[$fans['fill_id']] as $k=>$tmprow): if($k == 0): ?><strong>正确答案:</strong><?php endif; ?>
				<strong>答案(<?php echo ($tmprow['answer_id']); ?>) <?php echo ((isset($tmprow['answer']) && ($tmprow['answer'] !== ""))?($tmprow['answer']):''); ?>    </strong><?php endforeach; endif; ?>
		</div>
		<div class="panel-footer"><a href="javascript:void(0);" onclick="deltoexam(this,<?php echo ($eid); ?>,<?php echo ($fans['fill_id']); ?>,3)">[去除该题]</a></div>
	</div><?php endforeach; endif; ?>

<h4>四.程序设计题</h4>
<?php if(is_array($programans)): foreach($programans as $numofprogram=>$pans): ?><div class="panel panel-default">
		<div class="panel-heading"><font color='red'>(<?php echo ($allscore['programscore']); ?> 分)</font>
		<?php echo ($numofprogram+1); ?>.<?php echo (nl2br($pans['title'])); ?></div>
		<div class="panel-body">
			<h4>Description</h4>
			<p><?php echo (nl2br($pans['description'])); ?></p>
		</div>
	</div><?php endforeach; endif; ?>

<table border='1' style='position:fixed;right:0px;top:70px;'>
<thread>
	<th>题型</th><th>每题(空)分数</th><th>题(空)数</th><th>总分</th>
</thread>
<tbody>
	<tr>
		<td>选择题</td>
		<td><?php echo ($allscore['choosescore']); ?></td>
		<td><?php echo ($choosenum); ?>道</td>
		<td><?php echo ($allscore['choosescore']*$choosenum); ?>分</td>
	</tr>
	<tr>
		<td>判断题</td>
		<td><?php echo ($allscore['judgescore']); ?></td>
		<td><?php echo ($judgenum); ?>道</td>
		<td><?php echo ($allscore['judgescore']*$judgenum); ?>分</td>
	</tr>
	<tr>
		<td>基础填空题</td>
		<td><?php echo ($allscore['fillscore']); ?></td>
		<td><?php echo ($fillnum); ?>空</td>
		<td><?php echo ($allscore['fillscore']*$fillnum); ?>分</td>
	</tr>
	<tr>
		<td>写运行结果</td>
		<td><?php echo ($allscore['prgans']); ?></td>
		<td><?php echo ($prgansnum); ?>道</td>
		<td><?php echo ($allscore['prgans']*$prgansnum); ?>分</td>
	</tr>
	<tr>
		<td>程序填空题</td>
		<td><?php echo ($allscore['prgfill']); ?></td>
		<td><?php echo ($prgfillnum); ?>道</td>
		<td><?php echo ($allscore['prgfill']*$prgfillnum); ?>分</td>
	</tr>
	<tr>
		<td>程序设计题</td>
		<td><?php echo ((isset($allscore['programscore']) && ($allscore['programscore'] !== ""))?($allscore['programscore']):0); ?></td>
		<td><?php echo ($programnum); ?>道</td>
		<td><?php echo ($allscore['programscore']*$programnum); ?>分</td>
	</tr>
	<tr>
		<td>总计</td>
		<td>-----</td>
		<td>-----</td>
		<td><?php echo ($allscore['choosescore']*$choosenum+$allscore['judgescore']*$judgenum+$allscore['fillscore']*$fillnum+$allscore['prgans']*$prgansnum+$allscore['prgfill']*$prgfillnum+$allscore['programscore']*$programnum); ?>分</td>
	</tr>
</tbody>
</table>

</div>
<script type="text/javascript">
$(function(){
	$("#exam_index").addClass('active');
});
var deltoexamurl = "<?php echo U('Teacher/Problem/delpte');?>";
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