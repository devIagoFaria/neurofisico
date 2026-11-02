document.addEventListener("DOMContentLoaded", loadFrontAppointmentData);

let frontTimezoneOffsetMinutes = new Date().getTimezoneOffset();
frontTimezoneOffsetMinutes =
    frontTimezoneOffsetMinutes === 0 ? 0 : -frontTimezoneOffsetMinutes;
let frontSelectedDate;
let frontCharge = "";
let frontPayableAmount = "";
let dateEle = "#templateAppointmentDate";

function renderNoTimeSlotMessage(message = null) {
    const msg = message || Lang.get("messages.appointment.no_slot_found");
    $("#slotGrid").addClass("d-none").html("");
    $("#noSlotMessage").removeClass("d-none").find(".no-time-slot").text(msg);
}

function loadFrontDateData() {
    if (!$("#templateAppointmentDate").length) {
        return;
    }

    $("#templateAppointmentDate").datepicker({
        language: "es-es",
        format: "yyyy-mm-dd",
        minDate: new Date(),
        startDate: new Date(),
        todayHighlight: true,
    });
}
function loadFrontAppointmentData() {
    if (!$("#templateAppointmentDate").length) {
        return;
    }
    loadFrontDateData();
    let frontSelectedDate = $("#templateAppointmentDate").val();

    if (!($("#appointmentDoctorId").val() == "")) {
        $(dateEle).removeAttr("disabled");
        $.ajax({
            url: route("get-service"),
            type: "GET",
            data: {
                appointmentDoctorId: $("#appointmentDoctorId").val(),
            },
            success: function (result) {
                if (result.success) {
                    $(dateEle).removeAttr("disabled");
                    $("#FrontAppointmentServiceId").empty();
                    $("#FrontAppointmentServiceId").append(
                        $('<option value=""></option>').text(
                            Lang.get("js.select_service"),
                        ),
                    );
                    $.each(result.data, function (i, v) {
                        $("#FrontAppointmentServiceId").append(
                            $("<option></option>")
                                .attr("value", v.id)
                                .text(v.name),
                        );
                    });
                }
            },
        });
    }

    if (
        !($("#FrontAppointmentServiceId").val() == "") &&
        $("#FrontAppointmentServiceId").length
    ) {
        $.ajax({
            url: route("get-charge"),
            type: "GET",
            data: {
                chargeId: $("#FrontAppointmentServiceId").val(),
            },
            success: function (result) {
                if (result.success) {
                    $("#payableAmountText").removeClass("d-none");
                    $("#payableAmount").text(
                        currencyIcon +
                            " " +
                            getFormattedPrice(result.data.charges),
                    );
                    frontPayableAmount = result.data.charges;
                    frontCharge = result.data.charges;
                }
            },
        });
    }

    if (!frontSelectedDate) {
        return false;
    }

    $.ajax({
        url: route("doctor-session-time"),
        type: "GET",
        data: {
            adminAppointmentDoctorId: $("#appointmentDoctorId").val(),
            date: frontSelectedDate,
            timezone_offset_minutes: frontTimezoneOffsetMinutes,
        },
        success: function (result) {
            if (result.success) {
                if (!result.data["slots"] || !result.data["slots"].length) {
                    renderNoTimeSlotMessage();
                    return;
                }
                $("#noSlotMessage").addClass("d-none");
                $("#slotGrid").removeClass("d-none").html("");

                $.each(result.data["slots"], function (index, value) {
                    if (result.data["bookedSlot"] == null) {
                        $("#slotGrid").append(
                            '<span class="badge badge-lg slots-item bg-success time-slot" data-id="' +
                                value +
                                '">' +
                                value +
                                "</span>",
                        );
                    } else {
                        if (
                            $.inArray(value, result.data["bookedSlot"]) !== -1
                        ) {
                            $("#slotGrid").append(
                                '<span class="badge badge-lg slots-item bg-success time-slot bookedSlot" data-id="' +
                                    value +
                                    '">' +
                                    value +
                                    "</span>",
                            );
                        } else {
                            $("#slotGrid").append(
                                '<span class="badge badge-lg slots-item bg-success time-slot" data-id="' +
                                    value +
                                    '">' +
                                    value +
                                    "</span>",
                            );
                        }
                    }
                });
            }
        },
        error: function (result) {
            $(".appointment-slot-data").html("");
            $(".book-appointment-message").css("display", "block");
            let response =
                '<div class="gen alert alert-danger">' +
                result.responseJSON.message +
                "</div>";
            $(".book-appointment-message")
                .html(response)
                .delay(5000)
                .hide("slow");
            renderNoTimeSlotMessage(result.responseJSON.message);
        },
    });
}

