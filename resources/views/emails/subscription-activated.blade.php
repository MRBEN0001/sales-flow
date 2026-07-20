<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription activated</title>
</head>
<body style="margin:0;padding:0;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background:#eef3f8;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;">
                <tr>
                    <td style="padding:28px 28px 22px;background:#0d6efd;color:#ffffff;text-align:center;">
                        <div style="font-size:16px;font-weight:700;margin-bottom:8px;">{{ config('app.name') }}</div>
                        <h1 style="margin:0;font-size:24px;">
                            @if (! empty($forCompany))
                                Payment received
                            @else
                                Subscription activated
                            @endif
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        @if (! empty($forCompany))
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#475569;">
                                A shop subscription payment was completed successfully.
                            </p>
                        @else
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#475569;">
                                Thank you. Your shop subscription is now active.
                            </p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:20px;">
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;color:#64748b;font-size:13px;width:40%;">Shop</td>
                                <td style="padding:12px 14px;font-size:14px;font-weight:700;">{{ $tenant->shop_name }}</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;color:#64748b;font-size:13px;border-top:1px solid #e2e8f0;">Plan</td>
                                <td style="padding:12px 14px;font-size:14px;border-top:1px solid #e2e8f0;">{{ $planLabel }}</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;color:#64748b;font-size:13px;border-top:1px solid #e2e8f0;">Amount paid</td>
                                <td style="padding:12px 14px;font-size:14px;border-top:1px solid #e2e8f0;">₦{{ number_format($payment->amount) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;color:#64748b;font-size:13px;border-top:1px solid #e2e8f0;">Status</td>
                                <td style="padding:12px 14px;font-size:14px;font-weight:700;border-top:1px solid #e2e8f0;color:#16a34a;">Active</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;color:#64748b;font-size:13px;border-top:1px solid #e2e8f0;">Next expiry</td>
                                <td style="padding:12px 14px;font-size:14px;font-weight:700;border-top:1px solid #e2e8f0;">
                                    {{ optional($tenant->subscription_ends_at)->timezone(config('app.timezone'))->format('l, d F Y') ?: '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;color:#64748b;font-size:13px;border-top:1px solid #e2e8f0;">Reference</td>
                                <td style="padding:12px 14px;font-size:13px;border-top:1px solid #e2e8f0;word-break:break-all;">{{ $payment->reference }}</td>
                            </tr>
                        </table>

                        <p style="margin:0 0 18px;">
                            <a href="{{ $loginUrl }}" style="display:inline-block;background:#0d6efd;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700;">
                                Go to shop login
                            </a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
