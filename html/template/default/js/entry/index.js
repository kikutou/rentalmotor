$(function() {
    $('#zip-search').click(function() {
        AjaxZip3.zip2addr('entry[zip][zip01]', 'entry[zip][zip02]', 'entry[address][pref]', 'entry[address][addr01]');
    });

    initCategory();
    getCategory();
});

function initCategory() {
    $(
        "#entry_category_1_4, #entry_category_2_4, #entry_category_3_4,"
        + "#entry_category_1_2, #entry_category_2_2, #entry_category_3_2,"
        + "#entry_category_1_3, #entry_category_2_3, #entry_category_3_3"
    ).children("[value!='']").remove();

    $("#entry_category_1_1, #entry_category_2_1, #entry_category_3_1").val('');
}

function getCategory() {
    $(
        "#entry_category_1_1, #entry_category_2_1, #entry_category_3_1,"
        + "#entry_category_1_2, #entry_category_2_2, #entry_category_3_2,"
        + "#entry_category_1_3, #entry_category_2_3, #entry_category_3_3"
    ).change(function () {
        var level = /^.*(\d)$/.exec($(this).attr('id'))[1];
        var parent_id = $(this).val();
        var next = $("#" + $(this).attr('id').replace(/^(.*)\d$/, "$1" + (parseInt(level) + 1)));

        if (parent_id === '') {
            var child;
            for (var i = level; i < 4; i++) {
                child = $("#" + $(this).attr('id').replace(/^(.*)\d$/, "$1" + (parseInt(i) + 1)));
                //child.attr('disabled', true);
                child.children("[value!='']").remove();
            }
        } else {
            getChildCategory(parent_id, next);
        }
    });
}
