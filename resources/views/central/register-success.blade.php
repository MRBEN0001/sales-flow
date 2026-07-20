<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop created — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-shadow text-center">
                <div class="card-body p-5">
                    @if ($onTrial)
                        <h1 class="h3 text-success mb-3">Your shop is ready</h1>
                        <p class="text-muted mb-4">
                            <strong>{{ $shopName }}</strong> was created with a {{ $trialDays }} days free trial.
                            Your shop database, admin account, and settings are ready — log in below to start selling.
                            We also sent the shop details and trial expiry date to your admin email.
                        </p>
                    @else
                        <h1 class="h3 text-warning mb-3">Shop created — payment required</h1>
                        <p class="text-muted mb-4">
                            <strong>{{ $shopName }}</strong> was created, but this device already used a free trial.
                            To continue, subscribe at <strong>₦{{ number_format($monthlyPriceNgn) }}/month</strong>.
                            Log in below and complete payment to unlock your shop.
                            We also sent the shop details to your admin email.
                        </p>
                    @endif
                    <a href="{{ $shopLoginUrl }}" class="btn btn-primary btn-lg">Go to your shop login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.whatsapp-support')
</body>
</html>
