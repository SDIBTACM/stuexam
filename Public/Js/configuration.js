/**
 * Created by jiaying on 13/11/2016.
 */
$(function () {
    $("#chapterSelect").change(function () {
        var that = $(this);
        var chapterId = that.children('option:selected').val();
        updateParentSelect(chapterId);
    });
});


function updateParentSelect(chapterId) {
    var parentSelect = $("#parentSelect");
    parentSelect.empty().append('<option value="0" selected>请选择父级知识点</option>');
    if (chapterId <= 0) {
        return;
    }
    $.ajax({
        url: getParentNodeLink,
        type: "GET",
        dataType: "json",
        data: {"chapterId": chapterId},
        success: function (parents) {
            $.each(parents, function (index, _parent) {
                parentSelect.append('<option value="' + _parent.id + '">' + _parent.name + '</option>');
            });
        },
        error: function () {
            alert("sorry,something error")
        }
    });
}

function updateChildrenSelect(parentId) {
    var pointSelect = $("#pointSelect");
    pointSelect.empty().append('<option value="0" selected>请选择子级知识点</option>');
    if (parentId <= 0) {
        return;
    }
    $.ajax({
        url: getChildrenNodeLink,
        type: "GET",
        dataType: "json",
        data: {"parentId": parentId},
        success: function (children) {
            var pointSelect = $("#point");
            $.each(children, function (index, child) {
                pointSelect.append('<option value="' + child.id + '">' + child.name + '</option>');
            });
        },
        error: function () {
            alert("sorry,something error");
        }
    });
}