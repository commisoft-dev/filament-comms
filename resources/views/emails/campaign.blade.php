<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#1f2937;">
    @if(!empty($preheader))
        <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">
            {{ $preheader }}
        </div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:24px 32px; border-bottom:3px solid {{ config('filament-comms.primary_color', '#28bd0c') }}; text-align:left;">
                            @if(config('filament-comms.brand_logo'))
                                <img src="{{ config('filament-comms.brand_logo') }}" alt="{{ config('filament-comms.brand_name') }}" style="max-height:36px; width:auto;">
                            @else
                                <span style="font-size:20px; font-weight:700; color:#111827;">{{ config('filament-comms.brand_name') }}</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px; font-size:15px; line-height:1.65; color:#374151;">
                            {!! $bodyHtml !!}
                        </td>
                    </tr>

                    <!-- Footer / Unsubscribe -->
                    <tr>
                        <td style="padding:24px 32px; background-color:#f9fafb; border-top:1px solid #e5e7eb; font-size:12px; line-height:1.6; color:#6b7280; text-align:center;">
                            <p style="margin:0 0 8px 0;">
                                You are receiving this email from <strong>{{ config('filament-comms.brand_name') }}</strong>
                                ({{ config('filament-comms.categories.' . $category . '.label', ucfirst($category)) }}).
                            </p>
                            <p style="margin:0 0 8px 0;">
                                <a href="{{ $preferencesUrl }}" style="color:{{ config('filament-comms.primary_color', '#28bd0c') }}; text-decoration:underline; font-weight:600;">
                                    Manage Email Preferences or Unsubscribe
                                </a>
                            </p>
                            <p style="margin:0; color:#9ca3af;">
                                {{ config('filament-comms.brand_name') }} &bull; {{ config('filament-comms.footer_address') }}
                                @if(config('filament-comms.support_email'))
                                    &bull; {{ config('filament-comms.support_email') }}
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
