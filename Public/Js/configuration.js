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