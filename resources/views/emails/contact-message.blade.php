<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <title>{{ settings('contact.email_subject', __('New contact message')) }}</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
        <tr>
            <td align="center">

                {{-- Card --}}
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:12px; overflow:hidden;
                              box-shadow:0 10px 30px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#0f172a; padding:20px 30px;">
                            <h2 style="margin:0; color:#ffffff; font-size:20px;">
                                {{ settings('contact.email_subject', __('New contact message')) }}
                            </h2>
                            <p style="margin:6px 0 0; color:#cbd5e1; font-size:13px;">
                                {{ __('You have received a new message from the contact form') }}
                            </p>
                        </td>
                    </tr>

                    {{-- Content --}}
                    <tr>
                        <td style="padding:30px; color:#0f172a; font-size:14px;">

                            {{-- Info table --}}
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="border-collapse:collapse; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:8px 0; font-weight:bold; width:140px;">
                                        {{ __('Name') }}:
                                    </td>
                                    <td style="padding:8px 0;">
                                        {{ $contact->name }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0; font-weight:bold;">
                                        {{ __('Email') }}:
                                    </td>
                                    <td style="padding:8px 0;">
                                        <a href="mailto:{{ $contact->email }}"
                                            style="color:#2563eb; text-decoration:none;">
                                            {{ $contact->email }}
                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0; font-weight:bold;">
                                        {{ __('Phone') }}:
                                    </td>
                                    <td style="padding:8px 0;">
                                        {{ $contact->phone ?: '—' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0; font-weight:bold;">
                                        {{ __('IP address') }}:
                                    </td>
                                    <td style="padding:8px 0; color:#475569;">
                                        {{ $contact->ip_address ?? '—' }}
                                    </td>
                                </tr>

                                {{-- Project Type --}}
                                @if($contact->project_type)
                                    <tr>
                                        <td style="padding:8px 0; font-weight:bold;">
                                            {{ __('Project Type') }}:
                                        </td>
                                        <td style="padding:8px 0;">
                                            {{ $contact->project_type }}
                                        </td>
                                    </tr>
                                @endif

                                                                {{-- Selected Services --}}
                                @if($contact->services && count($contact->services) > 0)
                                    <tr>
                                        <td style="padding:8px 0; font-weight:bold;">
                                            {{ __('Selected Services') }}:
                                        </td>
                                        <td style="padding:8px 0;">
                                            {{ implode(', ', $contact->services) }}
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            {{-- Message --}}
                            <div>
                                <p style="margin:0 0 10px; font-weight:bold;">
                                    {{ __('Message') }}:
                                </p>

                                <div
                                    style="background:#f8fafc; border:1px solid #e2e8f0;
                                            border-radius:8px; padding:16px; color:#334155;
                                            line-height:1.7;">
                                    {{ $contact->message }}
                                </div>
                            </div>

                            <div style="background:#ffffff; border-radius:12px; overflow:hidden;
                              box-shadow:0 10px 30px rgba(0,0,0,0.08);">

                                @if ($downloadUrl)
                                    <p style="margin-top:16px;">
                                        📎 <strong>{{ __('Attachment') }}:</strong><br>
                                        <a href="{{ $downloadUrl }}" target="_blank">
                                            {{ __('Download attachment') }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="background:#f1f5f9; padding:16px 30px;
                                   text-align:center; font-size:12px; color:#64748b;">
                            {{ __('This email was sent automatically from your website contact form.') }}
                        </td>
                        {{ $settings['site_name'] }}
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
