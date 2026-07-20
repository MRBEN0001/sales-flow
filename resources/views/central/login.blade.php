<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in to your shop — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shadow { box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 0; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-4">
                <a href="{{ route('central.home') }}" class="text-decoration-none text-muted">&larr; Back to home</a>
                <h1 class="h3 mt-2">Log in to your shop</h1>
                <p class="text-muted">Enter the shop address you chose when you registered.</p>
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

                    <form method="POST" action="{{ route('central.login.redirect') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Shop address</label>
                            <div class="input-group">
                                <input type="text" name="subdomain" class="form-control" value="{{ old('subdomain') }}" placeholder="e.g. easy-mall" pattern="[a-z0-9]([a-z0-9-]*[a-z0-9])?" required autofocus>
                                <span class="input-group-text">.{{ $tenantDomain }}</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Continue to login</button>
                    </form>

                    <p class="text-muted small text-center mt-3 mb-0">
                        Don't have a shop yet? <a href="{{ route('central.register') }}">Create one</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.whatsapp-support')
</body>
</html>
