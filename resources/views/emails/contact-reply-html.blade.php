<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $siteName }}</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f4f6f8;
    font-family:Tahoma, Arial, sans-serif;
">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:28px 0;">
    <tr>
        <td align="center">

            <!-- Container -->
            <table width="600" cellpadding="0" cellspacing="0" style="
                background-color:#ffffff;
                border-radius:16px;
                overflow:hidden;
                box-shadow:0 12px 32px rgba(0,0,0,0.06);
            ">

                <!-- Header -->
                <tr>
                    <td style="
                        background-color:{{ $secondary }};
                        padding:26px;
                        text-align:center;
                    ">

                        @if (!empty($logoUrl))
                            <img src="{{ $logoUrl }}"
                                 alt="{{ $siteName }}"
                                 style="
                                     max-width:130px;
                                     max-height:60px;
                                     display:block;
                                     margin:0 auto 14px auto;
                                 ">
                        @endif

                        <h1 style="
                            margin:0;
                            color:#ffffff;
                            font-size:22px;
                            font-weight:600;
                            letter-spacing:0.4px;
                        ">
                            {{ $siteName }}
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="
                        padding:32px;
                        color:#1f2937;
                        font-size:14px;
                        line-height:1.9;
                    ">

                        <p style="margin-top:0;">
                            {{ __('Hello') }}
                            <strong>{{ $contact->name }}</strong>,
                        </p>

                        <p>
                            {{ __('Thank you for contacting us. We have reviewed your message and are pleased to share our response below:') }}
                        </p>

                                                <!-- Project Type -->
                        @if($contact->project_type)
                            <p>
                                <strong>{{ __('Project Type:') }}</strong> {{ $contact->project_type }}
                            </p>
                        @endif

                        <!-- Services -->
                        @if($contact->services && count($contact->services) > 0)
                            <p>
                                <strong>{{ __('Selected Services:') }}</strong> 
                                {{ implode(', ', $contact->services) }}
                            </p>
                        @endif
                        
                        <!-- Reply Box -->
                        <div style="
                            background-color:#f8fafc;
                            border-inline-start:4px solid {{ $accent }};
                            padding:20px;
                            border-radius:12px;
                            margin:26px 0;
                            color:#0f172a;
                        ">
                            {!! nl2br(e($reply)) !!}
                        </div>

                        <hr style="
                            border:none;
                            border-top:1px solid #e5e7eb;
                            margin:30px 0;
                        ">

                        <!-- Original Message -->
                        <p style="
                            font-size:13px;
                            color:#64748b;
                            margin-bottom:0;
                        ">
                            <strong style="color:#334155;">
                                {{ __('Your original message:') }}
                            </strong>
                        </p>

                        <div style="
                            margin-top:10px;
                            padding:14px;
                            background:#fafafa;
                            border-radius:10px;
                            color:#475569;
                            font-size:13px;
                        ">
                            {{ $contact->message }}
                        </div>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="
                        background-color:#f9fafb;
                        padding:20px;
                        text-align:center;
                        font-size:12px;
                        color:#6b7280;
                        line-height:1.7;
                    ">
                        <strong style="color:#374151;">
                            {{ $siteName }}
                        </strong><br>

                        @if(!empty($location))
                            {{ $location }}<br>
                        @endif

                        @if(!empty($phone))
                            {{ $phone }}
                        @endif
                    </td>
                </tr>

            </table>
            <!-- /Container -->

        </td>
    </tr>
</table>

</body>
</html>
