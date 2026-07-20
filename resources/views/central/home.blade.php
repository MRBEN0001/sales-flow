<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Pharmacy POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .hero { padding: 4rem 0 3rem; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
        .app-brand {
            background: #0d6efd;
            color: #fff !important;
            font-weight: 700;
            padding: 0.4rem 0.85rem;
            border-radius: 0.375rem;
        }
        .app-brand:hover { color: #fff !important; opacity: 0.92; }
        .pricing-note {
            color: #000;
            font-weight: 600;
        }
        .hero-subtitle {
            color: #000;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand app-brand" href="{{ route('dev.login') }}">{{ config('app.name') }}</a>
        <div class="d-flex gap-2">
            <a href="{{ route('central.login') }}" class="btn btn-outline-primary btn-sm">Log in</a>
            <a href="{{ route('central.plans') }}" class="btn btn-primary btn-sm">Subscription Plans</a>
        </div>
    </div>
</nav>

<header class="hero text-center">
    <div class="container col-lg-8">
        <h1 class="display-5 fw-bold mb-3">Scan-to-sell and inventory software designed to make running your business effortless.</h1>
        <p class="lead hero-subtitle mb-4">
            Run your shop from any device. Barcode scanning, receipts, stock reports, and daily sales — each shop gets its own secure workspace.
        </p>
        <a href="{{ route('central.register') }}" class="btn btn-primary btn-lg">Create your shop — {{ config('subscription.trial_days') }} days free trial</a>
        <p class="mt-2 mb-2">
            <a href="{{ route('central.plans') }}" class="text-decoration-none fw-semibold">See plans</a>
        </p>
        <p class="pricing-note mb-2">
            From ₦{{ number_format(subscription_monthly_price_ngn()) }}/month.
        </p>
        <p class="mb-0">
            <a href="{{ route('central.login') }}" class="text-decoration-none">Already registered? Log in to your shop</a>
        </p>
    </div>
</header>

<section class="pb-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Point of sale</h5>
                        <p class="card-text text-muted">Scan to sell, Fast checkout, discounts, printable receipts, and resume pending sales.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Stock control</h5>
                        <p class="card-text text-muted">Out of stock tracking, expiry tracking, sales sections, barcode labels, take-stock PDF reports and so much more.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Your own address</h5>
                        <p class="card-text text-muted">Each shop lives in the cloud with isolated data.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('partials.whatsapp-support')
</body>
</html>
