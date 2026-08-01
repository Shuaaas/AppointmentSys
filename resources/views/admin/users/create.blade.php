@extends('layout.app')

@section('title', 'Add User')

@section('content')
    <style>
        /* Fill the content region (cancel the layout's 28px padding) so the
           login-style background covers the whole area below the header. */
        .content { padding: 0 !important; }

        /* Hide the page bar ("Add User" title + white background) on this page. */
        .hr-pagebar { display: none !important; }

        .add-user-page {
            position: relative;
            min-height: calc(100vh - 56px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            background: url('{{ asset('deped_bg.jpg') }}') center center / cover no-repeat;
            overflow: hidden;
        }
        .add-user-page::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 48, 135, 0.72);
            z-index: 0;
            pointer-events: none;
        }
        .add-user-page::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 560px; height: 560px;
            background: url('{{ asset('deped_logo.png') }}') center / contain no-repeat;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Login-style card (slightly wider) ── */
        .auth-card {
            width: 100%;
            max-width: 520px;
            border: none;
            border-radius: 14px;
            box-shadow: 0 12px 48px rgba(0,0,0,.3);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .auth-card .card-body { padding: 0 !important; }

        .auth-card-header {
            background: linear-gradient(120deg, #003087 0%, #0a1a33 100%);
            border-bottom: 3px solid #ffd700;
            padding: 24px 28px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            overflow: hidden;
        }
        .auth-card-header::after {
            content: '';
            position: absolute;
            right: -30px; top: -30px;
            width: 140px; height: 140px;
            background: url('{{ asset('logo_DepED.png') }}') center / contain no-repeat;
            opacity: 0.10;
            pointer-events: none;
        }
        .auth-card-header-logo {
            height: 50px; width: 50px;
            background: #fff;
            border-radius: 50%;
            padding: 3px;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
            flex-shrink: 0;
            position: relative; z-index: 1;
        }
        .auth-card-header-text { position: relative; z-index: 1; }
        .auth-card-header-text .eyebrow {
            font-size: .68rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
            color: #ffd700; margin-bottom: 3px;
        }
        .auth-card-header-text .title {
            font-size: 1.05rem; font-weight: 800;
            color: #fff; line-height: 1.2;
        }

        .auth-card-body {
            padding: 28px 28px 24px;
            background: #fff;
        }

        .au-field { margin-bottom: 16px; }
        .au-field label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: #1a2840;
            margin-bottom: 5px;
        }
        .au-field input, .au-field select {
            width: 100%;
            border: 1px solid #c5d0e6;
            font-size: .92rem;
            border-radius: 8px;
            padding: 10px 14px;
            color: #1a2840;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .au-field input:focus, .au-field select:focus {
            border-color: #0047b3;
            box-shadow: 0 0 0 3px rgba(0,71,179,.12);
        }
        .au-field select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 38px;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'><path d='M2 4l4 4 4-4' fill='none' stroke='%235a6880' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/></svg>");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 12px;
        }

        /* Two-column row for password fields */
        .au-row {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        .au-row .au-field { flex: 1; margin-bottom: 0; }

        .btn-hr-primary {
            display: block;
            width: 100%;
            background: #003087;
            color: #fff;
            font-weight: 700;
            font-size: .92rem;
            padding: 11px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-hr-primary:hover { background: #0a1a33; color: #fff; }

        .au-back {
            text-align: center;
            margin-top: 16px;
            font-size: .85rem;
            color: #5a6880;
        }
        .au-back a { color: #003087; font-weight: 600; text-decoration: none; }
    </style>

    <div class="add-user-page">
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
                    @if (session('success'))
                        <div class="alert alert-success py-2">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            @foreach ($errors->all() as $error)
                                {{ $error }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.add') }}">
                        @csrf

                        <div class="au-field">
                            <label for="name">Full name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="au-field">
                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="au-field">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role…</option>
                                <option value="hr" {{ old('role') === 'hr' ? 'selected' : '' }}>HR Officer</option>
                            </select>
                            @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="au-row">
                            <div class="au-field">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required autocomplete="new-password">
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="au-field">
                                <label for="password_confirmation">Confirm password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="btn-hr-primary">Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
