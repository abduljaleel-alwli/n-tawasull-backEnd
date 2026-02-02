<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Settings\SettingsService;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $s = app(SettingsService::class);

        // ---> Settings 
        // General
        $s->set('site_name', 'أفلاك', 'string', 'general');
        $s->set('site_description', 'أفلاك شركة متخصصة في توريد الحديد والصاج والمواسير والزوايا والجسور بمقاسات متعددة وجودة عالية، لخدمة مشاريع البناء والصناعة بأفضل الأسعار والتسليم السريع.', 'text', 'general');

        // Branding
        $s->set('branding.logo', null, 'image', 'branding');
        $s->set('branding.favicon', null, 'image', 'branding');

        // Colors
        $s->set('colors.secondary', '#0d6efdf2', 'color', 'colors');
        $s->set('colors.accent', '#FED403', 'color', 'colors');
        $s->set('colors.background', '#0b1220', 'color', 'colors');

        // SEO
        $s->set('seo.meta_title', 'أفلاك | توريد وتجارة مواد البناء', 'string', 'seo');
        $s->set('seo.meta_description', 'أفلاك لتوريد وتجارة مواد البناء: حديد، صفائح، مواسير، شبك وحديد تسليح، مع خدمات قص وتشكيل وتوصيل سريع.', 'text', 'seo');
        $s->set('seo.keywords', 'أفلاك, حديد, مواسير, صفائح, مواد بناء, توريد, قص حديد, شبك, تسليح', 'text', 'seo');

        // Scripts
        $s->set('scripts.head', null, 'string', 'scripts');
        $s->set('scripts.footer', null, 'string', 'scripts');

        // ---> Contact
        $s->set(
            'contact.title',
            'توريد حديد • مواسير • صفائح • زوايا',
            'string',
            'contact'
        );

        $s->set(
            'contact.description',
            'توريد منظم للمشاريع والورش مع إمكانية تجهيز المقاسات والتسليم للموقع.',
            'text',
            'contact'
        );

        $s->set(
            'contact.email_to',
            'info@n-tawasull.sa',
            'string',
            'contact'
        );

        $s->set(
            'contact.phone',
            '+966555218270',
            'string',
            'contact'
        );

        $s->set(
            'contact.map_url',
            'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d927758.0367743374!2d46.163015146218584!3d24.724997784947895!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2f03890d489399%3A0xba974d1c98e79fd5!2sRiyadh%20Saudi%20Arabia!5e0!3m2!1sen!2s!4v1767366798764!5m2!1sen!2s',
            'text',
            'contact'
        );

        $s->set(
            'contact.location',
            'السعودية - الرياض',
            'string',
            'contact'
        );

        $s->set(
            'contact.working_time',
            'السبت - الخميس: 9:00 - 18:00',
            'string',
            'contact'
        );

        $s->set(
            'contact.email_subject',
            'رسالة جديدة نموذج تواصل معنا (أفلاك)',
            'string',
            'contact'
        );

        $s->set(
            'contact.social_links',
            [
                [
                    'platform' => 'Email',
                    'url' => 'mailto:info@n-tawasull.sa',
                    'icon_type' => 'class',
                    'icon_value' => 'fa-solid fa-envelope',
                ],
                [
                    'platform' => 'WhatsApp',
                    'url' => 'https://wa.me/966555218270',
                    'icon_type' => 'class',
                    'icon_value' => 'fa-brands fa-whatsapp',
                ],
                [
                    'platform' => 'Facebook',
                    'url' => 'https://facebook.com/yourpage',
                    'icon_type' => 'class',
                    'icon_value' => 'fa-brands fa-facebook-f',
                ],
                [
                    'platform' => 'Instagram',
                    'url' => 'https://instagram.com/yourusername',
                    'icon_type' => 'class',
                    'icon_value' => 'fa-brands fa-instagram',
                ],
                [
                    'platform' => 'X',
                    'url' => 'https://x.com/yourusername',
                    'icon_type' => 'class',
                    'icon_value' => 'fa-brands fa-x-twitter',
                ],
            ],
            'json',
            'contact'
        );


        // ---> About
        $s->set(
            'about.title',
            'من نحن',
            'string',
            'about'
        );

        $s->set(
            'about.subtitle',
            'اطلب عرض سعر الآن',
            'string',
            'about'
        );

        $s->set(
            'about.description',
            'أفلاك شركة متخصصة في توريد الحديد والصاج والمواسير والزوايا والجسور بمقاسات متعددة وجودة عالية، لخدمة مشاريع البناء والصناعة بأفضل الأسعار والتسليم السريع.',
            'text',
            'about'
        );

        $s->set(
            'about.features',
            [
                [
                    'title' => 'توريد موثوق',
                    'description' => 'نوفر مواد الحديد والصاج والمواسير بمواصفات ثابتة وتوريد منتظم للمشاريع.',
                    'icon_type' => 'flux',
                    'icon_value' => 'user',
                ],
                [
                    'title' => 'جودة ومعايير',
                    'description' => 'نلتزم بالمقاسات والمعايير المطلوبة لضمان قوة التحمل والسلامة في الاستخدام.',
                    'icon_type' => 'flux',
                    'icon_value' => 'beaker',
                ],
                [
                    'title' => 'خدمة سريعة',
                    'description' => 'متابعة الطلبات وتجهيزها بسرعة مع دعم فني لاختيار المقاسات المناسبة لمشروعك.',
                    'icon_type' => 'flux',
                    'icon_value' => 'flag',
                ],
                [
                    'title' => 'رؤيتنا',
                    'description' => 'أن نكون الخيار الأول في توريد الحديد ومستلزمات البناء بجودة موثوقة وسعر منافس.',
                    'icon_type' => 'flux',
                    'icon_value' => 'eye',
                ],
                [
                    'title' => 'رسالتنا',
                    'description' => 'تزويد عملائنا بمواد حديد وصاج ومواسير وزوايا وجسور بمقاسات متعددة، مع استشارة سريعة وتوريد منظم يضمن نجاح مشاريعهم.',
                    'icon_type' => 'flux',
                    'icon_value' => 'chat-bubble-left-right',
                ],
            ],
            'json',
            'about'
        );

    }
}
