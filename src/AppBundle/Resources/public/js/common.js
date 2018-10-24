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
        var dataset = this.dataset;

        if (dataset.appgearWidgetActionConfirm && !confirm('Are you sure?')) {
            return false;
        }

        if (dataset.appgearWidgetActionAjax || dataset.appgearWidgetActionPost) {
            var method = dataset.appgearWidgetActionPost ? 'POST' : 'GET';

            var loadingToast = toastr.info('Loading...', null, {timeOut: 0});

            $.ajax({
                url: this.href,
                method: method,
                data: JSON.parse(dataset.appgearWidgetActionPostParameters || '[]'),
                success: function (data, textStatus, xhr) {
                    var message = data || textStatus.capitalize();
                    $(loadingToast).closest('.toast').remove();

                    switch (dataset.appgearWidgetActionPayload) {
                        case 'reload':
                            location.reload();
                            break;
                        case 'alert':
                            alert(message);
                            break;
                        default:
                            toastr.success(message, null, {timeOut: 5000, closeButton: true})
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    $(loadingToast).closest('.toast').remove();
                    toastr.error(errorThrown, null, {timeOut: 2500, closeButton: true})
                }
            });

            return false;
        }
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

    /**
     * Selectize forms dropdown's who contains more that 20 items
     */
    $('form.appgear-form select').each(function (index, item) {
        if (item.options.length > 20) {
            $(item).selectize();
        }
    });

    /**
     * Selectize list filters dropdown's
     */
    $('form.appgear-list-filters select').selectize({

        /* https://github.com/selectize/selectize.js/issues/600#issuecomment-85737816 */
        inputClass: 'form-control selectize-input',
        dropdownParent: "body"
    });
});

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}