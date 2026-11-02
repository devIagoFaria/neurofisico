@if (isCaptchaEnabled())
    <div
        x-data
        x-init="
            window.onCaptchaSuccess = function(token) {
                $wire.set('data.captcha_token', token);
            };

            window.onCaptchaExpired = function() {
                $wire.set('data.captcha_token', null);
            };
        "
    >
        <div wire:ignore id="g-recaptcha-box"></div>

        @error('captcha_token')
            <span class="text-danger text-sm">{{ $message }}</span>
        @enderror
    </div>

    <script src="https://www.google.com/recaptcha/api.js?onload=loadCaptcha&render=explicit" async defer></script>

    <script>
        function loadCaptcha() {
            let box = document.getElementById('g-recaptcha-box');

            if (!box) return;
            if (box.innerHTML !== '') return;

            grecaptcha.render('g-recaptcha-box', {
                sitekey: "{{ getSettingValue('googleCaptchaKey') }}",
                callback: onCaptchaSuccess,
                'expired-callback': onCaptchaExpired,
            });
        }

        document.addEventListener('livewire:navigated', loadCaptcha);
    </script>
@endif