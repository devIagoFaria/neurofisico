import $ from 'jquery'
window.$ = window.jQuery = $

// plugins
import 'bootstrap'
import 'bootstrap-select'
import 'bootstrap-datepicker'
import 'slick-carousel'
import 'magnific-popup'

// intl tel input
import intlTelInput from 'intl-tel-input'
import 'intl-tel-input/dist/css/intlTelInput.css'
import 'intl-tel-input/dist/js/utils.js'

window.intlTelInput = intlTelInput

// ===============================

document.addEventListener('DOMContentLoaded', loadPhoneNumberCountryCodeData);

function loadPhoneNumberCountryCodeData() {
    loadPhoneNumberCountryCode()
    userCreateForm()
    userEditForm()
    vcardEditForm()
    createSetting()
}

function loadPhoneNumberCountryCode() {

    if (!$('#phoneNumber').length) {
        return false
    }

    let input = document.querySelector('#phoneNumber'),
        errorMsg = document.querySelector('#error-msg'),
        validMsg = document.querySelector('#valid-msg')

    let errorMap = [
        Lang.get('js.invalid_number'),
        Lang.get('js.invalid_country_number'),
        Lang.get('js.too_short'),
        Lang.get('js.too_long'),
        Lang.get('js.invalid_number')
    ]

    // ✅ initialise plugin (FIXED)
    let intl = window.intlTelInput(input, {
        initialCountry: defaultCountryCodeValue || 'auto',
        separateDialCode: true,
        geoIpLookup: function (success) {
            $.get('https://ipinfo.io', function () { }, 'jsonp')
                .always(function (resp) {
                    let countryCode = (resp && resp.country) ? resp.country : 'us'
                    success(countryCode)
                })
        }
        // ❌ removed utilsScript (not needed in new version)
    })

    let reset = function () {
        input.classList.remove('error')
        errorMsg.innerHTML = ''
        errorMsg.classList.add('d-none')
        validMsg.classList.add('d-none')
    }

    input.addEventListener('blur', function () {
        reset()
        if (input.value.trim()) {
            if (intl.isValidNumber()) {
                validMsg.classList.remove('d-none')
            } else {
                input.classList.add('error')
                let errorCode = intl.getValidationError()
                errorMsg.innerHTML = errorMap[errorCode] || ''
                errorMsg.classList.remove('d-none')
            }
        }
    })

    input.addEventListener('change', reset)
    input.addEventListener('keyup', reset)

    if (typeof phoneNo !== 'undefined' && phoneNo !== '') {
        setTimeout(function () {
            $('#phoneNumber').trigger('change')
        }, 500)
    }

    // ✅ SAFE EVENT
    $('#phoneNumber').on('blur keyup change countrychange', function () {

        if (typeof phoneNo !== 'undefined' && phoneNo !== '') {
            intl.setNumber('+' + phoneNo)
            phoneNo = ''
        }

        let countryData = intl.getSelectedCountryData()
        let getCode = countryData && countryData.dialCode ? countryData.dialCode : ''

        $('#prefix_code').val(getCode)
    })

    // ✅ INITIAL SET
    let countryData = intl.getSelectedCountryData()
    let getCode = countryData && countryData.dialCode ? countryData.dialCode : ''
    $('#prefix_code').val(getCode)

    let getPhoneNumber = $('#phoneNumber').val() || ''
    let removeDashPhoneNumber = getPhoneNumber.replaceAll('-', ' ')
    let removeSpacePhoneNumber = removeDashPhoneNumber.replace(/\s/g, '')

    $('#phoneNumber').val(removeSpacePhoneNumber)

    $('#phoneNumber').focus()
    $('#phoneNumber').trigger('blur')
}

// ✅ SAFE CLICK HANDLER
$(document).on('click', '.iti__country', function () {
    let flagClass = $('.iti__selected-flag>.iti__flag').attr('class') || ''
    flagClass = flagClass ? flagClass.split(/\s+/)[1] : ''

    let dialCodeVal = $('.iti__selected-dial-code').text() || ''

    window.localStorage.setItem('flagClassLocal', flagClass)
    window.localStorage.setItem('dialCodeValLocal', dialCodeVal)
})

// ===============================

function userCreateForm() {
    if (!$('#userCreateForm').length) return false

    $('#userCreateForm').submit(function () {
        if ($('#error-msg').text() !== '') {
            $('#phoneNumber').focus()
            return false
        }
    })
}

function vcardEditForm() {
    if (!$('#editForm').length) return false

    $('#editForm').submit(function () {
        if ($('#error-msg').text() !== '') {
            $('#phoneNumber').focus()
            return false
        }
    })
}

function createSetting() {
    if (!$('#createSetting').length) return false

    $('#createSetting').submit(function () {
        if ($('#error-msg').text() !== '') {
            $('#phoneNumber').focus()
            return false
        }
    })
}

function userEditForm() {
    if (!$('#userEditForm').length) return false

    $('#userEditForm').submit(function () {
        if ($('#error-msg').text() !== '') {
            $('#phoneNumber').focus()
            return false
        }
    })
}