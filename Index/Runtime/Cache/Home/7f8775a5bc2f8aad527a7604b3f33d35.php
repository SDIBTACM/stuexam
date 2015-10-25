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
<script type="text/javascript">
var isalert = false;
var left=<?php echo ($lefttime); ?>*1000;
var savetime=(300+<?php echo ($randnum); ?>%420)*1000;
var runtimes=0;
function GetRTime(){
	nMS=left-runtimes*1000;
	if(nMS>0){
		var nH=Math.floor(nMS/(1000*60*60)); 
		var nM=Math.floor(nMS/(1000*60)) % 60;
		var nS=Math.floor(nMS/1000) % 60;
		document.getElementById("RemainH").innerHTML=(nH>=10?nH:"0"+nH);
		document.getElementById("RemainM").innerHTML=(nM>=10?nM:"0"+nM);
		document.getElementById("RemainS").innerHTML=(nS>=10?nS:"0"+nS);
		if(nMS<=5*60*1000&&isalert==false){
			$('.tixinga').css("color","red");
			$('.tixingb').css("color","red");
			isalert=true;
		}
		if(nMS>0&&nMS<=1*1000){
			$('#exam').submit();
		}
		//如果出现问题，注释掉下面的if语句
		if(nMS%savetime==0&&nMS>savetime){
			saveanswer();
			setTimeout('history.go(0)',5000);
		}
		runtimes++;
		setTimeout("GetRTime()",1000);
	}
}
window.onload=GetRTime;
</script>

<style>
	.nocopy{
		-moz-user-select:none;
	}
	body{
		padding-top:0 !important; 
	}
