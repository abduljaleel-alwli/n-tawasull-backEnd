<div
    x-data="{
        show: false,
        message: '',
        type: 'info',
        timeout: null,

        notify(detail) {
            this.message = detail.message;
            this.type = detail.type ?? 'info';
            this.show = true;

            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => this.show = false, 3500);
        }
    }"
    x-on:toast.window="notify($event.detail)"
    x-show="show"
    x-transition.opacity.scale
    class="fixed bottom-6 {{ app()->getLocale() === 'ar' ? 'left-6' : 'right-6' }} z-50 w-80"
    style="display: none"
>
    <div
        class="rounded-lg shadow-lg px-4 py-3 text-white"
        :class="{
            'bg-green-600': type === 'success',
            'bg-red-600': type === 'error',
            'bg-yellow-500': type === 'warning',
            'bg-blue-600': type === 'info',
        }"
    >
        <p class="text-sm font-medium" x-text="message"></p>
    </div>
</div>
