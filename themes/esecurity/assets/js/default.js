var star = '<span class="field-required">&nbsp;</span>';

$(document).ready(function () {
    $('input, textarea', 'form').each(function () {
        if ($(this).prop('required')) {
            var $wrap = $(this).closest('div[class=form-group]');
            $wrap.find('label').append(star);
        }
    });

});