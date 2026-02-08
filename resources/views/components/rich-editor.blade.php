@props([
    'height' => 380,
    'value' => '',
    'editorKey' => null,
])

@php
    use Illuminate\Support\Str;

    $attrs = $attributes->getAttributes();

    $wireModelKey = collect(array_keys($attrs))
        ->first(fn ($k) => str_starts_with($k, 'wire:model'));

    if (!$wireModelKey) {
        throw new Exception('x-rich-editor requires wire:model (e.g. wire:model.defer="content")');
    }

    $wireModelValue = $attrs[$wireModelKey];

    $stableKey = $editorKey ?: ($attributes->get('editor-key') ?: ('uuid-' . Str::uuid()));
    $editorId = 'tinymce-' . preg_replace('/[^a-zA-Z0-9\-_]/', '-', $stableKey);

    $wrapperAttrs = $attributes
        ->except([$wireModelKey, 'id', 'editor-key'])
        ->merge([
            'class' => 'w-full space-y-2'
        ]);
@endphp

<div
    {{ $wrapperAttrs }}
    x-data="{
        editor: null,
        id: '{{ $editorId }}',
        pendingHtml: null,
        ready: false,

        init() {
            this.pendingHtml = this.$refs.hidden.value || '';
            this.mountWhenReady();
        },

        mountWhenReady() {
            const wait = () => {
                if (!window.tinymce) return setTimeout(wait, 50);

                const old = window.tinymce.get(this.id);
                if (old) old.remove();

                this.mount();
            };
            wait();
        },

        mount() {
            const isDark = document.documentElement.classList.contains('dark');

            window.tinymce.init({
                selector: '#'+this.id,
                menubar: false,
                branding: false,

                height: {{ (int) $height }},
                resize: false,
                directionality: 'rtl',

                plugins: 'lists link code table autoresize directionality',
                toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | removeformat | code',
                toolbar_mode: 'sliding',

                autoresize_bottom_margin: 16,
                autoresize_min_height: {{ max(220, (int) $height) }},
                autoresize_max_height: 900,

                content_style: `
                    :root{ color-scheme: ${isDark ? 'dark' : 'light'}; }
                    html,body{ direction: rtl; }
                    body{
                        font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, 'Apple Color Emoji','Segoe UI Emoji';
                        font-size: 15px;
                        line-height: 1.8;
                        padding: 14px 16px;
                        ${isDark ? 'color:#e5e7eb;background:#f0f0f0;' : 'color:#0f172a;background:#ffffff;'}
                    }
                    a{ color: ${isDark ? '#93c5fd' : '#2563eb'}; }
                    table{ border-collapse: collapse; width:100%; }
                    table td, table th{ border:1px solid ${isDark ? '#334155' : '#e2e8f0'}; padding:8px; }
                    blockquote{
                        border-right: 4px solid ${isDark ? '#334155' : '#cbd5e1'};
                        margin: 8px 0;
                        padding: 6px 12px;
                        ${isDark ? 'color:#cbd5e1;background:#0f172a;' : 'color:#334155;background:#f8fafc;'}
                    }
                `,

                setup: (ed) => {
                    this.editor = ed;

                    ed.on('init', () => {
                        this.ready = true;
                        this.applyPending();
                    });

                    ed.on('change keyup setcontent', () => {
                        const html = ed.getContent({ format: 'html' });
                        this.$refs.hidden.value = html;
                        this.$refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                },
            });
        },

        applyPending() {
            if (!this.editor || !this.ready) return;

            const html = this.pendingHtml ?? '';
            try {
                this.editor.setContent(html, { format: 'html' });
                this.editor.undoManager?.clear?.();
            } catch (e) {
                setTimeout(() => {
                    if (this.editor) {
                        this.editor.setContent(html, { format: 'html' });
                        this.editor.undoManager?.clear?.();
                    }
                }, 50);
            }
        },

        syncFromHidden() {
            this.pendingHtml = this.$refs.hidden.value || '';
            this.applyPending();
        },

        destroy() {
            if (!window.tinymce) return;
            const inst = window.tinymce.get(this.id);
            if (inst) inst.remove();
            this.editor = null;
            this.ready = false;
        }
    }"
    x-init="$nextTick(() => syncFromHidden())"
>
    {{-- Hidden Livewire field --}}
    <textarea
        x-ref="hidden"
        class="hidden"
        {{ $wireModelKey }}="{{ $wireModelValue }}"
    >{!! $value !!}</textarea>

    {{-- TinyMCE UI --}}
    <div wire:ignore class="w-full">
        <textarea id="{{ $editorId }}"></textarea>
    </div>

    {{-- Styling TinyMCE chrome (outside iframe) --}}
    <style>
        /* scoped-ish: tied to this editor id */
        #{{ $editorId }} ~ .tox.tox-tinymce,
        .tox.tox-tinymce[aria-labelledby="{{ $editorId }}_ifr"] {
            border-radius: 16px !important;
        }

        .tox.tox-tinymce {
            border: 1px solid rgba(148,163,184,.45) !important;
            overflow: hidden !important;
        }

        .dark .tox.tox-tinymce{
            border-color: rgba(51,65,85,.8) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,.25);
        }

        .tox .tox-toolbar,
        .tox .tox-toolbar__primary{
            background: transparent !important;
        }

        .tox .tox-toolbar__group{
            padding: 6px 8px !important;
        }

        .tox .tox-statusbar{
            border-top: 1px solid rgba(148,163,184,.35) !important;
        }

        .dark .tox .tox-statusbar{
            border-top-color: rgba(51,65,85,.7) !important;
        }

        /* make buttons feel modern */
        .tox .tox-tbtn{
            border-radius: 10px !important;
        }

        /* respect small screens */
        @media (max-width: 640px){
            .tox .tox-toolbar__primary{
                flex-wrap: wrap !important;
            }
        }
    </style>
</div>
