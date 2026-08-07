<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in &mdash; HR Recruitment System</title>
    <link rel="icon" href="{{ asset('deped_logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    {{-- Minimal stub: only the rules that embed Blade asset() URLs --}}
    <style>
        body { background: url('{{ asset('deped_bg.jpg') }}') center center / cover no-repeat fixed; position: relative; }
        body::before { content:''; position:fixed; top:50%; left:50%; width:560px; height:560px; background:url('{{ asset('deped_logo.png') }}') center/contain no-repeat; transform:translate(-50%,-50%); opacity:.05; pointer-events:none; z-index:0; }
        .auth-card-header::after { content:''; position:absolute; right:-30px; top:-30px; width:140px; height:140px; background:url('{{ asset('logo_DepED.png') }}') center/contain no-repeat; opacity:.10; pointer-events:none; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-header">
        <a href="/" class="auth-header-brand">
            <img src="{{ asset('deped_logo.png') }}" alt="DepEd" class="auth-header-logo">
            <div class="auth-header-text">
                <div class="org">Schools Division Office of Cavite Province</div>
                <div class="sys">Appointment System</div>
            </div>
        </a>
        <span class="auth-header-datetime" id="authHeaderDateTime"></span>
        <span class="auth-header-spacer"></span>
    </div>
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body">
                <div class="auth-card-header">
                    <img src="{{ asset('deped_logo.png') }}" alt="DepEd" class="auth-card-header-logo">
                    <div class="auth-card-header-text">
                        <div class="eyebrow">Department of Education</div>
                        <div class="title">Schools Division Office<br>of Cavite Province</div>
                    </div>
                </div>
                <div class="auth-card-body">
                    @if (session('status'))
                        <div class="alert alert-success py-2">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            @foreach ($errors->all() as $error)
                                {{ $error }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn-hr-primary w-100">Sign In</button>
                    </form>

                </div>
                <div class="auth-card-footer">
                    &copy; {{ date('Y') }} DepEd &mdash; Schools Division Office of Cavite Province
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/auth-clock.js') }}"></script>
    @stack('scripts')
</body>
</html>
