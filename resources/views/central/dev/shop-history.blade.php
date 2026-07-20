<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription history — {{ $tenant->shop_name ?: $tenant->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
        .summary-label { color: #64748b; font-size: 0.8rem; }
        .summary-value { color: #0f172a; font-weight: 700; }
        .reference { max-width: 230px; word-break: break-all; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="{{ route('dev.dashboard') }}">{{ config('app.name') }} Admin</a>
        <div class="d-flex gap-2">
            <a href="{{ route('dev.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to shops</a>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#activateModal">
                Activate subscription
            </button>
            <a href="{{ $loginUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">Open shop login</a>
        </div>
    </div>
</nav>

<div class="container py-4 py-lg-5">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $tenant->shop_name ?: $tenant->id }}</h1>
        <p class="text-muted mb-0">{{ $tenant->id }}.{{ config('app.tenant_domain') }} · Subscription history</p>
    </div>

    <div class="card card-shadow mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="summary-label">Current status</div>
                    <div class="summary-value text-capitalize">
                        @if ($tenant->subscription_status === 'active')
                            Active
                        @elseif ($tenant->isOnTrial())
                            Trial
                        @else
                            {{ $tenant->subscription_status }}
                        @endif
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-label">Current plan</div>
                    <div class="summary-value">
                        @if ($tenant->subscription_status === 'active' && $tenant->planLabel())
                            {{ $tenant->planLabel() }}
                        @elseif ($tenant->isOnTrial())
                            Free trial
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-label">Next expiry</div>
                    <div class="summary-value">{{ nigeria_datetime($tenant->accessEndsAt(), false) }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-label">Owner</div>
                    <div class="summary-value">{{ $tenant->owner_email ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-shadow">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Timeline</h2>
            <div class="small text-muted">Latest payment appears first · times shown in Nigeria (WAT), 12-hour.</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date &amp; time</th>
                        <th>Event / plan</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Access period</th>
                        <th>Channel</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $event)
                        <tr>
                            <td class="text-nowrap">
                                {{ nigeria_datetime($event['timestamp'] ?? null) }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $event['plan'] }}</div>
                                <div class="small text-muted">
                                    @if ($event['type'] === 'trial')
                                        Shop created / trial started
                                    @elseif (($event['channel'] ?? null) === 'manual')
                                        Manual activation (admin)
                                    @else
                                        Paystack subscription payment
                                    @endif
                                </div>
                            </td>
                            <td class="text-nowrap">
                                {{ $event['amount'] > 0 ? '₦'.number_format($event['amount']) : 'Free' }}
                            </td>
                            <td>
                                @if ($event['status'] === 'success')
                                    <span class="badge bg-success">Success</span>
                                @elseif ($event['status'] === 'active')
                                    <span class="badge bg-info text-dark">Active</span>
                                @elseif ($event['status'] === 'converted')
                                    <span class="badge bg-secondary">Converted to paid</span>
                                @elseif ($event['status'] === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif ($event['status'] === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @elseif ($event['status'] === 'ended')
                                    <span class="badge bg-secondary">Ended</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($event['status']) }}</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if ($event['period_starts_at'] || $event['period_ends_at'])
                                    <div>{{ nigeria_datetime($event['period_starts_at'] ?? null, false, true) }}</div>
                                    <div class="small text-muted">to {{ nigeria_datetime($event['period_ends_at'] ?? null, false, true) }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $event['channel'] ? ucfirst($event['channel']) : '—' }}</td>
                            <td class="reference small">{{ $event['reference'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('dev.shops.activate', ['tenantId' => $tenant->id]) }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="activateModalLabel">Activate subscription</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Manually activate or extend <strong>{{ $tenant->shop_name ?: $tenant->id }}</strong>.
                        If the shop is already active, the new period is added from the current expiry date.
                    </p>
                    <div class="mb-3">
                        <label for="activate_plan" class="form-label">Plan</label>
                        <select name="plan" id="activate_plan" class="form-select" required>
                            <option value="monthly">Monthly — ₦{{ number_format($monthlyPriceNgn) }} ( +1 month )</option>
                            <option value="yearly">Yearly — ₦{{ number_format($yearlyPriceNgn) }} ( +12 months )</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="activate_note" class="form-label">Note (optional)</label>
                        <textarea name="note" id="activate_note" class="form-control" rows="2"
                                  placeholder="e.g. Customer paid via transfer / Paystack confirmed offline"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Activate this subscription for the selected plan?');">
                        Activate now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
