<?php

use Livewire\Volt\Component;
use App\Models\Category;

new class extends Component {
    public function with(): array
    {
        return [
            'categories' => Category::query()
                ->orderBy('id') // أو orderBy('created_at')
                ->limit(4)
                ->get(),
        ];
    }
};
?>

<main>
    <!-- Preloader Start -->
    <div class="preloader">
        <div class="loading-container">
            <div class="loading"></div>
            <div id="loading-icon" class="rounded-circle">
                <x-app-logo-icon width="100px" />
            </div>

        </div>
    </div>
    <!-- Preloader End -->
    <!-- Hero Section Start -->
    <div id="top" class="hero dark-section parallaxie"
        style="background-image: url({{ asset('/assets/images/home-2/aflak_042.png') }}); background-size: cover; background-repeat: no-repeat; background-attachment: fixed; background-position: center;">
        <div class="container">
            <div class="row align-items-center flex-row-reverse">
                <div class="col-xl-4 col-md-6">
                    {{-- Iron animation --}}
                    <div class="Iron-hero-box">
                        <!-- ===================== LEFT (Animation Visual) ===================== -->
                        <div class="left-visual Iron-hero-animation">
                            <!-- صفيحة (Layer) -->
                            <div class="sheet" aria-hidden="true"></div>

                            <!-- مواسير (Images بدل Shapes) -->
                            <div class="pipes" aria-hidden="true">
                                <img class="pipe-img p1" src="{{ asset('assets/images/home/mausiar.png') }}"
                                    alt="" />
                                <img class="pipe-img p2" src="{{ asset('assets/images/home/mausiar.png') }}"
                                    alt="" />
                            </div>

                            <!-- التكديس + صورة المنتج -->
                            <div class="stack">
                                <!-- ضع صورتك هنا: steel.png -->
                                <img class="product-img" src="{{ asset('assets/images/home/6.png') }}"
                                    alt="منتجات الحديد - مواسير وصفائح" />
                            </div>

                            <!-- ضوء معدني يمر باستمرار -->
                            {{-- <div class="shine" aria-hidden="true"></div> --}}

                            <!-- شرارات بسيطة -->
                            <div class="sparks" aria-hidden="true">
                                <span class="spark s1"></span>
                                <span class="spark s2"></span>
                                <span class="spark s3"></span>
                                <span class="spark s4"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-md-6">
                    <div class="hero-content ms-5">
                        <div class="section-title">
                            <h1 class="text-light accent-color" data-cursor="-opaque">
                                نقطة تواصل <br>
                            </h1>
                            <h3 class="mt-3"> مواد البناء التي يعتمد عليها مشروعك </h3>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">
                                نوفر الحديد والصفائح والمواسير وملحقات البناء <br> بمواصفات دقيقة وأسعار منافسة، مع دعم
                                فني <br>وخدمات قص وتشكيل وتوصيل لتصل المواد جاهزة للعمل.
                            </p>
                        </div>
                        <div class="hero-content-body wow fadeInUp" data-wow-delay="0.4s">
                            <div class="hero-btn">
                                <a wire:navigate href="{{ route('contact') }}" class="btn-default btn-highlighted">اطلب عرض سعر
                                    الآن</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Section End -->
    <!-- Hero Info Box Start -->
    <div class="hero-info-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="hero-info-list">
                        <div class="hero-info-item box-1">
                            <div class="hero-info-content-box">
                                <div class="hero-info-item-content">
                                    <ul>
                                        <li>توريد مواد البناء</li>
                                    </ul>
                                    <h3>نُسرّع إنجاز مشروعك بتوريد <br> ما تحتاجه</h3>
                                </div>
                                <div class="hero-info-btn">
                                    <a wire:navigate href="{{ route('products') }}" class="readmore-btn">اعرف المزيد</a>
                                </div>
                            </div>
                            <div class="hero-info-image">
                                <figure class="image-anime reveal">
                                    <img src="{{ asset('assets/images/home-2/aflak_042.png') }}" alt="">
                                </figure>
                            </div>
                        </div>
                        <div class="hero-info-item box-2">
                            <figure class="image-anime reveal">
                                <img src="{{ asset('assets/images/home-2/aflak_034.png') }}" alt="">
                            </figure>
                        </div>
                        <div class="hero-info-item box-3"> 
                            <div class="hero-info-header">
                                <div class="icon-box">
                                    <img src="{{ asset('assets/images/home/icon-hero-info-1.svg') }}" alt="">
                                </div>
                                <div class="satisfy-client-images">
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime"><img src="{{ asset('assets/images/home-2/aflak_007.png') }}"
                                                alt=""></figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime"><img src="{{ asset('assets/images/home-2/aflak_010.png') }}"
                                                alt=""></figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime"><img src="{{ asset('assets/images/home-2/aflak_009.png') }}"
                                                alt=""></figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime"><img src="{{ asset('assets/images/home-2/aflak_029.png') }}"
                                                alt=""></figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime"><img src="{{ asset('assets/images/home-2/aflak_031.png') }}"
                                                alt=""></figure>
                                    </div>
                                </div>
                            </div>
                            <div class="hero-info-counter-box">
                                <h3>طلبات تم تجهيزها بنجاح</h3>
                                <h2><span class="counter">15</span>K+</h2>
                            </div>
                            <div class="hero-info-bg-icon">
                                <img src="{{ asset('assets/images/home/icon-hero-info-bg-1.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Info Box End -->
    <!-- About Us Section Start -->
    <div id="about" class="about-us">
        <div class="container">
            <div class="row flex-row-reverse">
                <div class="col-xl-5">
                    <div class="about-us-image-box wow fadeInUp">
                        <div class="about-us-image-box-1">
                            <div class="about-us-image">
                                <figure class="image-anime">
                                    <img src="{{ asset('assets/images/home-2/aflak_033.png') }}" alt="">
                                </figure>
                            </div>
                        </div>
                        <div class="about-us-image-box-2">
                            <div class="about-us-image">
                                <figure class="image-anime">
                                    <img src="{{ asset('assets/images/home-2/aflak_014.png') }}" alt="">
                                </figure>
                            </div>
                            <div class="year-experience-circle accent-bg-color">
                                <x-app-logo-icon class="accent-bg-color" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7">
                    <div class="about-us-content">
                        <div class="section-title">
                            <h2 class="mt-3" data-cursor="-opaque">نقطة تواصل … شريك التوريد الذي يضمن لك نجاح
                                عملك </h2>
                            <p class="text-dark" data-wow-delay="0.2s">
                                نحن شركة متخصصة في توريد وتجارة مواد البناء والمعادن، نركّز على توفير مواد موثوقة
                                بمواصفات دقيقة وخدمة سريعة تساعد المقاولين والورش والمشاريع على الإنجاز دون تأخير.
                            </p>
                        </div>
                        <div class="about-us-body wow fadeInUp  " data-wow-delay="0.4s">
                            <div class="about-body-item ">
                                <div class="icon-box">
                                    <img src="{{ asset('assets/images/home/icon-about-item-1.svg') }}" alt="">
                                </div>
                                <div class="about-body-item-content">
                                    <h3>جودة ومواصفة</h3>
                                    <p>نختار المواد بعناية ونلتزم بمقاسات وسماكات واضحة لضمان سلامة التنفيذ.</p>
                                </div>
                            </div>
                            <div class="about-body-item">
                                <div class="icon-box">
                                    <img src="{{ asset('assets/images/home/icon-about-item-2.svg') }}" alt="">
                                </div>
                                <div class="about-body-item-content">
                                    <h3>سرعة التجهيز والتسليم</h3>
                                    <p>تجهيز طلباتك بسرعة مع إمكانية التوصيل، لتبدأ العمل فوراً.</p>
                                </div>
                            </div>
                        </div>
                        <div class="about-us-footer wow fadeInUp" data-wow-delay="0.6s">
                            <div class="about-us-footer-content">
                                <div class="about-footer-content-list">
                                    <ul class="fs-6">
                                        <li>تشكيلة واسعة: حديد، صفائح، مواسير، شبك وملحقات.</li>
                                        <li>خدمة قص وتشكيل وتجهيز حسب الطلب.</li>
                                        <li>أسعار واضحة وتوصيل سريع ودعم فني.</li>
                                    </ul>
                                </div>
                                <div class="about-us-btn ms-2">
                                    <a wire:navigate href="{{ route('contact') }}" class="btn-default">تواصل معنا</a>
                                </div>
                            </div>
                            <div class="about-us-video-box">
                                <div class="about-video-image">
                                    <figure class="image-anime">
                                        <img src="{{ asset('assets/images/home-2/aflak_013.png') }}" alt="">
                                    </figure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About Us Section End -->
    <!-- Our Features Section Start -->
    <div class="our-features">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <div class="section-title section-title-center">
                        <h1 class=" fs-1 mt-5"> ما يميزنا </h1>
                        <h2 class="accent-color mt-3" data-cursor="-opaque">
                            نوفّر مواد بناء بمواصفات دقيقة، وخدمة سريعة، وتسعير واضح… لتنجز مشروعك بنجاح
                        </h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 col-md-6 order-1">
                    <div class="feature-item box-1 wow fadeInUp">
                        <div class="feature-item-shape-image">
                            <figure><img src="{{ asset('assets/images/home-2/aflak_024.png') }}" alt=""></figure>
                        </div>
                        <div class="feature-item-content-box">
                            <div class="feature-item-content">
                                <h3 class="accent-color">جودة ومقاسات واضحة</h3>
                                <p>نلتزم بالمواصفة والسماكة والمقاس لتكون النتيجة مطابقة لمتطلبات التنفيذ.</p>
                            </div>
                            <div class="service-benefit-list">
                                <ul>
                                    <li style="   background: var(--background-color); color: var(--white-color);">
                                        توريد
                                        مواد معروفة المصدر قدر الإمكان.</li>
                                    <li style="   background: var(--background-color); color: var(--white-color);">
                                        تقليل
                                        الهدر عبر تجهيز المقاسات.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 order-xl-2 order-md-3 order-2">
                    <div class="feature-item box-2 wow fadeInUp" data-wow-delay="0.2s">
                        <div class="feature-item-info">
                            <div class="feature-item-info-content">
                                <p class="accent-color">سريع وسهل</p>
                                <h3>اطلب عرض سعر حسب المقاسات والكمية</h3>
                            </div>
                            <div class="feature-item-btn">
                                <a wire:navigate href="{{ route('contact') }}" class="readmore-btn">احصل على عرض سعر</a>
                            </div>
                        </div>
                        <div class="feature-item-image">
                            <figure><img src="{{ asset('assets/images/home/feature-item-image-2.png') }}" alt=""></figure>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 order-xl-3 order-md-2 order-3">
                    <div class="feature-item box-3 wow fadeInUp" data-wow-delay="0.4s">
                        <div class="feature-item-content-box">
                            <div class="feature-item-content">
                                <h2><span class="counter">250</span>+</h2>
                                <h3>طلب شهريًا</h3>
                            </div>
                            <div class="feature-item-counter-info">
                                <p>نخدم ورش ومقاولين ومشاريع بتجهيز يومي ومتابعة حتى التسليم.</p>
                            </div>
                        </div>
                        <div class="feature-item-tag-list">
                            <ul>
                                <li class="text-white" style=" background-color: var(--background-color)">قص وتجهيز
                                </li>
                                <li class="text-white" style=" background-color: var(--background-color)">توصيل للموقع
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Our Features Section End -->
    <!-- Blog Section Start -->
    <div class="our-blog">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <div class="section-title section-title-center">
                        <h2 class="" data-cursor="-opaque">
                            نقدم لك نصائح سريعة تساعدك في انجاز مشروعك
                        </h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 col-md-6">
                    <div class="post-item wow fadeInUp">
                        <div class="post-featured-image">
                            <a wire:navigate href="{{ route('products') }}" data-cursor-text="عرض">
                                <figure><img src="{{ asset('assets/images/home-2/aflak_043.png') }}" alt=""></figure>
                            </a>
                        </div>
                        <div class="post-item-tags"><a wire:navigate href="{{ route('contact') }}">حديد</a></div>
                        <div class="post-item-body">
                            <div class="post-content-box">
                                <div class="post-item-content">
                                    <h2><a wire:navigate href="{{ route('products') }}">اختيار حديد التسليح المناسب حسب
                                            <br>طبيعة مشروعك</a>
                                    </h2>
                                </div>
                            </div>
                            <div class="post-item-btn"><a wire:navigate href="{{ route('contact') }}" class="readmore-btn">
                                    التفاصيل </a></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="post-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="post-featured-image">
                            <a wire:navigate href="{{ route('products') }}" data-cursor-text="عرض">
                                <figure><img src="{{ asset('assets/images/home-2/aflak_005.png') }}" alt=""></figure>
                            </a>
                        </div>
                        <div class="post-item-tags"><a wire:navigate href="{{ route('contact') }}">مواسير</a></div>
                        <div class="post-item-body">
                            <div class="post-content-box">
                                <div class="post-item-content">
                                    <h2><a wire:navigate href="{{ route('products') }}"> اختيار ما يناسب مشروعك من
                                            المواسير <br> والسماكات
                                            المناسبة</a></h2>
                                </div>
                            </div>
                            <div class="post-item-btn"><a wire:navigate href="{{ route('contact') }}" class="readmore-btn">
                                    التفاصيل</a></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="post-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="post-featured-image">
                            <a wire:navigate href="{{ route('products') }}" data-cursor-text="عرض">
                                <figure><img src="{{ asset('assets/images/home-2/aflak_024.png') }}" alt=""></figure>
                            </a>
                        </div>
                        <div class="post-item-tags"><a wire:navigate href="{{ route('contact') }}">صفائح</a></div>
                        <div class="post-item-body">
                            <div class="post-content-box">
                                <div class="post-item-content">
                                    <h2><a wire:navigate href="{{ route('contact') }}"> أفضل طريقة لتقليل الهدر عند طلب الصفائح
                                            والقص</a></h2>
                                </div>
                            </div>
                            <div class="post-item-btn"><a wire:navigate href="{{ route('contact') }}" class="readmore-btn">
                                    التفاصيل </a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog Section End -->
    <!-- Our منتجاتنا Section Start -->
    <div id="products" class="our-products mt-0">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <div class="section-title section-title-center mb-0">
                        <h1 class="fs-1">منتجاتنا </h1>
                        <h2 class="accent-color mt-3 " data-cursor="-opaque">
                            خدمات توريد وتجهيز بأعلى جودة وأفضل سعر
                        </h2>
                    </div>
                </div>
            </div>

            {{-- Products Slider --}}
            <div class="slider-wrap">
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/josor.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/mabrom.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/mauser.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/panal.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/plinet.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/sajblack.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img
                                src="{{ asset('assets/images/home/montajat/sajmojalfan.png') }}" alt="">
                        </div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/sajmosut.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/shenko.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/tupat.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/zauaua.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/asel.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/grmed.png') }}"
                                alt=""></div>
                        <div class="swiper-slide"><img src="{{ asset('assets/images/home/montajat/selecasel.png') }}"
                                alt=""></div>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="service-benefit-box wow fadeInUp mb-5" data-wow-delay="0.4s">
                    <div class="service-benefit-list">
                        <ul>
                            @foreach ($categories as $category)
                                <li style="background: var(--background-color); color: var(--white-color);">
                                    {{ $category->name }}
                                </li>
                            @endforeach

                            {{-- زر المزيد --}}
                            <a wire:navigate href="{{ route('products') }}">
                                <li style="background: var(--secondary-color); color: var(--accent-color); border: 2px solid var(--accent-color);">
                                    المزيد
                                </li>
                            </a>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row products-item-list">
                <div class="col-xl-3 col-md-6">
                    <div class="service-item wow fadeInUp active">
                        <div class="service-item-header">
                            <div class="service-item-title">
                                <h2><a wire:navigate href="{{ route('contact') }}">حديد تسليح</a></h2>
                            </div>
                            <div class="service-item-content">
                                <p>حديد تسليح بمقاسات وأقطار متعددة مطابق للمواصفات الهندسية للمشاريع السكنية والتجارية.
                                </p>
                            </div>
                        </div>
                        <div class="service-image-box">
                            <div class="service-item-image">
                                <figure class="image-anime">
                                    <img src="{{ asset('assets/images/home-2/aflak_044.png') }}" alt="">
                                </figure>
                            </div>
                            <div class="service-item-btn">
                                <a wire:navigate href="{{ route('contact') }}"><img src="{{ asset('assets/images/home/arrow-primary.svg') }}"
                                        alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="service-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="service-item-header">
                            <div class="service-item-title">
                                <h2><a wire:navigate href="{{ route('contact') }}">مواسير حديد</a></h2>
                            </div>
                            <div class="service-item-content">
                                <p>مواسير حديد بسماكات وأطوال مختلفة للاستخدامات الإنشائية والصناعية.</p>
                            </div>
                        </div>
                        <div class="service-image-box">
                            <div class="service-item-image">
                                <figure class="image-anime">
                                    <img src="{{ asset('assets/images/home-2/aflak_007.png') }}" alt="">
                                </figure>
                            </div>
                            <div class="service-item-btn">
                                <a wire:navigate href="{{ route('contact') }}"><img src="{{ asset('assets/images/home/arrow-primary.svg') }}"
                                        alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="service-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="service-item-header">
                            <div class="service-item-title">
                                <h2><a wire:navigate href="{{ route('contact') }}">صفائح معدنية</a></h2>
                            </div>
                            <div class="service-item-content">
                                <p>صفائح حديد وفولاذ بسماكات متعددة مناسبة لأعمال القص والتشكيل والتصنيع.</p>
                            </div>
                        </div>
                        <div class="service-image-box">
                            <div class="service-item-image">
                                <figure class="image-anime">
                                    <img src="{{ asset('assets/images/home-2/aflak_045.png') }}" alt="">
                                </figure>
                            </div>
                            <div class="service-item-btn">
                                <a wire:navigate href="{{ route('contact') }}"><img src="{{ asset('assets/images/home/arrow-primary.svg') }}"
                                        alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="service-item wow fadeInUp" data-wow-delay="0.6s">
                        <div class="service-item-header">
                            <div class="service-item-title">
                                <h2><a wire:navigate href="{{ route('contact') }}">زوايا حديد</a></h2>
                            </div>
                            <div class="service-item-content">
                                <p>زوايا حديد جاهزة بمقاسات مختلفة تُستخدم في الهياكل المعدنية وأعمال التدعيم.</p>
                            </div>
                        </div>
                        <div class="service-image-box">
                            <div class="service-item-image">
                                <figure class="image-anime">
                                    <img src="{{ asset('assets/images/home-2/aflak_046.png') }}" alt="">
                                </figure>
                            </div>
                            <div class="service-item-btn">
                                <a wire:navigate href="{{ route('contact') }}"><img src="{{ asset('assets/images/home/arrow-primary.svg') }}"
                                        alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Our products Section End -->
    <!-- Start Footer Rating  -->
    <div class="col-12">
        <div class="d-flex flex-column align-items-center gap-2 py-4">
            <!-- Top row -->
            <div class="d-flex align-items-center gap-3 our-products-section">
                <!-- Avatars -->
                <div class="d-flex align-items-center position-relative">
                    <img src="{{ asset('storage/' . $settings['branding.logo']) }}" alt="{{ $settings['site_name'] }}" class="rounded-circle border border-2 border-white bg-white"
                        width="38" height="38" alt="client">
                    <div class="rounded-circle accent-bg-color d-flex align-items-center justify-content-center border border-2 border-white ms-n2"
                        style="width:38px;height:38px;">
                        <img src="{{ asset('assets/images/home/icon-phone-primary.svg') }}" width="16" alt="phone">
                    </div>
                </div>
                <!-- Text -->
                <p class="mb-0 small text-dark">
                    توريد يعتمد عليه المقاولون —
                    <a wire:navigate href="{{ route('contact') }}" class="accent-color fw-semibold text-decoration-underline">
                        تواصل معنا لتجهيز طلبك بسرعة.
                    </a>
                </p>
            </div>
            <!-- Bottom row -->
            <div class="d-flex align-items-center gap-2 small fw-medium text-dark">
                <span>4.9/5</span>
                <div class="accent-color">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <span>تقييم 4200 </span>
            </div>
        </div>
    </div>
    <!-- End Footer Rating  -->
</main>
