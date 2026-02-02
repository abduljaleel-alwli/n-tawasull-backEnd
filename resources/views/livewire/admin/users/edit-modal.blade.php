@if ($showEditModal)
    <div class="fixed inset-0 z-50">

        {{-- Overlay --}}
        <div wire:click="resetEditForm" wire:loading.remove wire:target="updateUser"
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        {{-- Center wrapper --}}
        <div class="relative h-full w-full flex items-start justify-center px-4 py-6 sm:py-10">

            {{-- Modal container --}}
            <div
                class="w-full max-w-lg rounded-2xl
                   bg-white dark:bg-slate-900
                   border border-slate-200 dark:border-slate-800
                   shadow-2xl
                   max-h-[90vh]
                   flex flex-col overflow-hidden">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-lg font-semibold tracking-tight">
                        {{ __('Edit user') }}
                    </h3>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5 overflow-y-auto">

                    {{-- Validation summary --}}
                    @if ($errors->any())
                        <div
                            class="rounded-xl border border-red-200
                                bg-red-50 dark:bg-red-950/30
                                p-4 text-sm text-red-700 dark:text-red-400">
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
                            {{ __('Name') }}
                        </label>
                        <input wire:model.defer="editName" type="text"
                            class="input w-full @error('editName') ring-1 ring-red-500 @enderror" />
                        @error('editName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-500">
                            {{ __('Email') }}
                        </label>
                        <input wire:model.defer="editEmail" type="email"
                            class="input w-full @error('editEmail') ring-1 ring-red-500 @enderror" />
                        @error('editEmail')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-500">
                            {{ __('Role') }}
                        </label>
                        <select wire:model.defer="editRole"
                            class="select w-full @error('editRole') ring-1 ring-red-500 @enderror">
                            <option value="admin">{{ __('Admin') }}</option>
                            <option value="super-admin">{{ __('Super admin') }}</option>
                        </select>
                        @error('editRole')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Note --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-500">
                            {{ __('Note (private)') }}
                        </label>
                        <textarea wire:model.defer="editNote" rows="3"
                            class="textarea w-full @error('editNote') ring-1 ring-red-500 @enderror"></textarea>
                        @error('editNote')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Footer --}}
                <div
                    class="px-6 py-4 border-t border-slate-200 dark:border-slate-800
                        flex justify-end gap-2">

                    <button wire:click="resetEditForm"
                        class="px-4 py-2 rounded-lg text-sm
                           bg-slate-200 dark:bg-slate-800
                           hover:opacity-80 transition">
                        {{ __('Cancel') }}
                    </button>

                    <button wire:click="updateUser" wire:loading.attr="disabled" wire:target="updateUser"
                        class="relative inline-flex items-center justify-center
           px-4 py-2 rounded-lg text-sm
           bg-accent text-white
           hover:opacity-90 transition
           disabled:opacity-60 disabled:cursor-not-allowed">
                        {{-- Normal state --}}
                        <span wire:loading.remove wire:target="updateUser">
                            {{ __('Save changes') }}
                        </span>

                        {{-- Loading state --}}
                        <span wire:loading wire:target="updateUser" class="flex items-center gap-2">
                            {{ __('Saving...') }}
                        </span>
                    </button>

                </div>

            </div>
        </div>
    </div>
@endif
