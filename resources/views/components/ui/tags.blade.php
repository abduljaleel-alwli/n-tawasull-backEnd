@props(['tags' => [], 'size' => 'sm'])

@if(count($tags))
    <div class="flex flex-wrap gap-1.5">
        @foreach ($tags as $tag)
            <span
                class="
                    inline-flex items-center rounded-full
                    {{ $size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-sm' }}
                    bg-accent/10 text-accent
                    ring-1 ring-accent/30
                ">
                {{ $tag->name }}
            </span>
        @endforeach
    </div>
@endif
