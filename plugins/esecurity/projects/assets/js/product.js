$(document).ready(function () {
    $("#light-gallery").lightGallery({
        thumbnail: true,
        controls: true,
        download: false,
        hideControlOnEnd: false,

    });
});


function setOrder(element) {
    var $form = $(element).closest('form');

    $form.request('onSetOrder', {
        success: function (e, data) {
            alert(data.message);
        }
    });

}