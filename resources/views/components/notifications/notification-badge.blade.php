@props([
    'count' => 0,
])

@if($count > 0)
    <span
        class="
            absolute -top-1 -end-1
            min-w-[18px] h-[18px]
            px-1
            flex items-center justify-center
            rounded-full
            bg-red-500 text-white
            text-[10px] font-bold leading-none
            ring-2 ring-white dark:ring-zinc-900
        "
    >
        {{ $count > 99 ? '99+' : $count }}
    </span>
@endif
