<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscribe {{ $planLabel }} — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="{{ route('central.home') }}">{{ config('app.name') }}</a>
        <a href="{{ route('central.plans') }}" class="btn btn-outline-primary btn-sm">All plans</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="text-center mb-4">
                <a href="{{ route('central.plans') }}" class="text-decoration-none text-muted">&larr; Back to plans</a>
                <h1 class="h3 mt-2 mb-1">Pay for {{ $planLabel }} plan</h1>
                <p class="text-muted mb-0">Amount: <strong>₦{{ number_format($amountNgn) }}</strong></p>
            </div>

            <div class="card card-shadow">
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('central.subscribe.start', $plan) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="shop_address" class="form-label">Shop address</label>
                            <div class="input-group">
                                <input type="text" name="shop_address" id="shop_address" class="form-control"
                                       value="{{ old('shop_address') }}" placeholder="yourshop" required autofocus>
                                <span class="input-group-text">.{{ $tenantDomain }}</span>
                            </div>
                            <div class="form-text">Example: if your shop is easymall.{{ $tenantDomain }}, enter <strong>easymall</strong>.</div>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label">Shop owner email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="{{ old('email') }}" placeholder="owner@email.com" required>
                            <div class="form-text">Must match the email used when the shop was registered.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Continue to Paystack — ₦{{ number_format($amountNgn) }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.whatsapp-support')
</body>
</html>
