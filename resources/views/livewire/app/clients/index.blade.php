<?php

use Livewire\Volt\Component;

new class extends Component {
    public int $active = 0; // 0,1,2
    public int $total = 3;

    public function next()
    {
        $this->active = ($this->active + 1) % $this->total;
    }

    public function prev()
    {
        $this->active = ($this->active - 1 + $this->total) % $this->total;
    }

    public function goTo(int $index)
    {
        $this->active = $index;
    }
};
?>


<div class="container">

    <div class="aflak-head">
        <h2 class="accent-color">آراء عملائنا</h2>
        <p>تعرّف على ثقة المقاولين، الشركات، وأصحاب الورش في نقطة تواصل—جودة وتوريد سريع حسب المواصفات.</p>
        <div class="aflak-top-glow"></div>
    </div>

    <div class="aflak-shell position-relative">

        <!-- Buttons -->
        {{-- <button class="aflak-nav prev" type="button" aria-label="السابق" wire:click="prev">
                <i class="fa-solid fa-chevron-right"></i>
            </button> --}}

        <div class="aflak-track" aria-label="سلايدر آراء العملاء">
            <!-- Card 1 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
<img src="https://images.pexels.com/photos/206172/pexels-photo-206172.jpeg"
     alt="مستودع مواد بناء منظم">

                    </div>

                    <h5 class="aflak-name fs-5">أبو فيصل – مقاول</h5>
                    <p class="aflak-meta">مشروع فلل سكنية</p>

                    <p class="aflak-quote">
                        “تعاملت مع نقطة تواصل في أكثر من توريدة. الحديد وصل نظيف ومطابق، والأهم إنهم ملتزمين بالموعد.
                        هذا الشي نادر بصراحة.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-clock"></i> التزام</span>
                        <span class="aflak-chip"><i class="fa-solid fa-shield-halved"></i> جودة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-truck-fast"></i> توصيل</span>
                    </div>
                </div>
            </article>

            <!-- Card 2 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
<img src="https://images.pexels.com/photos/2381463/pexels-photo-2381463.jpeg"
     alt="ورشة حدادة وقص حديد">

                    </div>

                    <h3 class="aflak-name">سعيد – صاحب ورشة</h3>
                    <p class="aflak-meta">قص صفائح وتجهيز</p>

                    <p class="aflak-quote">
                        “طلبنا قص صفائح بسماكات مختلفة، وكلها طلعت دقيقة.
                        ما اضطرينا نعيد أي قطعة، وهذا وفر علينا وقت وجهد.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-scissors"></i> قص دقيق</span>
                        <span class="aflak-chip"><i class="fa-solid fa-ruler-combined"></i> مقاسات</span>
                        <span class="aflak-chip"><i class="fa-solid fa-check"></i> اعتماد</span>
                    </div>
                </div>
            </article>

            <!-- Card 3 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
<img src="https://images.pexels.com/photos/906982/pexels-photo-906982.jpeg"
     alt="مبنى سكني قيد الإنشاء">


                    </div>

                    <h3 class="aflak-name">ريم – مشتريات</h3>
                    <p class="aflak-meta">توريد مواسير وزوايا</p>

                    <p class="aflak-quote">
                        “التعامل كان واضح من البداية. عرض السعر مفهوم،
                        والتوريد وصل مرتب ومغلف بدون أي ملاحظات.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-tags"></i> وضوح</span>
                        <span class="aflak-chip"><i class="fa-solid fa-box"></i> تنظيم</span>
                        <span class="aflak-chip"><i class="fa-solid fa-thumbs-up"></i> راحة</span>
                    </div>
                </div>
            </article>

            <!-- Card 4 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
<img src="https://images.pexels.com/photos/1216589/pexels-photo-1216589.jpeg"
     alt="مبنى قيد الإنشاء">

                    </div>

                    <h3 class="aflak-name">أبو ناصر – مشرف موقع</h3>
                    <p class="aflak-meta">حديد تسليح</p>

                    <p class="aflak-quote">
                        “الحديد وصل جاهز للاستخدام بدون فرز أو تنظيف.
                        هذا الشي فرق معنا كثير في سرعة التنفيذ.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-broom"></i> نظافة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-stopwatch"></i> سرعة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-check"></i> اعتماد</span>
                    </div>
                </div>
            </article>

            <!-- Card 5 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
<img src="https://images.pexels.com/photos/259966/pexels-photo-259966.jpeg"
     alt="مشروع تجاري إنشائي">


                    </div>

                    <h5 class="aflak-name fs-5">ماجد – مقاول</h5>
                    <p class="aflak-meta">مشروع تجاري</p>

                    <p class="aflak-quote">
                        “أكثر شيء عجبني إن التواصل سريع وما فيه لف ودوران.
                        تسأل عن الكمية، يعطيك الرد مباشرة.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-phone"></i> تواصل</span>
                        <span class="aflak-chip"><i class="fa-solid fa-bolt"></i> سرعة رد</span>
                        <span class="aflak-chip"><i class="fa-solid fa-handshake"></i> تعامل</span>
                    </div>
                </div>
            </article>

            <!-- Card 6 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
<img src="https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg"
     alt="موقع أعمال ومقاولات">

                    </div>

                    <h3 class="aflak-name">يحيى – إدارة مشاريع</h3>
                    <p class="aflak-meta">توريد متنوع</p>

                    <p class="aflak-quote">
                        “جرّبنا أكثر من مورد، لكن نقطة تواصل كانوا الأوضح والأكثر التزام.
                        حاليًا هم خيارنا الأول.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-star"></i> ثقة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-repeat"></i> تعامل مستمر</span>
                        <span class="aflak-chip"><i class="fa-solid fa-award"></i> اختيار أول</span>
                    </div>
                </div>
            </article>
        </div>

        {{-- <button class="aflak-nav next" type="button" aria-label="التالي" wire:click="next">

                <i class="fa-solid fa-chevron-left"></i>
            </button> --}}

    </div>
</div>
