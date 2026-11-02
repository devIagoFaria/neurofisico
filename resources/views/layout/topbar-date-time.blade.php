<div x-data="headerclock()" x-init="startHeaderClock()" class="flex flex-col items-end justify-center border-e border-gray-200 dark:border-gray-700 h-10">
    <div class="text-md font-bold text-gray-800 dark:text-white leading-none">
        <span x-text="time"></span>
    </div>
    <div class="text-sm text-gray-800 dark:text-white mt-1 leading-none">
        <span x-text="date"></span>
    </div>
</div>

<script>
    function headerclock() {
        return {
            time: '',
            date: '',

            timezone: @js(getSettingValue('timezone') ?: 'America/Sao_Paulo'),

            updateClock() {
                const now = new Date();

                this.time = new Intl.DateTimeFormat('pt-BR', {
                    timeZone: this.timezone,
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }).format(now);

                this.date = new Intl.DateTimeFormat('pt-BR', {
                    timeZone: this.timezone,
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: '2-digit'
                }).format(now);
            },

            startHeaderClock() {
                this.updateClock();
                setInterval(() => {
                    this.updateClock();
                }, 1000);
            }
        }
    }
</script>
