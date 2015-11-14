/**
 * Created by jiaying on 15/11/14.
 */
function submitChoosePaper() {
    $("#chooseExam").submit();
}

function submitJudgePaper() {
    $("#judgeExam").submit();
}

function submitFillPaper() {
    $("#fillExam").submit();
}

function submitProgramPaper() {

}

function savePaper(saveUrl) {
    $.ajax({
        url: saveUrl,
        type: "POST",
        dataType: "html",
        data: $("#exam").serialize(),
        success: function(e) {
            "ok" == e ? ($("#saveover").html("[已保存]"), setTimeout(function() {
                $("#saveover").html("")
            }, 6e3)) : $("#saveover").html(e)
        },
        error: function() {
            alert("something error when you save")
        }
    })
}

function saveChoosePaper() {
    savePaper(chooseSaveUrl);
}

function saveJudgePaper() {
    savePaper(judgeSaveUrl);
}

function saveFillPaper() {
    savePaper(fillSaveUrl);
}

function antiCheat() {
    $("body").keydown(function (event) {
        if (event.keyCode == 116) {
            event.returnValue = false;
            return false;
        }
        //if (event.ctrlKey) {
        //    event.returnValue = false;
        //    return false;
        //}
        //if (event.altKey) {
        //    event.returnValue = false;
        //    return false;
        //}
        if (event.keyCode == 123) {
            event.returnValue = false;
            return false;
        }
    });
    //}).mouseleave(function () {
    //    alert('xxx');
    //});
}

var isalert = false;
var runtimes = 0;
function GetRTime() {
    var nMS = left - runtimes * 1000;
    if (nMS > 0) {
        var nH = Math.floor(nMS / (1000 * 60 * 60));
        var nM = Math.floor(nMS / (1000 * 60)) % 60;
        var nS = Math.floor(nMS / 1000) % 60;
        var nHstr = (nH >= 10 ? nH : "0" + nH);
        var nMstr = (nM >= 10 ? nM : "0" + nM);
        var nSstr = (nS >= 10 ? nS : "0" + nS);
        $("#RemainH").html(nHstr);
        $("#RemainM").html(nMstr);
        $("#RemainS").html(nSstr);
        if (nMS <= 5 * 60 * 1000 && isalert == false) {
            $('.tixinga').css("color", "red");
            $('.tixingb').css("color", "red");
            isalert = true;
        }
        if (nMS > 0 && nMS <= 1000) {
            switch (questionType) {
                case 1 :
                    submitChoosePaper();
                    break;
                case 2 :
                    submitJudgePaper();
                    break;
                case 3 :
                    submitFillPaper();
                    break;
                default :
                    submitProgramPaper();
                    break;
            }
        }

        if (nMS % savetime == 0 && nMS > savetime) {
            switch (questionType) {
                case 1 :
                    saveChoosePaper();
                    break;
                case 2 :
                    saveJudgePaper();
                    break;
                case 3 :
                    saveFillPaper();
                    break;
            }
            //saveanswer();
            //setTimeout('history.go(0)', 5000);
        }
        runtimes++;
        setTimeout("GetRTime()", 1000);
    }
}