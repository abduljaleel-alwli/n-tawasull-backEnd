<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination;

    protected $listeners = [
        'open-product-popup' => 'open',
    ];

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $category = 'all';

    // ======================
    // Data
    // ======================
    public function getProductsProperty()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->category !== 'all', fn($q) => $q->whereHas('category', fn($c) => $c->where('slug', $this->category)))
            ->orderBy('display_order')
            ->paginate(12);
    }

    public function categories()
    {
        return Category::query()->active()->orderBy('display_order')->get();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function searchNow(): void
    {
        $this->resetPage();
    }

    public function imageUrl(?string $path): string
    {
        if (!$path) {
            return 'https://placehold.co/600x400?text=No+Image';
        }

        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    }
};
?>

<div>
    <div class="grid-overlay" aria-hidden="true"></div>

    <div class="container">

        <!-- ================== HERO ================== -->
        <header class="hero">
            <h1>منتجاتنا</h1>
            <p>
                نوفر تشكيلة متكاملة من مواد الحديد والصلب للمشاريع السكنية والتجارية والصناعية.
                جودة معتمدة، مقاسات متعددة، وتوصيل سريع حسب الحاجة.
            </p>

            <form wire:submit.prevent="searchNow" class="search-wrap">
                <div class="search" role="search" aria-label="بحث في المنتجات">

                    <input type="text" placeholder="ابحث عن منتج: صاج، مواسير، زوايا..." wire:model.defer="search" />

                    <button class="search-btn" type="submit" aria-label="بحث">
                        <flux:icon name="magnifying-glass" />
                    </button>

                </div>
            </form>
        </header>

        <!-- ================== FILTER CHIPS ================== -->
        <div class="chips">

            <button class="chip {{ $category === 'all' ? 'active' : '' }}" wire:click="$set('category', 'all')">
                كل المنتجات
            </button>

            @foreach ($this->categories() as $cat)
                <button class="chip {{ $category === $cat->slug ? 'active' : '' }}"
                    wire:click="$set('category', '{{ $cat->slug }}')">
                    {{ $cat->name }}
                </button>
            @endforeach


        </div>


        <!-- ================== SECTION HEAD ================== -->
        <div class="section-head">
            <h2 class="accent-color">أفضل جودة لدينا</h2>
            <p>جميع المنتجات التي تحتاجها متوفر لدينا بأفضل الأسعار.</p>
        </div>

        <!-- ================== PRODUCTS GRID ================== -->
        <section class="grid mt-3" id="grid">
            @foreach ($this->products as $product)
                @if ($product->is_active)
                    <article class="cardx" data-cat="{{ $product->category?->slug }}"
                        data-title="{{ $product->title }}"
                        style="animation-delay:.05s;">
                        <div class="inner">
                            <div class="thumb">
                                @if ($product->is_featured)
                                    <span class="badge">الأكثر طلبًا</span>
                                @endif

                                <img alt="{{ $product->title }}" src="{{ $this->imageUrl($product->main_image) }}">
                            </div>

                            <h3 class="title">{{ $product->title }}</h3>

                            <p class="desc">
                                {{ Str::limit($product->description, 120) }}
                            </p>

                            <div class="meta">
                                <span class="pill"><i></i>سماكات متنوعة</span>
                                <span class="pill"><i></i>توريد سريع</span>
                            </div>

                            <div class="card-footer">
                                <div class="price">
                                    حسب المقاس
                                </div>

                                <div>
                                    <a class="more" wire:navigate href="{{ route('contact') }}">
                                        طلب الآن
                                    </a>
                                    <a class="more pointer"
                                        onclick="Livewire.dispatch('open-product-popup', { productId: {{ $product->id }} })">
                                        التفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </section>

        {{-- ✅ Pagination --}}
        <div class="mt-4" dir="ltr">
            {{ $this->products->links() }}
        </div>

        <!-- ================== CTA ================== -->
        <div class="cta" id="contact">
            <h3>جاهزين نجهّز طلبك!</h3>
            <p>ادخل معنا في استشارة مجانية لاختيار أفضل المنتجات والمقاسات التي تحتاجها في مشروعك.</p>
            <a href="https://wa.me/{{ settings('contact.phone') }}" target="_blank" data-analytics
                data-event="whatsapp_click" data-entity="whatsapp" data-id="whatsapp" data-source="products_page"
                rel="noopener" class="btn-aflak yellow">
                واتساب
            </a>
        </div>

    </div><!-- /container -->


    <!-- ================== TIMELINE ================== -->
    <div class="section-head mx-auto" style="margin-top: 40px;">
        <h2>كيف تطلب من نقطة تواصل؟</h2>
    </div>

    <section class="timeline mb-5">
        <div class="line"></div>

        <div class="steps">
            <div class="step">
                <div class="num">1</div>
                <h4 class="mt-3">اختر المنتج</h4>
                <p>حدد الفئة (حديد/صاج/مواسير…) والمقاس والسماكة المطلوبة.</p>
            </div>

            <div class="step">
                <div class="num">2</div>
                <h4 class="mt-3">أرسل تفاصيل الطلب</h4>
                <p>أرسل الكميات والمواصفات لنجهّز لك عرض سعر سريع.</p>
            </div>

            <div class="step">
                <div class="num">3</div>
                <h4 class="mt-3">تأكيد وتنسيق التوريد</h4>
                <p>نؤكد التوفر وننسق موعد التسليم حسب موقع مشروعك.</p>
            </div>

            <div class="step">
                <div class="num">4</div>
                <h4 class="mt-3">استلام وتنفيذ</h4>
                <p>استلم المواد وابدأ التنفيذ بثقة وجودة مضمونة.</p>
            </div>
        </div>

        <div class="cta-row">
            <p class="mini">جاهز؟ أرسل تفاصيلك واحصل على عرض سعر خلال دقائق.</p>
            <a class="products-btn" wire:navigate href="{{ route('contact') }}">
                <span>اطلب عرض سعر الآن</span>
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M8 5l8 7-8 7" />
                </svg>
            </a>
        </div>
    </section>

    <livewire:app.products.product-popup />
</div>
