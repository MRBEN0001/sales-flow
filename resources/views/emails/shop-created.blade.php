<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your shop is ready</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { padding: 14px 8px !important; }
            .email-card { width: 100% !important; border-radius: 12px !important; }
            .email-header { padding: 26px 20px !important; }
            .email-content { padding: 22px 18px !important; }
            .detail-label,
            .detail-value {
                display: block !important;
                width: auto !important;
                text-align: left !important;
            }
            .detail-label { padding: 12px 14px 3px !important; }
            .detail-value { padding: 0 14px 12px !important; }
            .login-button { display: block !important; text-align: center !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#0f172a;-webkit-text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-shell" style="width:100%;background:#eef3f8;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="email-card" style="width:100%;max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 12px 36px rgba(15,23,42,0.10);">
                <tr>
                    <td class="email-header" style="padding:34px 34px 30px;background:#0d6efd;text-align:center;color:#ffffff;">
                        <div style="display:inline-block;margin-bottom:14px;padding:7px 13px;border-radius:7px;background:rgba(255,255,255,0.16);font-size:16px;font-weight:700;">
                            {{ config('app.name') }}
                        </div>
                        <h1 style="margin:0 0 8px;font-size:27px;line-height:1.25;color:#ffffff;">
                            @if (! empty($forCompany))
                                New shop registered
                            @else
                                Your shop is ready!
                            @endif
                        </h1>
                        <p style="margin:0;font-size:15px;line-height:1.6;color:#dbeafe;">
                            @if (! empty($forCompany))
                                A new shop was created on {{ config('app.name') }}.
                            @else
                                Everything has been set up successfully.
                            @endif
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="email-content" style="padding:30px 34px 34px;">
                        @if (! empty($forCompany))
                            <p style="margin:0 0 8px;font-size:17px;font-weight:700;color:#0f172a;">Sales Flow team,</p>
                            <p style="margin:0 0 22px;font-size:15px;line-height:1.65;color:#475569;">
                                A new shop was registered. Details are below for your records.
                            </p>
                        @else
                            <p style="margin:0 0 8px;font-size:17px;font-weight:700;color:#0f172a;">Hi {{ $adminName }},</p>
                            <p style="margin:0 0 22px;font-size:15px;line-height:1.65;color:#475569;">
                                Welcome to {{ config('app.name') }}. Your shop has been created successfully. Keep this email for your shop and subscription details.
                            </p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 24px;border:1px solid #dbe3ec;border-radius:10px;border-collapse:separate;overflow:hidden;">
                            <tr>
                                <td class="detail-label" width="38%" style="padding:13px 16px;border-bottom:1px solid #e8edf3;background:#f8fafc;color:#64748b;font-size:13px;">Shop name</td>
                                <td class="detail-value" style="padding:13px 16px;border-bottom:1px solid #e8edf3;font-size:14px;font-weight:700;color:#0f172a;">{{ $tenant->shop_name }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label" style="padding:13px 16px;border-bottom:1px solid #e8edf3;background:#f8fafc;color:#64748b;font-size:13px;">Shop address</td>
                                <td class="detail-value" style="padding:13px 16px;border-bottom:1px solid #e8edf3;font-size:14px;color:#0f172a;word-break:break-word;">{{ $tenant->id }}.{{ config('app.tenant_domain') }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label" style="padding:13px 16px;border-bottom:1px solid #e8edf3;background:#f8fafc;color:#64748b;font-size:13px;">Admin email</td>
                                <td class="detail-value" style="padding:13px 16px;border-bottom:1px solid #e8edf3;font-size:14px;color:#0f172a;word-break:break-word;">{{ $tenant->owner_email }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label" style="padding:13px 16px;border-bottom:1px solid #e8edf3;background:#f8fafc;color:#64748b;font-size:13px;">Phone number</td>
                                <td class="detail-value" style="padding:13px 16px;border-bottom:1px solid #e8edf3;font-size:14px;color:#0f172a;">{{ $tenant->owner_phone ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label" style="padding:13px 16px;border-bottom:1px solid #e8edf3;background:#f8fafc;color:#64748b;font-size:13px;">Current plan</td>
                                <td class="detail-value" style="padding:13px 16px;border-bottom:1px solid #e8edf3;font-size:14px;font-weight:700;color:#0f172a;">
                                    @if ($onTrial)
                                        Free trial ({{ config('subscription.trial_days') }} days)
                                    @else
                                        Payment required
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label" style="padding:13px 16px;background:#f8fafc;color:#64748b;font-size:13px;">
                                    @if ($onTrial)
                                        Trial expires
                                    @else
                                        Access status
                                    @endif
                                </td>
                                <td class="detail-value" style="padding:13px 16px;font-size:14px;font-weight:700;color:#0f172a;">
                                    @if ($onTrial && $tenant->trial_ends_at)
                                        {{ $tenant->trial_ends_at->timezone(config('app.timezone'))->format('l, d F Y') }}
                                    @else
                                        Subscribe to unlock — from ₦{{ number_format(subscription_monthly_price_ngn()) }}/month
                                    @endif
                                </td>
                            </tr>
                        </table>

                        @if ($onTrial && $tenant->trial_ends_at)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 24px;background:#eff6ff;border-left:4px solid #0d6efd;border-radius:7px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:14px;line-height:1.55;color:#1e3a8a;">
                                        @if (! empty($forCompany))
                                            Free trial is active until <strong>{{ $tenant->trial_ends_at->timezone(config('app.timezone'))->format('d F Y') }}</strong>.
                                        @else
                                            Your free trial remains active until <strong>{{ $tenant->trial_ends_at->timezone(config('app.timezone'))->format('d F Y') }}</strong>. After that, choose a subscription plan to continue using your shop.
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        @endif

                        @if (empty($forCompany))
                            <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#475569;">
                                Use the admin email above and the password you chose during registration to sign in.
                            </p>
                        @endif

                        <p style="margin:0 0 24px;">
                            <a href="{{ $loginUrl }}" class="login-button" style="display:inline-block;background:#0d6efd;color:#ffffff;text-decoration:none;padding:14px 22px;border-radius:8px;font-size:15px;font-weight:700;">
                                @if (! empty($forCompany))
                                    Open shop login
                                @else
                                    Go to shop login
                                @endif
                            </a>
                        </p>

                        <p style="margin:0 0 24px;font-size:12px;color:#94a3b8;line-height:1.55;">
                            If the button does not work, copy this link into your browser:<br>
                            <a href="{{ $loginUrl }}" style="color:#0d6efd;word-break:break-all;">{{ $loginUrl }}</a>
                        </p>

                        @if (empty($forCompany))
                            <div style="padding-top:20px;border-top:1px solid #e8edf3;text-align:center;">
                                <p style="margin:0 0 5px;font-size:13px;color:#64748b;">Need help? Contact Sales Flow support</p>
                                <a href="mailto:{{ config('dev.admin.email') }}" style="font-size:13px;color:#0d6efd;text-decoration:none;">{{ config('dev.admin.email') }}</a>
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
            <p style="margin:18px 0 0;font-size:12px;color:#94a3b8;text-align:center;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </td>
    </tr>
</table>
</body>
</html>
