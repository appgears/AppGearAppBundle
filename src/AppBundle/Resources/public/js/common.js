function smartyAlert(message, type = 'success') {
    var alertHtml =
        `<div class="alert alert-` + type + ` margin-bottom-30">
            <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">×</span>
        <span class="sr-only">Close</span>
            </button>
            ${message}
        </div>`;

    $('#content').prepend(alertHtml);
}

$(document).ready(function () {

    /**
     * Expander for multiline text
     */
    $('.widget_text_max_lines_expand').click(function () {
        $(this).prev('.widget_text_max_lines').get(0).style.maxHeight = 'inherit';
        $(this).hide();
        return false;
    });

    /**
     * Action widget handler
     */
    $('.appgear-widget-action').click(function () {
        var data = this.dataset;

        if (data.appgearWidgetActionConfirm && !confirm('Are you sure?')) {
            return;
        }

        var method = data.appgearWidgetActionPost ? 'POST' : 'GET';

        $.ajax({
            url: data.appgearWidgetActionUrl,
            method: method,
            data: JSON.parse(data.appgearWidgetActionPostParameters),
            statusCode: {
                200: function (data) {
                    if (data.length > 0) {
                        smartyAlert(data);
                    } else {
                        location.reload();
                    }
                },
                403: function () {
                    smartyAlert('Access Denied!', 'warning');
                },
                404: function () {
                    smartyAlert('Not Found!', 'warning');
                }
            }
        });
    });

    /**
     * Add item handler for forms that contains collection
     */
    $('.btn-compound-add-item').click(function () {
        var group = $('[data-prototype]').filter(':parent').first();
        var prototype = group.data('prototype');

        prototype = prototype.replace(/__name__/g, group.children().length);

        group.append(prototype);
    });
});