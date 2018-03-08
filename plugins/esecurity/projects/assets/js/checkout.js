function getStates(element) {
    var $form = $(element).closest('form');
    var id = $(element).val();

    $form.request('onGetStates', {
        data: {id: id},
        update: {'Checkout::_states': '#select-state'},
    });
}