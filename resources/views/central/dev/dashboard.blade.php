<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dev dashboard — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
        .dev-badge {
            background: #0d6efd;
            color: #fff;
            font-weight: 700;
            padding: 0.3rem 0.7rem;
            border-radius: 0.375rem;
            letter-spacing: 0.03em;
        }
        .stat-value { font-size: 1.6rem; font-weight: 700; }
        code.creds { background: #eef2ff; color: #3730a3; padding: 0.1rem 0.4rem; border-radius: 0.25rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
    <div class="container">
        <span class="navbar-brand"><span class="dev-badge">{{ config('app.name') }} DEV</span></span>
        <form method="POST" action="{{ route('dev.logout') }}" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Log out</button>
        </form>
    </div>
</nav>

<div class="container pb-5">
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

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h3 mb-1">All shops</h1>
            <p class="text-muted mb-0">
                Click a shop link to open its login page, then sign in with the dev admin
                (<code class="creds">{{ $devAdminEmail }}</code>) to reach its admin area.
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card card-shadow"><div class="card-body">
                <div class="text-muted small">Total shops</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-shadow"><div class="card-body">
                <div class="text-muted small">On trial</div>
                <div class="stat-value text-info">{{ $stats['trial'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-shadow"><div class="card-body">
                <div class="text-muted small">Active</div>
                <div class="stat-value text-success">{{ $stats['active'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-shadow"><div class="card-body">
                <div class="text-muted small">Expired</div>
                <div class="stat-value text-danger">{{ $stats['expired'] }}</div>
            </div></div>
        </div>
    </div>

    <form method="GET" action="{{ route('dev.dashboard') }}" class="mb-3">
        <div class="input-group" style="max-width: 420px;">
            <input type="text" name="q" class="form-control" placeholder="Search by name, address, email, or phone" value="{{ $search }}">
            <button class="btn btn-primary" type="submit">Search</button>
            @if ($search !== '')
                <a href="{{ route('dev.dashboard') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card card-shadow">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Shop</th>
                        <th>Address</th>
                        <th>Owner email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Plan</th>
                        <th>Next expiry</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shops as $shop)
                        <tr>
                            <td class="fw-semibold">{{ $shop['shop_name'] ?: '—' }}</td>
                            <td><span class="text-muted">{{ $shop['id'] }}</span></td>
                            <td>{{ $shop['owner_email'] ?: '—' }}</td>
                            <td>{{ $shop['owner_phone'] ?: '—' }}</td>
                            <td>
                                @if ($shop['status'] === 'active')
                                    <span class="badge bg-success">Active · {{ $shop['days_left'] }}d left</span>
                                @elseif ($shop['on_trial'])
                                    <span class="badge bg-info text-dark">Trial · {{ $shop['trial_days_left'] }}d</span>
                                @elseif ($shop['status'] === 'expired')
                                    <span class="badge bg-danger">Expired</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($shop['status'] ?? 'unknown') }}</span>
                                @endif
                            </td>
                            <td>{{ $shop['plan'] ?: ($shop['on_trial'] ? 'Trial' : '—') }}</td>
                            <td>{{ nigeria_datetime($shop['access_ends_at'] ?? null, false, true) }}</td>
                            <td>{{ nigeria_datetime($shop['created_at'] ?? null, false) }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                    <button type="button"
                                            class="btn btn-sm btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#activateModal"
                                            data-shop-id="{{ $shop['id'] }}"
                                            data-shop-name="{{ $shop['shop_name'] ?: $shop['id'] }}">
                                        Activate
                                    </button>
                                    <a href="{{ route('dev.shops.history', ['tenantId' => $shop['id']]) }}" class="btn btn-sm btn-outline-secondary">
                                        History
                                    </a>
                                    <a href="{{ $shop['login_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                        Open shop login
                                    </a>
                                    <form method="POST"
                                          action="{{ route('dev.shops.destroy', ['tenantId' => $shop['id']]) }}"
                                          onsubmit="return confirm('Permanently delete this shop and all its data? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No shops found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="activateForm" action="#">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="activateModalLabel">Activate subscription</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Manually activate or extend <strong id="activateShopName">this shop</strong>.
                        Use this when Paystack payment succeeded but the shop was not updated.
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
<script>
(function () {
    var modal = document.getElementById('activateModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button) return;

        var shopId = button.getAttribute('data-shop-id');
        var shopName = button.getAttribute('data-shop-name') || shopId;
        var form = document.getElementById('activateForm');
        var nameEl = document.getElementById('activateShopName');

        if (form) {
            form.action = @json(url('/dev/shops')) + '/' + encodeURIComponent(shopId) + '/activate';
        }
        if (nameEl) nameEl.textContent = shopName;
    });
})();
</script>
</body>
</html>