</style>
<body oncontextmenu="return false" onselectstart="return false" class="nocopy">
  <div class="container">
	<div style="text-align:center">
		<h1>程序设计考试系统<br>
		<small>考试名称:<?php echo ($row['title']); ?></small>
		</h1>
	</div>
	<table class='mytxtable'>
		<tr><td class='tixinga'><h4>距考试结束还有:</h4></td></tr>
		<tr><td class='tixingb'><h4><strong id="RemainH">XX</strong>:<strong id="RemainM">XX</strong>:<strong id="RemainS">XX</strong></h4></td></tr>
		<tr><td class='tixing'><button type='button' id='saveanswer' onclick='saveanswer()' class='btn btn-info'>保存答案<span id="saveover"></span></button></td></tr>
		<tr><td><button type="button" id="examsubmit" class='btn btn-danger' data-toggle="modal" data-target="#myModal">交卷</button></td></tr>
	</table>
	<form name="exam" id="exam" action="<?php echo U('Home/Exam/submitpaper');?>" method="post">
		<input type="hidden" name="eid" value="<?php echo ($_GET['eid']); ?>">
		<h4>一.选择题</h4>
		<?php if(is_array($choosesx)): foreach($choosesx as $numofchoose=>$csx): ?><div class="panel panel-default">
			<div class="panel-heading"><font color='red'>(<?php echo ($allscore['choosescore']); ?> 分)</font><?php echo ($numofchoose+1); ?>.<?php echo ($chooseans[$csx]['question']); ?></div>
			<div class="panel-body">
			  <p>
			  	<div class="radio"><label>
				<?php if(isset($choosearr[$chooseans[$csx]['choose_id']]) and $choosearr[$chooseans[$csx]['choose_id']] == 'A'): ?><input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="A" checked>(A) <?php echo ($chooseans[$csx]['ams']); ?><br/>
				<?php else: ?>
				  <input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="A">(A) <?php echo ($chooseans[$csx]['ams']); ?><br/><?php endif; ?></label></div>
				<div class="radio"><label>
				<?php if(isset($choosearr[$chooseans[$csx]['choose_id']]) and $choosearr[$chooseans[$csx]['choose_id']] == 'B'): ?><input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="B" checked>(B) <?php echo ($chooseans[$csx]['bms']); ?><br/>
				<?php else: ?>
				  <input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="B">(B) <?php echo ($chooseans[$csx]['bms']); ?><br/><?php endif; ?></label></div>
				<div class="radio"><label>
				<?php if(isset($choosearr[$chooseans[$csx]['choose_id']]) and $choosearr[$chooseans[$csx]['choose_id']] == 'C'): ?><input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="C" checked>(C) <?php echo ($chooseans[$csx]['cms']); ?><br/>
				<?php else: ?>
				  <input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="C">(C) <?php echo ($chooseans[$csx]['cms']); ?><br/><?php endif; ?></label></div>
				<div class="radio"><label>
				<?php if(isset($choosearr[$chooseans[$csx]['choose_id']]) and $choosearr[$chooseans[$csx]['choose_id']] == 'D'): ?><input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="D" checked>(D) <?php echo ($chooseans[$csx]['dms']); ?>
				<?php else: ?>
				  <input class="xzda" type="radio" name="xzda<?php echo ($chooseans[$csx]['choose_id']); ?>" value="D">(D) <?php echo ($chooseans[$csx]['dms']); endif; ?></label></div>
			  </p>
			</div>
		  </div><?php endforeach; endif; ?>

		<h4>二.判断题</h4>
		<?php if(is_array($judgesx)): foreach($judgesx as $numofjudge=>$jsx): ?><div class="panel panel-default">
				<div class="panel-heading"><font color='red'>(<?php echo ($allscore['judgescore']); ?> 分)</font><?php echo ($numofjudge+1); ?>.<?php echo ($judgeans[$jsx]['question']); ?></div>
				<div class="panel-body">
				  <p>
				  	<div class="radio"><label>
					<?php if(isset($judgearr[$judgeans[$jsx]['judge_id']]) and $judgearr[$judgeans[$jsx]['judge_id']] == 'Y'): ?><input class="pdda" type="radio" name="pdda<?php echo ($judgeans[$jsx]['judge_id']); ?>" value="Y" checked>Ture
					<?php else: ?>
						<input class="pdda" type="radio" name="pdda<?php echo ($judgeans[$jsx]['judge_id']); ?>" value="Y">Ture<?php endif; ?></label></div>
					<div class="radio"><label>
					<?php if(isset($judgearr[$judgeans[$jsx]['judge_id']]) and $judgearr[$judgeans[$jsx]['judge_id']] == 'N'): ?><input class="pdda" type="radio" name="pdda<?php echo ($judgeans[$jsx]['judge_id']); ?>" value="N" checked>False
					<?php else: ?>
						<input class="pdda" type="radio" name="pdda<?php echo ($judgeans[$jsx]['judge_id']); ?>" value="N">False<?php endif; ?></label></div>
				  </p>
				</div>
			</div><?php endforeach; endif; ?>

		<h4>三.填空题</h4>
		<?php if(is_array($fillsx)): foreach($fillsx as $numoffill=>$fsx): ?><div class="panel panel-default">
				<div class="panel-heading">
					<?php if($fillans[$fsx]['kind'] == 1): ?><font color='red'>(<?php echo ($allscore['fillscore']*$fillans[$fsx]['answernum']); ?> 分)</font>
					<?php elseif($fillans[$fsx]['kind'] == 2): ?>
						<font color='red'>(<?php echo ($allscore['prgans']); ?> 分)</font>
					<?php else: ?>
						<font color='red'>(<?php echo ($allscore['prgfill']); ?> 分)</font><?php endif; ?>
				<?php echo ($numoffill+1); ?>.<?php echo ($fillans[$fsx]['question']); ?></div>
				<div class="panel-body">
				  <p>
					<?php $__FOR_START_612260484__=1;$__FOR_END_612260484__=$fillans[$fsx]['answernum']+1;for($i=$__FOR_START_612260484__;$i < $__FOR_END_612260484__;$i+=1){ ?>答案<?php echo ($i); ?>.<input type="text" maxlength="100" name="<?php echo ($fillans[$fsx]['fill_id']); ?>tkda<?php echo ($i); ?>" value="<?php echo ((isset($fillarr[$fillans[$fsx]['fill_id']][$i]) && ($fillarr[$fillans[$fsx]['fill_id']][$i] !== ""))?($fillarr[$fillans[$fsx]['fill_id']][$i]):''); ?>" class='form-control'><br/><?php } ?>
				  </p>
				</div>
			</div><?php endforeach; endif; ?>
	</form>

		<h4>四.程序设计题</h4>
		<?php if(is_array($programans)): foreach($programans as $numofprogram=>$pans): ?><div class="panel panel-default">
				<div class="panel-heading"><font color='red'>(<?php echo ($allscore['programscore']); ?> 分)</font>
				第<?php echo ($numofprogram+1); ?>题.<?php echo (nl2br($pans['title'])); ?><a class="btn-sm btn btn-success" data-toggle="collapse" href="#collapseExample<?php echo ($pans['program_id']); ?>" aria-expanded="false" aria-controls="collapseExample">点击开闭
				</a></div>

				<div class="collapse" id="collapseExample<?php echo ($pans['program_id']); ?>">
				<div class="panel-body">
					<h4><strong>Description</strong></h4>
					<p><?php echo ($pans['description']); ?></p>
					<h4><strong>Input</strong></h4>
					<p><?php echo ($pans['input']); ?></p>
					<h4><strong>Output</strong></h4>
					<p><?php echo ($pans['output']); ?></p>
					<h4><strong>Sample Input</strong></h4>
					<p><?php echo (nl2br($pans['sample_input'])); ?></p>
					<h4><strong>Sample Output</strong></h4>
					<p><?php echo (nl2br($pans['sample_output'])); ?></p>
					<p>
						<label for="code<?php echo ($pans['program_id']); ?>">Code here:</label>
						<textarea class='form-control' id="code<?php echo ($pans['program_id']); ?>" name="code<?php echo ($pans['program_id']); ?>" rows="30">
						</textarea>
					</p>
				</div>
				<div class="panel-footer row">
					<div class="col-md-2">
						<select id="language<?php echo ($pans['program_id']); ?>" class='form-control'>
							<option value="0">C</option>
							<option value="1" selected>C++</option>
							<option value="2">Pascal</option>		
							<option value="3">Java</option>
						</select>
					</div>
					<div class="col-md-2">
					<button type="button" class="btn btn-success" onclick="submitcode('span<?php echo ($pans['program_id']); ?>','code<?php echo ($pans['program_id']); ?>','language<?php echo ($pans['program_id']); ?>','<?php echo ($pans['program_id']); ?>','<?php echo ($_GET['eid']); ?>')">提交</button>
					</div>
					<div class="col-md-3">
						<button class='btn btn-info' onclick="updateresult(this,'span<?php echo ($pans['program_id']); ?>','<?php echo ($pans['program_id']); ?>','<?php echo ($_GET['eid']); ?>')">点击刷新结果</button>
					</div>
					<div class="col-md-4">
						<span id="span<?php echo ($pans['program_id']); ?>"><font color='green' size='3px'>未提交</font></span>
					</div>
				</div>
				</div>
			</div><?php endforeach; endif; ?>
	</div>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">操作确认</h4>
			</div>
			<div class="modal-body">确定要提交试卷吗？提交后无法撤销!
			</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
			<button type="button" class="btn btn-primary" onclick="submitpaper()">确定</button>
		</div>
		</div>
		</div>
	</div>
	<script type="text/javascript" src="/stuexam/Public/Js/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="/stuexam/Public/Js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/stuexam/Public/Js/Home.min.js"></script>
</body>
<script type="text/javascript">
$(function(){
	$("body").keydown(function(event){
		if(event.keyCode==116){
			event.returnValue=false;
			alert("当前设置不允许使用F5刷新键");
			return false;
		  }
		if((event.ctrlKey)&&(event.keyCode==83)){
			event.returnValue=false;
			alert("当前设置不允许使用Ctrl+S键");
			return false;
		}
		if(event.keyCode==123){
			event.returnValue=false;
			alert("当前设置不允许使用F12键");
			return false;
		}
	});
});
var answersaveurl = "<?php echo U('Home/Exam/saveanswer');?>";
var codesubmiturl = "<?php echo U('Home/Exam/prgsubmit');?>";
var updresulturl = "<?php echo U('Home/Exam/updresult');?>";
</script>
</html>