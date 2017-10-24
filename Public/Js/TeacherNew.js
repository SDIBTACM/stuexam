/**
 * Created by jiaying on 25/10/2017.
 */
function addProblem2Exam(url, examId, problemId, type, that) {
    $.ajax({
        url: url,
        type: "POST",
        dataType: "html",
        data: "eid=" + examId + "&id=" + problemId + "&type=" + type + "&sid=" + Math.random(),
        success: function (t) {
            var str = '<a href="javascript:void(0);" class="deltoexam" data-pid="' + problemId +
                '" data-type="'+ type + '"> <span class="glyphicon glyphicon-remove" style="color: darkred"></span> </a>';
            $(that).parent().html(str);
        },
        error: function() {
            alert("添加题目失败, 请刷新重试");
        }
    });
}

function deleteProblem2Exam(url, examId, problemId, type, that, isSelect) {
    $.ajax({
        url: url,
        type: "POST",
        dataType: "html",
        data: "eid=" + examId + "&id=" + problemId + "&type=" + type + "&sid=" + Math.random(),
        success: function (t) {
            if ("ok" == t) {
                if (!isSelect) {
                    var r = $(that).parent().parent();
                    $(r).slideUp("slow", function () {
                        $(this).remove();
                    });
                } else {
                    var str = '<a href="javascript:void(0);" class="addtoexam" data-pid="' + problemId +
                        '" data-type="'+ type + '"> <span class="glyphicon glyphicon-plus"></span> </a>';
                    $(that).parent().html(str);
                }
            } else {
                alert(t);
            }
        },
        error: function() {
            alert("题目删除失败, 请刷新重试~");
        }
    });
}