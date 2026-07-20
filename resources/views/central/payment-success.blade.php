<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment successful — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.55rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }
        .detail-row:last-child { border-bottom: 0; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card card-shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <div class="text-success mb-2" style="font-size: 2.5rem;">✓</div>
                        <h1 class="h4 mb-1">Payment successful</h1>
                        <p class="text-muted mb-0">Your shop subscription has been updated.</p>
                    </div>

                    <div class="detail-row">
                        <span class="text-muted">Shop</span>
                        <span class="fw-semibold">{{ $shopName }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="text-muted">Plan</span>
                        <span class="fw-semibold">{{ $planLabel }}</span>
                    </div>
                    @if ($amount)
                        <div class="detail-row">
                            <span class="text-muted">Amount paid</span>
                            <span class="fw-semibold">₦{{ number_format($amount) }}</span>
                        </div>
                    @endif
                    <div class="detail-row">
                        <span class="text-muted">Status</span>
                        <span class="fw-semibold text-success">{{ $status }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="text-muted">Next expiry</span>
                        <span class="fw-semibold">{{ $endsAt ?: '—' }}</span>
                    </div>
                    @if ($reference)
                        <div class="detail-row">
                            <span class="text-muted">Reference</span>
                            <span class="small">{{ $reference }}</span>
                        </div>
                    @endif

                    <a href="{{ $loginUrl }}" class="btn btn-primary w-100 mt-4">Go to shop login</a>
                    <a href="{{ route('central.home') }}" class="btn btn-link w-100 mt-1">Back to home</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
