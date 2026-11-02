<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Payment Processing</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>

<body>
    <div style="display: none;">
        <!-- Hidden content while payment popup loads -->
        <p>Processing your payment...</p>
    </div>

    <script type="text/javascript">
        var razorpayOpened = false;
        var options = {
            "key": "{{ $key }}",
            "amount": {{ $amount }},
            "currency": "{{ $currency }}",
            "name": "{{ $name }}",
            "description": "Appointment Payment",
            "order_id": "{{ $order_id }}",
            "handler": function(response) {
                // Handle successful payment
                window.location.href = "{{ route('razorpay.success') }}?razorpay_payment_id=" + response
                    .razorpay_payment_id +
                    "&razorpay_order_id=" + response.razorpay_order_id +
                    "&razorpay_signature=" + response.razorpay_signature +
                    "&appointment_id={{ $appointment_id }}";
            },
            "prefill": {
                "name": "{{ $name }}",
                "email": "{{ $email }}",
                "contact": "{{ $contact }}"
            },
            "theme": {
                "color": "#3399cc"
            },
            "modal": {
                "ondismiss": function() {
                    window.location.href = "{{ route('razorpay.failed') }}";
                }
            }
        };

        function openRazorpayCheckout() {
            if (razorpayOpened) {
                return;
            }

            if (typeof Razorpay === 'undefined') {
                setTimeout(openRazorpayCheckout, 250);
                return;
            }

            razorpayOpened = true;
            var rzp1 = new Razorpay(options);

            rzp1.on('payment.failed', function(response) {
                console.error('Payment failed:', response.error);
                window.location.href = "{{ route('razorpay.failed') }}";
            });

            rzp1.open();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', openRazorpayCheckout);
        } else {
            openRazorpayCheckout();
        }

        window.addEventListener('load', openRazorpayCheckout);
    </script>
</body>

</html>
