    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50">

            {{-- Overlay --}}
            <div wire:click="closeModal" wire:loading.remove wire:target="save"
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>


            {{-- Center Wrapper --}}
            <div
                class="relative h-full w-full flex items-start justify-center
                        px-4 py-6 sm:py-10">

                {{-- Modal Container --}}
                <div
                    class="w-full max-w-lg
                           rounded-2xl
                           bg-white dark:bg-slate-900
                           border border-slate-200 dark:border-slate-800
                           shadow-2xl
                           max-h-[90vh]
                           flex flex-col overflow-hidden">

                    {{-- Header --}}
                    <div
                        class="px-6 py-4
                               bg-white dark:bg-slate-900
                               border-b border-slate-200 dark:border-slate-800
                               flex items-center justify-between">

                        <h3 class="text-lg font-semibold tracking-tight">
                            {{ $editing ? __('Edit category') : __('Create category') }}
                        </h3>

                        <button wire:click="closeModal" wire:loading.attr="disabled" wire:target="save"
                            class="text-slate-400 hover:text-slate-600
           dark:hover:text-slate-300 transition
           disabled:opacity-40 disabled:cursor-not-allowed"
                            aria-label="{{ __('Close') }}">
                            ✕
                        </button>

                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-5 overflow-y-auto">

                        {{-- Validation summary --}}
                        @if ($errors->any())
                            <div
                                class="rounded-xl
                                       border border-red-200
                                       bg-red-50 dark:bg-red-950/30
                                       p-4 text-sm
                                       text-red-700 dark:text-red-400">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Name --}}
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-500">
                                {{ __('Category name') }}
                            </label>

                            <input type="text" wire:model.defer="name"
                                class="w-full rounded-lg input
                                       @error('name') ring-1 ring-red-500 @enderror" />

                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-6 py-4
                               bg-white dark:bg-slate-900
                               border-t border-slate-200 dark:border-slate-800
                               flex justify-end gap-2">

                        <button wire:click="closeModal" wire:loading.attr="disabled" wire:target="save"
                            class="px-4 py-2 rounded-lg text-sm
           bg-slate-200 dark:bg-slate-800
           hover:opacity-80 transition
           disabled:opacity-60 disabled:cursor-not-allowed">
                            {{ __('Cancel') }}
                        </button>


                        <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="relative inline-flex items-center justify-center
           px-4 py-2 rounded-lg text-sm
           bg-accent text-white
           hover:opacity-90 transition
           disabled:opacity-60 disabled:cursor-not-allowed">
                            {{-- Normal --}}
                            <span wire:loading.remove wire:target="save">
                                {{ __('Save') }}
                            </span>

                            {{-- Loading --}}
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                {{ __('Saving...') }}
                            </span>
                        </button>

                    </div>

                </div>
            </div>
        </div>
    @endif
