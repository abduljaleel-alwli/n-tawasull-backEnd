@if (!empty($settings['branding.logo']))
    <img
        src="{{ asset('storage/' . $settings['branding.logo']) }}"
        alt="{{ __('Site logo') }}"
        {{ $attributes->merge(['class' => 'h-8']) }}
    >
@else
    <span class="font-semibold">
        {{ $settings['site_name'] ?? __('Website') }}
    </span>
@endif