listenChange("#isPatientAccount", function () {
    if (this.checked) {
        $(".name-details").addClass("d-none");
        $(".registered-patient").removeClass("d-none");
        $("#template-medical-email").keyup(function () {
            $("#patientName").val("");
            let email = $("#template-medical-email").val();

            $.ajax({
                url: route("get-patient-name"),
                type: "GET",
                data: { email: email },
                success: function (result) {
                    if (result.data) {
                        $("#patientName").val(result.data);
                    }
                },
            });
        });
    } else {
        $(".name-details").removeClass("d-none");
        $(".registered-patient").addClass("d-none");
    }
});

$(".no-time-slot").removeClass("d-none");
listenChange(dateEle, function () {
    frontSelectedDate = $(this).val();
    $.ajax({
        url: route("doctor-session-time"),
        type: "GET",
        data: {
            adminAppointmentDoctorId: $("#appointmentDoctorId").val(),
            date: frontSelectedDate,
            timezone_offset_minutes: frontTimezoneOffsetMinutes,
        },
        success: function (result) {
            if (result.success) {
                if (!result.data["slots"] || !result.data["slots"].length) {
                    renderNoTimeSlotMessage();
                    return;
                }
                $("#noSlotMessage").addClass("d-none");
                $("#slotGrid").removeClass("d-none").html("");

                $.each(result.data["slots"], function (index, value) {
                    if (result.data["bookedSlot"] == null) {
                        $("#slotGrid").append(
                            '<span class="badge badge-lg slots-item bg-success time-slot" data-id="' +
                                value +
                                '">' +
                                value +
                                "</span>",
                        );
                    } else {
                        if (
                            $.inArray(value, result.data["bookedSlot"]) !== -1
                        ) {
                            $("#slotGrid").append(
                                '<span class="badge badge-lg slots-item bg-success time-slot bookedSlot" data-id="' +
                                    value +
                                    '">' +
                                    value +
                                    "</span>",
                            );
                        } else {
                            $("#slotGrid").append(
                                '<span class="badge badge-lg slots-item bg-success time-slot" data-id="' +
                                    value +
                                    '">' +
                                    value +
                                    "</span>",
                            );
                        }
                    }
                });
            }
        },
        error: function (result) {
            $(".appointment-slot-data").html("");
            $(".book-appointment-message").css("display", "block");
            let response =
                '<div class="gen alert alert-danger">' +
                result.responseJSON.message +
                "</div>";
            $(".book-appointment-message")
                .html(response)
                .delay(5000)
                .hide("slow");
            renderNoTimeSlotMessage(result.responseJSON.message);
        },
    });
});

listenClick(".time-slot", function () {
    if ($(".time-slot").hasClass("activeSlot")) {
        $(".time-slot").removeClass("activeSlot");
        $(this).addClass("activeSlot");
    } else {
        $(this).addClass("activeSlot");
    }
    let fromToTime = $(this).attr("data-id").split("-");
    let fromTime = fromToTime[0];
    let toTime = fromToTime[1];
    $("#timeSlot").val("");
    $("#toTime").val("");
    $("#timeSlot").val(fromTime);
    $("#toTime").val(toTime);
});

let serviceIdExist = $("#FrontAppointmentServiceId").val();

listenChange("#appointmentDoctorId", function (e) {
    e.preventDefault();
    $("#payableAmountText").addClass("d-none");
    $("#chargeId").val("");
    $("#payableAmount").val("");
    $("#templateAppointmentDate").val("");
    $("#addFees").val("");
    $("#noSlotMessage").removeClass("d-none");
    $("#slotGrid").addClass("d-none").html("");
    renderNoTimeSlotMessage();
    $(dateEle).removeAttr("disabled");
    $.ajax({
        url: route("get-service"),
        type: "GET",
        data: {
            appointmentDoctorId: $(this).val(),
        },
        success: function (result) {
            if (result.success) {
                $(dateEle).removeAttr("disabled");
                $("#FrontAppointmentServiceId").empty();
                $("#FrontAppointmentServiceId").append(
                    $('<option value=""></option>').text(
                        Lang.get("js.select_service"),
                    ),
                );
                $.each(result.data, function (i, v) {
                    $("#FrontAppointmentServiceId").append(
                        $("<option></option>")
                            .attr("value", v.id)
                            .attr("selected", v.id == serviceIdExist)
                            .text(v.name),
                    );
                });
                if (serviceIdExist && $("#FrontAppointmentServiceId").val()) {
                    $("#payableAmountText").removeClass("d-none");
                }
            }
        },
    });
});

