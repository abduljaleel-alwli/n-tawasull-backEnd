<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="space-y-6">
    <h1 class="text-3xl font-bold">
        title
        {{ settings('about.title') }}
    </h1>

    @if (settings('about.subtitle'))
        <p class="text-xl text-gray-500">
            subtitle
            {{ settings('about.subtitle') }}
        </p>
    @endif

    <p class="text-gray-700">
        description
        {{ settings('about.description') }}
    </p>

    <div class="grid md:grid-cols-3 gap-6 pt-6">
        features
        <div class="grid md:grid-cols-3 gap-6 pt-6">
            @foreach ((array) settings('about.features', []) as $feature)
                <div class="card space-y-3">

                    {{-- Icon --}}
                    @if (!empty($feature['icon_value']))
                        <div class="text-3xl text-primary">
                            @if ($feature['icon_type'] === 'fa')
                                <i class="{{ $feature['icon_value'] }}"></i>
                            @elseif ($feature['icon_type'] === 'svg')
                                {!! $feature['icon_value'] !!}
                            @endif
                        </div>
                    @endif

                    <h3 class="font-semibold">
                        {{ $feature['title'] }}
                    </h3>

                    <p class="text-sm text-gray-600">
                        {{ $feature['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>