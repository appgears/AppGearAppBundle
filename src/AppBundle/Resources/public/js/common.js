$(document).ready(function () {

    $('.widget_text_max_lines_expand').click(function () {
        $(this).prev('.widget_text_max_lines').get(0).style.maxHeight = 'inherit';
        $(this).hide();
        return false;
    });

    $('.appgear-widget-action').click(function () {

        var data = this.dataset;

        if (data.appgearWidgetActionConfirm && !confirm('Are you sure?')) {
            return;
        }

        var method = data.appgearWidgetActionPost ? 'POST' : 'GET';

        $.ajax({
            url: data.appgearWidgetActionUrl,
            method: method,
            statusCode: {
                200: function () {
                    location.reload();
                },
                403: function () {
                    alert("Access Denied!");
                }
            }
        });
    });
});