listenChange("#FrontAppointmentServiceId", function () {
    if ($(this).val() == "") {
        $("#payableAmountText").addClass("d-none");
        return;
    }
    $.ajax({
        url: route("get-charge"),
        type: "GET",
        data: {
            chargeId: $(this).val(),
        },
        success: function (result) {
            if (result.success) {
                $("#payableAmountText").removeClass("d-none");
                $("#payableAmount").text(
                    currencyIcon + " " + getFormattedPrice(result.data.charges),
                );
                frontPayableAmount = result.data.charges;
                frontCharge = result.data.charges;
            }
        },
    });
});

listenSubmit("#frontAppointmentBook", function (e) {
    e.preventDefault();

    let firstName = ($("#template-medical-first_name").val() || "").trim();
    let lastName = ($("#template-medical-last_name").val() || "").trim();
    let email = ($("#template-medical-email").val() || "").trim();
    let doctor = ($("#appointmentDoctorId").val() || "").trim();
    let services = ($("#FrontAppointmentServiceId").val() || "").trim();
    let appointmentDate = ($("#templateAppointmentDate").val() || "").trim();
    let paymentType = ($("#paymentMethod").val() || "").trim();

    $(".error-span").remove();
    $(".is-invalid").removeClass("is-invalid");
    $(".form-select").removeClass("is-invalid");
    $(".select2-selection").removeClass("border border-danger");

    let hasError = false;
    let firstErrorElement = null;

    if (!$("#isPatientAccount").is(":checked")) {
        if (firstName == "") {
            hasError = true;
            let inputField = $("#template-medical-first_name");
            inputField.addClass("is-invalid");
            inputField.parent().append('<span class="text-danger error-span fs-small d-block mt-1">' + Lang.get("js.first_name_required") + '</span>');
            if (!firstErrorElement) firstErrorElement = inputField;
        }
        if (lastName == "") {
            hasError = true;
            let inputField = $("#template-medical-last_name");
            inputField.addClass("is-invalid");
            inputField.parent().append('<span class="text-danger error-span fs-small d-block mt-1">' + Lang.get("js.last_name_required") + '</span>');
            if (!firstErrorElement) firstErrorElement = inputField;
        }
    }

    if (email == "") {
        hasError = true;
        let inputField = $("#template-medical-email");
        inputField.addClass("is-invalid");
        inputField.parent().append('<span class="text-danger error-span fs-small d-block mt-1">' + Lang.get("js.email_required") + '</span>');
        if (!firstErrorElement) firstErrorElement = inputField;
    }
    if (doctor == "") {
        hasError = true;
        let selectField = $("#appointmentDoctorId");
        selectField.addClass("is-invalid");
        selectField.parent().find(".select2-selection").addClass("border border-danger");
        selectField.parent().append('<span class="text-danger error-span fs-small d-block mt-1">' + Lang.get("js.doctor_required") + '</span>');
        if (!firstErrorElement) firstErrorElement = selectField.parent();
    }
    if (services == "") {
        hasError = true;
        let selectField = $("#FrontAppointmentServiceId");
        selectField.addClass("is-invalid");
        selectField.parent().find(".select2-selection").addClass("border border-danger");
        selectField.parent().append('<span class="text-danger error-span fs-small d-block mt-1">' + Lang.get("js.service_required") + '</span>');
        if (!firstErrorElement) firstErrorElement = selectField.parent();
    }
    if (appointmentDate == "") {
        hasError = true;
        let inputField = $("#templateAppointmentDate");
        inputField.addClass("is-invalid");
        inputField.parent().append('<span class="text-danger error-span fs-small d-block mt-1">' + Lang.get("js.appointment_date_required") + '</span>');
        if (!firstErrorElement) firstErrorElement = inputField;
    }
    if (paymentType == "") {
        hasError = true;
        let selectField = $("#paymentMethod");
        selectField.addClass("is-invalid");
        selectField.parent().find(".select2-selection").addClass("border border-danger");
        selectField.parent().append('<span class="text-danger error-span fs-small d-block mt-1">' + Lang.get("js.payment_type_required") + '</span>');
        if (!firstErrorElement) firstErrorElement = selectField.parent();
    }

    if (hasError) {
        if (firstErrorElement) {
            $('html, body').animate({
                scrollTop: firstErrorElement.offset().top - 150
            }, 500);
        }
        return false;
    }

    let btnSaveEle = $(this).find("#saveBtn");
    setFrontBtnLoader(btnSaveEle);

    let frontAppointmentFormData = new FormData($(this)[0]);
    frontAppointmentFormData.append("payable_amount", frontPayableAmount);
    let response =
        '<div class="alert alert-warning alert-dismissable"> ' +
        Lang.get("js.processing") +
        "</div>";
    jQuery(this).find(".book-appointment-message").html(response).show("slow");
    $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: frontAppointmentFormData,
        processData: false,
        contentType: false,
        success: function (result) {
            if (result.success) {
                let appointmentID = result.data.appointmentId;

                response =
                    '<div class="gen alert alert-success">' +
                    result.message +
                    "</div>";

                $(".book-appointment-message")
                    .html(response)
                    .delay(5000)
                    .hide("slow");
                $(window).scrollTop($(".appointment-form").offset().top);
                $("#frontAppointmentBook")[0].reset();

                if (result.data.payment_type == manually) {
                    // Turbo.visit(
                    //     route("manually-payment", {
                    //         appointmentId: appointmentID,
                    //     })
                    // );
                    window.location.href = route("manually-payment", {
                        appointmentId: appointmentID,
                    });
                }

                if (result.data.payment_type == paystack) {
                    if (result.data.redirect_url) {
                        window.location.replace(result.data.redirect_url);
                    }
                }

                if (result.data.payment_type == authorizeMethod) {
                    window.location.replace(
                        route("authorize.init", {
                            appointmentId: appointmentID,
                        }),
                    );
                }

                if (result.data.payment_type == paytmMethod) {
                    window.location.replace(
                        route("paytm.init", { appointmentId: appointmentID }),
                    );
                }

                if (result.data.payment_type == paypal) {
                    if (result.data.redirect_url) {
                        window.location.replace(result.data.redirect_url);
                    }
                }

                if (result.data.payment_type == razorpayMethod) {

                    window.location.href = route("razorpay.init", {
                        appointmentId: appointmentID,
                    });

                }

                if (result.data.payment_type == stripeMethod) {
                    let sessionId = result.data[0].sessionId;
                    stripe
                        .redirectToCheckout({
                            sessionId: sessionId,
                        })
                        .then(function (result) {
                            manageAjaxErrors(result);
                        });
                }

                if (result.data === manually) {
                    setTimeout(function () {
                        location.reload();
                    }, 1200);
                }
            }
        },
        error: function (result) {
            $(".book-appointment-message").css("display", "block");
            let errorMsg = result.responseJSON && result.responseJSON.message ? result.responseJSON.message : "Something went wrong.";
            response =
                '<div class="gen alert alert-danger">' +
                errorMsg +
                "</div>";
            $(".book-appointment-message")
                .html(response)
                .delay(5000)
                .hide("slow");

            let hasSlotError = errorMsg.indexOf("select appointment time slot") !== -1;
            let hasPatientError = errorMsg.indexOf("already registered") !== -1 || 
                                  errorMsg.indexOf("registered as a patient") !== -1 ||
                                  errorMsg.indexOf("Email is not registered") !== -1 ||
                                  errorMsg.indexOf("not registered") !== -1 ||
                                  errorMsg.indexOf("(and ") !== -1;

            if (hasPatientError) {
                $('html, body').animate({
                    scrollTop: $(".appointment-form").offset().top - 150
                }, 500);
            } else if (hasSlotError) {
                $('html, body').animate({
                    scrollTop: $(".slots-box").offset().top - 150
                }, 500);
            } else {
                $('html, body').animate({
                    scrollTop: $(".appointment-form").offset().top - 150
                }, 500);
            }
        },
        complete: function () {
            setFrontBtnLoader(btnSaveEle);
        },
    });
});

listenClick(".show-more-btn", function () {
    if ($(".question").hasClass("d-none")) {
        $(".question").removeClass("d-none");
        $(".show-more-btn").html("show less");
    } else {
        $(".show-content").addClass("d-none");
        $(".show-more-btn").html("show more");
    }
});

window.setFrontBtnLoader = function (btnLoader) {
    if (btnLoader.attr("data-old-text")) {
        btnLoader.html(btnLoader.attr("data-old-text")).prop("disabled", false);
        btnLoader.removeAttr("data-old-text");
        return;
    }
    btnLoader.attr("data-old-text", btnLoader.text());
    btnLoader
        .html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>',
        )
        .prop("disabled", true);
};

function storeFailedPayment(response) {
    $.ajax({
        type: "POST",
        url: route("razorpay.failed"),
        data: {
            data: response,
        },
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
            }
        },
        error: function () {},
    });
}

$(document).on('input', 'input', function () {
    $(this).removeClass('is-invalid');
    $(this).parent().find('.error-span').remove();
});

$(document).on('change', 'select', function () {
    $(this).removeClass('is-invalid');
    $(this).parent().find('.select2-selection').removeClass('border border-danger');
    $(this).parent().find('.error-span').remove();
});
