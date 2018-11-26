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
                '" data-type="' + type + '"> <span class="glyphicon glyphicon-remove" style="color: darkred"></span> </a>';
            $(that).parent().html(str);
        },
        error: function () {
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
                        '" data-type="' + type + '"> <span class="glyphicon glyphicon-plus"></span> </a>';
                    $(that).parent().html(str);
                }
            } else {
                alert(t);
            }
        },
        error: function () {
            alert("题目删除失败, 请刷新重试~");
        }
    });
}

function addProgramInput() {
    var numAnswer = $("#numanswer");
    var e = numAnswer.val();
    if (8 > e) {
        e++;
        var t = $("<div>", {
                id: "divans" + e,
                "class": "col-md-5"
            }),
            r = $("<input>", {
                type: "text",
                "class": "form-control",
                name: "answer" + e,
                id: "answer" + e,
                placeholder: "答案" + e
            }),
            languageDiv = $("<div>", {
                id: "div_language" + e,
                "class": "col-md-5 form-group"
            });
        var html = '<label class="checkbox-inline"> ' +
            '<input type="checkbox" name="language' + e + '[]" value="0" checked>C' +
            '</label>';
        html += '<label class="checkbox-inline"> ' +
            '<input type="checkbox" name="language' + e + '[]" value="1" checked>C++' +
            '</label>';
        html += '<label class="checkbox-inline"> ' +
            '<input type="checkbox" name="language' + e + '[]" value="3" checked>JAVA' +
            '</label>';
        languageDiv.append(html);
        t.append(r), $("#Content").append(t).append(languageDiv), numAnswer.val(e)
    } else {
        alert("空数已达到上限")
    }
}

function removeProgramInput() {
    var numAnswer = $("#numanswer");

    var e = numAnswer.val();
    e > 0 ? ($("#divans" + e).remove(), $("#div_language"+ e).remove(), e--, numAnswer.val(e)) :
        alert("Nothing to be deleted")
}
