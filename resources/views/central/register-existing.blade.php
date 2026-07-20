<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop already exists — {{ config('app.name') }}</title>
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
        }
        .detail-row:last-child { border-bottom: 0; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-4">
                <a href="{{ route('central.home') }}" class="text-decoration-none text-muted">&larr; Back to home</a>
                <h1 class="h3 mt-2">You already have a shop</h1>
            </div>

            <div class="card card-shadow">
                <div class="card-body p-4">
                    <div class="detail-row">
                        <span class="text-muted">Shop name</span>
                        <span class="fw-semibold">{{ $shopName ?: '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="text-muted">Shop address</span>
                        <span class="fw-semibold">{{ $shopAddress }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="text-muted">Status</span>
                        <span class="fw-semibold">
                            @if ($onTrial)
                                Free trial · {{ $trialDaysLeft }} day(s) left
                            @elseif ($status === 'active')
                                Active{{ $planLabel ? ' · '.$planLabel : '' }} · {{ $daysLeft }} day(s) left
                            @elseif ($status === 'expired')
                                Expired — payment required
                            @else
                                {{ ucfirst($status) }}
                            @endif
                        </span>
                    </div>
                    @if ($accessEndsAt)
                        <div class="detail-row">
                            <span class="text-muted">{{ $onTrial ? 'Trial ends' : 'Next expiry' }}</span>
                            <span class="fw-semibold">{{ $accessEndsAt->format('d M Y') }}</span>
                        </div>
                    @endif

                    @if ($needsPayment)
                        <div class="alert alert-warning mt-4 mb-3">
                            Your shop needs a subscription to continue. Choose a plan and pay to unlock access.
                        </div>
                        <a href="{{ $plansUrl }}" class="btn btn-primary w-100">Subscribe — see plans</a>
                    @else
                        <div class="alert alert-success mt-4 mb-3">
                            Your shop is still active. Use your usual shop login when you want to continue.
                        </div>
                        <a href="{{ $plansUrl }}" class="btn btn-outline-primary w-100">View subscription plans</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.whatsapp-support')
</body>
</html>
