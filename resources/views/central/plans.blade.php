<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plans — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
        .app-brand {
            background: #0d6efd;
            color: #fff !important;
            font-weight: 700;
            padding: 0.4rem 0.85rem;
            border-radius: 0.375rem;
        }
        .app-brand:hover { color: #fff !important; opacity: 0.92; }
        .plan-card {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            height: 100%;
        }
        .plan-card.is-featured {
            border-color: #0d6efd;
            position: relative;
        }
        .plan-badge {
            position: absolute;
            top: -0.7rem;
            left: 50%;
            transform: translateX(-50%);
            background: #0d6efd;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            white-space: nowrap;
        }
        .plan-price {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
        }
        .plan-strike {
            text-decoration: line-through;
            color: #94a3b8;
            font-size: 0.95rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.45rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        .detail-row:last-child { border-bottom: 0; }
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

<div class="container py-5">
    <div class="text-center mb-4">
        <a href="{{ route('central.home') }}" class="text-decoration-none text-muted">&larr; Back to home</a>
        <h1 class="h3 mt-2 mb-2">Subscription plans</h1>
        <p class="text-muted mb-0">
            Start with a {{ $trialDays }}-day free trial, then choose a plan that fits your shop.
        </p>
        @if (session('payment_error'))
            <div class="alert alert-danger mt-3 mb-0 text-start">{{ session('payment_error') }}</div>
        @endif
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card plan-card card-shadow">
                <div class="card-body p-4">
                    <h2 class="h5 text-center mb-1">Monthly</h2>
                    <p class="text-muted small text-center mb-3">Flexible — pay every month</p>
                    <div class="text-center mb-4">
                        <div class="plan-price">₦{{ number_format($monthlyPriceNgn) }}</div>
                        <div class="text-muted">per month</div>
                    </div>
                    <div class="mb-4">
                        <div class="detail-row">
                            <span class="text-muted">Billing</span>
                            <span class="fw-semibold">Every month</span>
                        </div>
                        <div class="detail-row">
                            <span class="text-muted">You pay</span>
                            <span class="fw-semibold">₦{{ number_format($monthlyPriceNgn) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('central.subscribe', 'monthly') }}" class="btn btn-primary w-100 mb-2">Subscribe with Paystack</a>
                    <a href="{{ route('central.register') }}" class="btn btn-outline-primary w-100">Start free trial</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card plan-card is-featured card-shadow">
                <span class="plan-badge">Save {{ $yearlyDiscountPercent }}%</span>
                <div class="card-body p-4">
                    <h2 class="h5 text-center mb-1">Yearly</h2>
                    <p class="text-muted small text-center mb-3">Best value — billed once a year</p>
                    <div class="text-center mb-4">
                        <div class="plan-strike">₦{{ number_format($yearlyFullPriceNgn) }}</div>
                        <div class="plan-price">₦{{ number_format($yearlyPriceNgn) }}</div>
                        <div class="text-muted">per year</div>
                    </div>
                    <div class="mb-4">
                        <div class="detail-row">
                            <span class="text-muted">Full year (12 × monthly)</span>
                            <span class="fw-semibold">₦{{ number_format($yearlyFullPriceNgn) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="text-muted">Discount</span>
                            <span class="fw-semibold text-success">{{ $yearlyDiscountPercent }}% (−₦{{ number_format($yearlySavingsNgn) }})</span>
                        </div>
                        <div class="detail-row">
                            <span class="text-muted">You pay</span>
                            <span class="fw-semibold">₦{{ number_format($yearlyPriceNgn) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('central.subscribe', 'yearly') }}" class="btn btn-primary w-100 mb-2">Subscribe with Paystack</a>
                    <a href="{{ route('central.register') }}" class="btn btn-outline-primary w-100">Start free trial</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card plan-card card-shadow">
                <div class="card-body p-4">
                    <h2 class="h5 text-center mb-1">One-off</h2>
                    <p class="text-muted small text-center mb-3">A custom arrangement for your business</p>
                    <div class="text-center mb-4">
                        <div class="plan-price">Negotiable</div>
                        <div class="text-muted">contact us for pricing</div>
                    </div>
                    <div class="mb-4">
                        <div class="detail-row">
                            <span class="text-muted">Billing</span>
                            <span class="fw-semibold">One-off payment</span>
                        </div>
                        <div class="detail-row">
                            <span class="text-muted">Price</span>
                            <span class="fw-semibold">Negotiable</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#oneOffPlanModal">
                        Subscribe to one-off plan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $supportEmail = config('dev.admin.email');
    $supportWhatsApp = config('dev.whatsapp_support');
    $oneOffMessage = rawurlencode('Hello, I am interested in the negotiable one-off Sales Flow plan.');
@endphp
<div class="modal fade" id="oneOffPlanModal" tabindex="-1" aria-labelledby="oneOffPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="oneOffPlanModalLabel">Discuss the one-off plan</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">The one-off price is negotiable. Chat with our support team or send us an email to discuss your requirements and agree on a price.</p>
            </div>
            <div class="modal-footer">
                <a href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('Sales Flow one-off plan') }}" class="btn btn-outline-primary">
                    Email {{ $supportEmail }}
                </a>
                <a href="https://wa.me/{{ $supportWhatsApp }}?text={{ $oneOffMessage }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                    Chat on WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>
@include('partials.whatsapp-support')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
