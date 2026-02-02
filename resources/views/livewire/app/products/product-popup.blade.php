<?php

use Livewire\Volt\Component;
use App\Models\Product;

new class extends Component {
    public ?Product $product = null;
    public bool $open = false;

    protected $listeners = [
        'open-product-popup' => 'open',
    ];

    public function open(int $productId): void
    {
        $this->product = Product::with('category')->findOrFail($productId);
        $this->open = true;
    }

    public function close(): void
    {
        $this->reset(['open', 'product']);
    }
};
?>

<div>
    @if ($product)
        <style>
            main {
                z-index: 99999 !important;
            }
        </style>
    @endif

    @if ($product)
        <div class="lw-modal-backdrop" wire:click.self="close">

            <div class="lw-modal">
                <div class="lw-modal-content">

                    {{-- Header --}}
                    <div class="lw-modal-header">
                        <h5 class="lw-modal-title">
                            {{ $product->title }}
                            <span class="badge bg-warning text-dark ms-2">
                                {{ $product->category?->name }}
                            </span>
                        </h5>

                        <button class="lw-close" wire:click="close">&times;</button>
                    </div>

                    {{-- Body --}}
                    <div class="lw-modal-body">
                        <div class="row g-4">

                            {{-- Images --}}
                            <div class="col-md-6">
                                @php
                                    $images = collect([$product->main_image])
                                        ->merge($product->images ?? [])
                                        ->filter();
                                @endphp

                                @php
                                    $images = collect([$product->main_image])
                                        ->merge($product->images ?? [])
                                        ->filter()
                                        ->values();
                                @endphp

                                @if ($images->count())
                                    <div class="lw-slider">

                                        {{-- Radio controls --}}
                                        @foreach ($images as $i => $img)
                                            <input type="radio" name="slider-{{ $product->id }}"
                                                id="slide-{{ $product->id }}-{{ $i }}"
                                                 data-index="{{ $i }}"
                                                {{ $i === 0 ? 'checked' : '' }}>
                                        @endforeach

                                        {{-- Slides --}}
                                        <div class="lw-slides" dir="ltr">
                                            @foreach ($images as $i => $img)
                                                <div class="lw-slide">
                                                    <img src="{{ asset('storage/' . $img) }}" alt="">
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Dots --}}
                                        <div class="lw-dots">
                                            @foreach ($images as $i => $img)
                                                <label for="slide-{{ $product->id }}-{{ $i }}"></label>
                                            @endforeach
                                        </div>

                                    </div>
                                @endif

                            </div>

                            {{-- Content --}}
                            <div class="col-md-6">
                                <h4 class="fw-bold mb-3">
                                    {{ $product->meta_title ?? $product->title }}
                                </h4>

                                @if ($product->description)
                                    <div class="meta-box">
                                        <div class="accent-color fw-semibold mb-3">
                                            {{ __('Description') }}
                                        </div>
                                        <p class="mb-0">
                                            {{ $product->description }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="lw-modal-footer">
                        <button class="btn btn-outline-warning" wire:click="close">
                            {{ __('Close') }}
                        </button>

                        @role('admin|super-admin')
                            <small class="text-muted bg-gradient px-2 py-1 rounded">
                                {{ __('Added by') }}:
                                {{ $product->creator?->name ?? '-' }}
                            </small>
                        @else
                            <small class="text-muted bg-gradient px-2 py-1 rounded">
                                {{ $settings['site_name'] }}
                            </small>
                        @endrole
                    </div>

                </div>
            </div>

        </div>
    @endif
</div>
