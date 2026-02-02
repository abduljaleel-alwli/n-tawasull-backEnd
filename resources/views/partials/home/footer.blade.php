<!-- ---------------- My Scripts ----------------- -->
<!-- Scripts -->
<script data-page-style src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<!-- Animations -->
{{-- <script data-page-style src="{{ asset('assets/js/validator.min.js') }}"></script> --}}
<script data-page-style src="{{ asset('assets/js/jquery.slicknav.js') }}"></script>
<script data-page-style src="{{ asset('assets/js/jquery.waypoints.min.js') }}"></script>
<script data-page-style src="{{ asset('assets/js/jquery.counterup.min.js') }}"></script>
{{-- <script data-page-style src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script> --}}
<!-- SmoothScroll -->
<script data-page-style src="{{ asset('assets/js/SmoothScroll.js') }}"></script>
<script data-page-style src="{{ asset('assets/js/parallaxie.js') }}"></script>
<!-- Mouse Cursor -->
<script data-page-style src="{{ asset('assets/js/gsap.min.js') }}"></script>
<script data-page-style src="{{ asset('assets/js/magiccursor.js') }}"></script>
<!-- Animations Images With Scroll -->
<script data-page-style src="{{ asset('assets/js/ScrollTrigger.min.js') }}"></script>
<!-- Animations -->
<script data-page-style src="{{ asset('assets/js/wow.min.js') }}"></script>
<!-- Main script -->
<script data-page-style src="{{ asset('assets/js/function.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>

  <script>
    var swiper = new Swiper(".mySwiper", {
      cssMode: true,
      loop: true,

      autoplay: {
        delay: 2500,
        disableOnInteraction: false
      },

      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },

      pagination: {
        el: ".swiper-pagination",
        clickable: true
      },

      mousewheel: true,
      keyboard: true,
    });
  </script>