<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preferences — {{ config('filament-comms.brand_name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; color: #111827; margin: 0; padding: 32px 16px; }
        .card { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
        .brand { font-size: 20px; font-weight: 700; margin-bottom: 4px; color: {{ config('filament-comms.primary_color', '#28bd0c') }}; }
        .sub { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        .alert { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .item { display: flex; align-items: flex-start; gap: 12px; padding: 14px 0; border-bottom: 1px solid #e5e7eb; }
        .item:last-of-type { border-bottom: none; }
        .item-title { font-weight: 600; font-size: 15px; }
        .item-desc { color: #6b7280; font-size: 13px; margin-top: 2px; }
        .danger-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; }
        .btn-primary { background: {{ config('filament-comms.primary_color', '#28bd0c') }}; color: #ffffff; }
        .input { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-top: 8px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">{{ config('filament-comms.brand_name') }}</div>
        <div class="sub">Email Preferences for <strong>{{ $email }}</strong></div>

        @if($saved)
            <div class="alert">Your email preferences have been updated.</div>
        @endif

        <form method="POST" action="{{ request()->fullUrl() }}">
            @csrf

            @foreach($categories as $key => $meta)
                <label class="item">
                    <input type="checkbox" name="preferences[{{ $key }}]" value="1"
                        {{ !empty($preferences[$key]) && !$unsubscribedAll ? 'checked' : '' }}
                        style="margin-top:4px; width:16px; height:16px;">
                    <div>
                        <div class="item-title">
                            {{ $meta['label'] }}
                            @if($highlightedCategory === $key)
                                <span style="font-size:11px; background:#fef3c7; color:#92400e; padding:2px 6px; border-radius:4px; margin-left:6px;">This Email</span>
                            @endif
                        </div>
                        <div class="item-desc">{{ $meta['description'] ?? '' }}</div>
                    </div>
                </label>
            @endforeach

            <div class="danger-box">
                <label style="display:flex; align-items:center; gap:10px; font-weight:600; color:#991b1b; font-size:14px;">
                    <input type="checkbox" name="unsubscribed_all" value="1" {{ $unsubscribedAll ? 'checked' : '' }}>
                    Unsubscribe from all marketing, newsletters &amp; digests
                </label>
                <input type="text" name="unsubscribe_reason" class="input" value="{{ $unsubscribeReason }}" placeholder="Optional: tell us why (e.g. too many emails)...">
            </div>

            <div style="margin-top:24px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:12px; color:#6b7280;">Essential security &amp; billing notices are always sent.</span>
                <button type="submit" class="btn btn-primary">Save Preferences</button>
            </div>
        </form>
    </div>
</body>
</html>
