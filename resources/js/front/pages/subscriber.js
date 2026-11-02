listenClick('.subscriber-delete-btn', function () {
    let subscriberId = $(this).attr('data-id')
    deleteItem(route('subscribers.destroy', subscriberId),
        Lang.get('js.subscribers'))
})

listenSubmit('#subscribeForm', function (e) {
    e.preventDefault()

    const $form = $(this)
    const $submitButton = $form.find('button[type="submit"]')

    $('.alert').remove()
    $submitButton.prop('disabled', true)
    
    $form[0].reset()

    setTimeout(function() {
        $submitButton.prop('disabled', false)
    }, 3000)
})
