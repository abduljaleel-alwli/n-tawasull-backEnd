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
        $s->set('site_name', 'نقطة تواصل', 'string', 'general');
        $s->set('site_description', 'نقطة تواصل شركة متخصصة في توريد الحديد والصاج والمواسير والزوايا والجسور بمقاسات متعددة وجودة عالية، لخدمة مشاريع البناء والصناعة بأفضل الأسعار والتسليم السريع.', 'text', 'general');

        // Branding
        $s->set('branding.logo', null, 'image', 'branding');
        $s->set('branding.favicon', null, 'image', 'branding');

        // Colors
        $s->set('colors.secondary', '#0d6efdf2', 'color', 'colors');
        $s->set('colors.accent', '#FED403', 'color', 'colors');
        $s->set('colors.background', '#0b1220', 'color', 'colors');

        // SEO
        $s->set('seo.meta_title', 'نقطة تواصل', 'string', 'seo');
        $s->set('seo.meta_description', 'نقطة تواصل لتوريد وتجارة مواد البناء: حديد، صفائح، مواسير، شبك وحديد تسليح، مع خدمات قص وتشكيل وتوصيل سريع.', 'text', 'seo');
        $s->set('seo.keywords', 'نقطة تواصل, حديد, مواسير, صفائح, مواد بناء, توريد, قص حديد, شبك, تسليح', 'text', 'seo');

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
            'رسالة جديدة نموذج تواصل معنا (نقطة تواصل)',
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
            'نقطة تواصل شركة متخصصة في توريد الحديد والصاج والمواسير والزوايا والجسور بمقاسات متعددة وجودة عالية، لخدمة مشاريع البناء والصناعة بأفضل الأسعار والتسليم السريع.',
            'text',
            'about'
        );

        $s->set(
            'features.items',
            [
                [
                    'title' => '',
                    'description' => '',
                    'icon_type' => 'svg',
                    'icon_value' => '',
                ],
            ],
            'json',
            'features'
        );

    }
}
