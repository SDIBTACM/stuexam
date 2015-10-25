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

<script type="text/javascript" src="/stuexam/Public/Js/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="/stuexam/Public/Js/Teacher.min.js"></script>
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

<div class="exam_content container">
	<?php if(isset($row['choose_id'])): ?><h1>编辑选择题</h1>
	<?php else: ?>
		<h1>添加选择题</h1><?php endif; ?>
	
	<hr>
	<form class='form-horizontal' method="post" action="<?php echo U('Teacher/Add/choose');?>" onSubmit="return chkchoose(this)">
		<div class="form-group">
		  <label for="choose_des" class="control-label col-md-2">题目描述:</label>
		  <div class="col-md-8">
		  <textarea id='choose_des' name="choose_des" class="form-control" rows="6"><?php echo ((isset($row['question']) && ($row['question'] !== ""))?($row['question']):""); ?></textarea></div>
		</div>
		<div class="form-group">
			<label for="ams" class="col-md-2 control-label">选项A:</label>
			<div class="col-md-3">
				<textarea name="ams" id="ams" class="form-control" rows="2" ><?php echo ((isset($row['ams']) && ($row['ams'] !== ""))?($row['ams']):""); ?></textarea>
			</div>
			<label for="bms" class="col-md-2 control-label">选项B:</label>
			<div class="col-md-3">
				<textarea name="bms" id="bms" class="form-control" rows="2" ><?php echo ((isset($row['bms']) && ($row['bms'] !== ""))?($row['bms']):""); ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label for="cms" class="col-md-2 control-label">选项C:</label>
			<div class="col-md-3">
				<textarea name="cms" id="cms" class="form-control" rows="2" ><?php echo ((isset($row['cms']) && ($row['cms'] !== ""))?($row['cms']):""); ?></textarea>
			</div>
			<label for="dms" class="col-md-2 control-label">选项D:</label>
			<div class="col-md-3">
				<textarea name="dms" id="dms" class="form-control" rows="2" ><?php echo ((isset($row['dms']) && ($row['dms'] !== ""))?($row['dms']):""); ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class='col-md-2 control-label'>答案：</label>
			<div class='col-md-7'>
			<label class="radio-inline">
			<?php if(isset($row['answer']) and $row['answer'] == 'A'): ?><input type="radio" name="answer" value="A" checked> A
			<?php else: ?>
			<input type="radio" name="answer" value="A"> A<?php endif; ?>
			</label>

			<label class="radio-inline">
			<?php if(isset($row['answer']) and $row['answer'] == 'B'): ?><input type="radio" name="answer" value="B" checked> B
			<?php else: ?>
			<input type="radio" name="answer" value="B"> B<?php endif; ?>
			</label>

			<label class="radio-inline">
			<?php if(isset($row['answer']) and $row['answer'] == 'C'): ?><input type="radio" name="answer" value="C" checked> C
			<?php else: ?>
			<input type="radio" name="answer" value="C"> C<?php endif; ?>
			</label>

			<label class="radio-inline">
			<?php if(isset($row['answer']) and $row['answer'] == 'D'): ?><input type="radio" name="answer" value="D" checked> D
			<?php else: ?>
			<input type="radio" name="answer" value="D"> D<?php endif; ?>
			</label>

			</div>
		</div>
		<div class="form-group">
			<label for="point" class='col-md-2 control-label'>知识点:</label>
			<div class="col-md-2">
			<select class='form-control' name="point" id="point">
				<?php if(is_array($pnt)): foreach($pnt as $key=>$p): if(isset($row['point']) and $row['point'] == $p['point']): ?><option value="<?php echo ($p['point']); ?>" selected><?php echo ($p['point']); ?></option>
					<?php else: ?>
					<option value="<?php echo ($p['point']); ?>"><?php echo ($p['point']); ?></option><?php endif; endforeach; endif; ?>
			</select>
			</div>
			<label class='col-md-1 control-label' for="easycount">难度系数:</label>
			<div class="col-md-2">
			<select class='form-control' name="easycount" id="easycount">
				<?php $__FOR_START_1308894498__=0;$__FOR_END_1308894498__=11;for($i=$__FOR_START_1308894498__;$i < $__FOR_END_1308894498__;$i+=1){ if(isset($row['easycount']) and $row['easycount'] == $i): ?><option value="<?php echo ($i); ?>" selected><?php echo ($i); ?></option>
					<?php else: ?>
						<option value="<?php echo ($i); ?>"><?php echo ($i); ?></option><?php endif; } ?>
			</select>
			</div>
			<label class='col-md-1 control-label' for="isprivate">题库类型:</label>
			<div class="col-md-2">
			<select class='form-control' name="isprivate" id="isprivate" onchange="showmsg()">
				<?php if(isset($row['isprivate']) and $row['isprivate'] == 0): ?><option value="0" selected>公共题库</option>
				<?php else: ?>
					<option value="0">公共题库</option><?php endif; ?>
				<?php if(isset($row['isprivate']) and $row['isprivate'] == 1): ?><option value="1" selected>私人题库</option>
				<?php else: ?>
					<option value="1">私人题库</option><?php endif; ?>
				<?php if(isset($row['isprivate']) and $row['isprivate'] == 2): ?><option value="2" selected>系统隐藏</option>
				<?php else: ?>
					<option value="2">系统隐藏</option><?php endif; ?>
			</select>
			</div>
		</div>
		<input type='hidden' name="postkey" value="<?php echo ($mykey); ?>">
		<input type='hidden' name='page' value="<?php echo ($page); ?>">
		<?php if(isset($row['choose_id'])): ?><input type="hidden" name="chooseid" value="<?php echo ($row['choose_id']); ?>"><?php endif; ?>
		<div class="form-group">
			<div class="alert alert-warning" role="alert" id='msg' style="display:none"></div>
		</div>
		<div class="col-md-offset-2 col-md-8">
			<button class="btn btn-primary col-md-6" type="submit">Submit</button>
			<?php if(isset($row['choose_id'])): ?><button class="btn btn-danger col-md-6" type="button" onclick="javascript:history.go(-1);">Back</button>
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
<script type="text/javascript" src="/stuexam/Public/Js/bootstrap.min.js"></script>
</body>
</html>