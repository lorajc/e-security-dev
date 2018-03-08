$(document).ready(function () {
    $("#light-gallery").lightGallery({
        thumbnail: true,
        controls: true,
        download: false,
        hideControlOnEnd: false,

    });

    var productHeight = $('.product-content').height();
    $('.product-clear-fix').height(productHeight - 200);

    $('.product-description').find('a').each(function () {
       $(this).attr('target', '_blank')
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

$('#btn-add-cart').on('click', function () {
    var $self = $(this);
    var id = $self.data('product-id');
    $.request('Basket::onAddCart', {
        data: {id: id},
        update: {'Basket::default': '.shop-cart'},
        beforeUpdate: function (data) {
            toastr.info(data.message, null, opts);
            $self.prop('disabled', true);
            $('#one-click-link').remove();
        }

    })
});
