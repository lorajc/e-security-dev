/**
 * Created by jclora on 2016-02-09.
 */
function saveUserForm(element) {
    var $form = $(element).closest('form');
    var redirect = $form.data('redirect');
    var validate = $form.parsley().validate();
    if (validate) {
        $form.request('onUpdate', {
            //redirect: redirect
        });
    }

}

$('#orders').on('click', '.pagination a', function (event) {
    var page = $(this).text();
    event.preventDefault();
    if ($(this).attr('href') != '#') {
        $("html, body").animate({scrollTop: 0}, "fast");
        $.request('onLoadPagination', {
            data: {page: page},
            update:  {'UserUpdate::_orders': '#orders'}
        });
    }
});