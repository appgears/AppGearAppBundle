$(document).ready(function(){
    $('.widget_text_max_lines_expand').click(function() {
        $(this).prev('.widget_text_max_lines').get(0).style.maxHeight='inherit';
        $(this).hide();
        return false;
    })
});