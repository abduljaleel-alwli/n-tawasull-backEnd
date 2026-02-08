<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نقطة تواصل | شريكك التسويقي الاستراتيجي</title>
    <link rel="icon" type="image/png" href="./assets/logo-FPSStkaL.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Mouse Follower CSS -->
    <link rel="stylesheet" href="https://unpkg.com/mouse-follower@1/dist/mouse-follower.min.css">
    
    <!-- GSAP for Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <!-- Mouse Follower JS -->
    <script src="https://unpkg.com/mouse-follower@1/dist/mouse-follower.min.js"></script>
    <!-- Particles JS -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            scroll-behavior: smooth;
            background-color: #f0f0f0;
            overflow-x: hidden;
            /* cursor: none; */
        }

        /* Particles Background */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
            background-color: transparent;
        }

        /* Customize Mouse Follower Identity */
        .mf-cursor {
            color: #EF7F17; /* Text color inside cursor */
            z-index: 1000;
        }
        .mf-cursor:before {
            background: #203C71; /* Main circle color */
            opacity: 0.15;
        }
        .mf-cursor.-text:before {
            opacity: 1;
            background: #203C71;
        }
        .mf-cursor.-pointer:before {
            transform: scale(2);
            opacity: 0.1;
        }

        /* Core Transition Curves */
        .dramatic-transition {
            transition: all 1.1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Home Content States */
        .home-exit-up {
            transform: translateY(-100vh) !important;
            opacity: 0 !important;
        }
        .home-hidden-bottom {
            transform: translateY(100vh);
            opacity: 0;
            transition: none !important;
        }
        .home-visible {
            transform: translateY(0);
            opacity: 1;
        }

        /* Detail Layer States */
        .detail-hidden-bottom {
            transform: translateY(100vh);
            opacity: 0;
            pointer-events: none;
        }
        .detail-active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .detail-exit-up {
            transform: translateY(-100vh) !important;
            opacity: 0 !important;
            pointer-events: none;
        }

        /* Scroll Reveal Styles */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), 
                        transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform, opacity;
        }

        .reveal.reveal-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Initial Entrance Animations */
        @keyframes entranceSlideDown {
            from { transform: translateY(-40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes entranceSlideUp {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes entranceScaleUp {
            from { transform: scale(0.95) translateY(20px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }

        .animate-entrance-down {
            animation: entranceSlideDown 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-entrance-up {
            animation: entranceSlideUp 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0; 
        }

        .animate-entrance-scale {
            animation: entranceScaleUp 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        /* Staggered delay helpers */
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }
        .delay-600 { animation-delay: 600ms; }
        .delay-700 { animation-delay: 700ms; }
        .delay-800 { animation-delay: 800ms; }
        .delay-900 { animation-delay: 900ms; }
        .delay-1000 { animation-delay: 1000ms; }
        .delay-1200 { animation-delay: 1200ms; }
        .delay-1400 { animation-delay: 1400ms; }
        .delay-1600 { animation-delay: 1600ms; }
        .delay-1800 { animation-delay: 1800ms; }
        .delay-2000 { animation-delay: 2000ms; }
        .delay-2200 { animation-delay: 2200ms; }
        .delay-2400 { animation-delay: 2400ms; }
        .delay-2600 { animation-delay: 2600ms; }
        .delay-2800 { animation-delay: 2800ms; }
        .delay-3000 { animation-delay: 3000ms; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f0f0f0;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d1d1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #bbbbbb;
        }

        .no-scroll {
            overflow: hidden !important;
        }

        /* Ensure interactive elements show they are clickable even with custom cursor (Desktop Only) */
        @media (min-width: 769px) {
            a, button, [role="button"], input, textarea, select {
                cursor: none !important;
            }
        }

        /* Hide mouse follower completely on mobile */
        @media (max-width: 768px) {
            .mf-cursor {
                display: none !important;
            }
        }
    </style>

<script type="importmap">
{
  "imports": {
    "react-dom/": "https://esm.sh/react-dom@^19.2.4/",
    "lucide-react": "https://esm.sh/lucide-react@^0.563.0",
    "vite": "https://esm.sh/vite@^7.3.1",
    "@vitejs/plugin-react": "https://esm.sh/@vitejs/plugin-react@^5.1.2",
    "react/": "https://esm.sh/react@^19.2.4/",
    "react": "https://esm.sh/react@^19.2.4"
  }
}
</script>
  <script type="module" crossorigin src="./assets/index-D4-dL1lz.js"></script>
</head>
<body class="bg-[#f0f0f0] text-[#111111]">
    <div id="particles-js"></div>
    <div id="root" class="relative z-10"></div>

</body>
</html>