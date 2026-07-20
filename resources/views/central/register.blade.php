<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create your shop — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
        #register-panel[hidden], #checking-panel[hidden] { display: none !important; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div id="checking-panel" class="card card-shadow">
                <div class="card-body p-5 text-center">
                    <h1 class="h4 mb-2">Checking this device…</h1>
                    <p class="text-muted mb-0">Please wait a moment.</p>
                </div>
            </div>

            <div id="register-panel" hidden>
                <div class="text-center mb-4">
                    <a href="{{ route('central.home') }}" class="text-decoration-none text-muted">&larr; Back</a>
                    <h1 class="h3 mt-2">Create your shop</h1>
                    <p class="text-muted">
                        {{ $trialDays }} days free trial for new devices, then ₦{{ number_format($monthlyPriceNgn) }}/month.
                        <a href="{{ route('central.plans') }}">See plans</a>
                    </p>
                </div>

                <div class="card card-shadow">
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('central.register.store') }}" id="register-form">
                            @csrf
                            <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

                            <div class="mb-3">
                                <label class="form-label">Shop name</label>
                                <input type="text" name="shop_name" id="shop_name" class="form-control text-uppercase" value="{{ strtoupper(old('shop_name', '')) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Shop address (subdomain)</label>
                                <div class="input-group">
                                    <input type="text" name="subdomain" id="subdomain" class="form-control" value="{{ strtolower(preg_replace('/[^a-z0-9]/', '', old('subdomain', old('shop_name', '')))) }}" readonly required>
                                    <span class="input-group-text">.{{ $tenantDomain }}</span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label class="form-label">Admin name</label>
                                <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Admin email</label>
                                <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
                                <div class="form-text">Use an active email address — we will send your shop details here. Each email can only be used for one shop.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm admin email</label>
                                <input type="email" name="admin_email_confirmation" class="form-control" value="{{ old('admin_email_confirmation') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Active phone number</label>
                                <input type="tel" name="owner_phone" class="form-control" value="{{ old('owner_phone') }}" placeholder="e.g. 08136323444" required>
                                <div class="form-text">Enter a reachable Nigerian phone number. Each phone can only be used for one shop.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirm password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Create shop</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.whatsapp-support')
@include('partials.device-fingerprint')
<script>
(function () {
    var checkUrl = @json($checkDeviceUrl);
    var skipCheck = @json($errors->any());

    function toShopAddress(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '')
            .slice(0, 40);
    }

    function showRegisterForm(fingerprint) {
        var checking = document.getElementById('checking-panel');
        var panel = document.getElementById('register-panel');
        if (checking) checking.hidden = true;
        if (panel) panel.hidden = false;

        var field = document.getElementById('device_fingerprint');
        if (field) field.value = fingerprint;

        var shopName = document.getElementById('shop_name');
        var subdomain = document.getElementById('subdomain');

        function syncAddress() {
            if (!shopName || !subdomain) return;
            shopName.value = String(shopName.value || '').toUpperCase();
            subdomain.value = toShopAddress(shopName.value);
        }

        if (shopName) {
            shopName.addEventListener('input', syncAddress);
            syncAddress();
        }
    }

    var fingerprint = window.SalesFlowDevice
        ? window.SalesFlowDevice.buildFingerprint()
        : '';

    if (skipCheck) {
        showRegisterForm(fingerprint);
        return;
    }

    var token = document.querySelector('meta[name="csrf-token"]');
    fetch(checkUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ device_fingerprint: fingerprint })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data && data.has_shop && data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            showRegisterForm(fingerprint);
        })
        .catch(function () {
            showRegisterForm(fingerprint);
        });
})();
</script>
</body>
</html>
