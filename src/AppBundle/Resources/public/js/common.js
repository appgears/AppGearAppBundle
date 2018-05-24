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
            return false;
        }

        var parentForm = this.closest('form');
        if (parentForm !== null && parentForm !== undefined) {
            parentForm.submit();
        }

        if (data.appgearWidgetActionAjax) {
            var method = data.appgearWidgetActionPost ? 'POST' : 'GET';

            var loadingToast = toastr.info('Loading...', null, {timeOut: 0});

            $.ajax({
                url: data.appgearWidgetActionUrl,
                method: method,
                data: JSON.parse(data.appgearWidgetActionPostParameters),
                statusCode: {
                    200: function (data) {
                        if (data.length > 0) {
                            $(loadingToast).closest('.toast').remove();
                            toastr.success(data, null, {timeOut: 5000, closeButton: true, progressBar: true})
                        } else {
                            location.reload();
                        }
                    },
                    403: function () {
                        $(loadingToast).closest('.toast').remove();
                        toastr.warning('Access Denied!', null, {timeOut: 2000, closeButton: true, progressBar: true})
                    },
                    404: function () {
                        $(loadingToast).closest('.toast').remove();
                        toastr.warning('Not Found!', null, {timeOut: 2000, closeButton: true, progressBar: true})
                    },
                    422: function (data) {
                        $(loadingToast).closest('.toast').remove();
                        toastr.warning(data.responseText, null, {timeOut: 3000, closeButton: true, progressBar: true})
                    }
                }
            });
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