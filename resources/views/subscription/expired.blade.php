<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscribe — {{ $shopName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
        .plan-card {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            height: 100%;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .plan-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12);
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
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-4">
                <h1 class="h3 mb-2">Choose a plan for {{ $shopName }}</h1>
                <p class="text-muted mb-0">Your free trial has ended. Subscribe to keep selling and managing stock.</p>
            </div>

            <div class="row g-4 mb-4">
                @if ($errors->any())
                    <div class="col-12">
                        <div class="alert alert-danger mb-0">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="card plan-card card-shadow">
                        <div class="card-body p-4 text-center">
                            <h2 class="h5 mb-1">Monthly</h2>
                            <p class="text-muted small mb-3">Flexible — pay every month</p>
                            <div class="plan-price mb-1">₦{{ number_format($monthlyPriceNgn) }}</div>
                            <div class="text-muted mb-4">per month</div>
                            <ul class="list-unstyled text-start small text-muted mb-4">
                                <li class="mb-2">✓ Full shop access</li>
                                <li class="mb-2">✓ Sales, stock &amp; reports</li>
                                <li class="mb-2">✓ Cancel anytime</li>
                            </ul>
                            <form method="POST" action="{{ route('subscription.checkout') }}">
                                @csrf
                                <input type="hidden" name="plan" value="monthly">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    Pay ₦{{ number_format($monthlyPriceNgn) }}/month
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card plan-card is-featured card-shadow">
                        <span class="plan-badge">Save {{ $yearlyDiscountPercent }}%</span>
                        <div class="card-body p-4 text-center">
                            <h2 class="h5 mb-1">Yearly</h2>
                            <p class="text-muted small mb-3">Best value — billed once a year</p>
                            <div class="plan-strike mb-0">₦{{ number_format($yearlyFullPriceNgn) }}</div>
                            <div class="plan-price mb-1">₦{{ number_format($yearlyPriceNgn) }}</div>
                            <div class="text-muted mb-1">per year</div>
                            <div class="text-success small fw-semibold mb-4">
                                You save ₦{{ number_format($yearlySavingsNgn) }} vs monthly
                            </div>
                            <ul class="list-unstyled text-start small text-muted mb-4">
                                <li class="mb-2">✓ Everything in Monthly</li>
                                <li class="mb-2">✓ {{ $yearlyDiscountPercent }}% off full yearly price</li>
                                <li class="mb-2">✓ One payment for 12 months</li>
                            </ul>
                            <form method="POST" action="{{ route('subscription.checkout') }}">
                                @csrf
                                <input type="hidden" name="plan" value="yearly">
                                <button type="submit" class="btn btn-primary w-100">
                                    Pay ₦{{ number_format($yearlyPriceNgn) }}/year
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card plan-card card-shadow">
                        <div class="card-body p-4 text-center">
                            <h2 class="h5 mb-1">One-off</h2>
                            <p class="text-muted small mb-3">A custom arrangement for your business</p>
                            <div class="plan-price mb-1">Negotiable</div>
                            <div class="text-muted mb-4">contact us for pricing</div>
                            <ul class="list-unstyled text-start small text-muted mb-4">
                                <li class="mb-2">✓ Full shop access</li>
                                <li class="mb-2">✓ One-off payment arrangement</li>
                                <li class="mb-2">✓ Price agreed with support</li>
                            </ul>
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#oneOffPlanModal">
                                Subscribe to one-off plan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p class="text-muted small mb-3">
                    Need help? Use WhatsApp chat support below.
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Log out</button>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $supportEmail = config('dev.admin.email');
    $supportWhatsApp = config('dev.whatsapp_support');
    $oneOffMessage = rawurlencode('Hello, I am interested in the negotiable one-off Sales Flow plan for '.$shopName.'.');
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
