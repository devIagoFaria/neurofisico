listenClick('#enquiryResetFilter', function () {
    let allEnquiry = $('#allEnquiry').val();
    $('#enquiriesStatus').val(allEnquiry).trigger('change')
    hideDropdownManually($('#enquiryFilterBtn'), $('.dropdown-menu'));
})

listenChange('#enquiriesStatus', function () {
    Livewire.dispatch('changeStatusFilter', { value: $(this).val(),})
})

listenClick('.enquiry-delete-btn', function () {
    let enquiryRecordId = $(this).attr('data-id')
    deleteItem(route('enquiries.destroy', enquiryRecordId), Lang.get('js.enquiry'))
})

listenSubmit('.ajax-form', function (e) {
    e.preventDefault();
    let $form = $(this);
    let $submitBtn = $form.find('button[type="submit"]');
    let originalBtnText = $submitBtn.html();

    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: $form.serialize(),
        success: function (response) {
            if (response.success) {
                let alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-check-circle"></i> ' + (response.message || '__("messages.flash.messages_sent")') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                $('.ajax-message').html(alertHtml).show();
                $form[0].reset();
            } else {
                let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-exclamation-circle"></i> ' + (response.message || '__("messages.flash.something_went_wrong")') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                $('.ajax-message').html(alertHtml).show();
            }
        },
        complete: function () {
            $submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});
