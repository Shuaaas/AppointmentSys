@extends('layout.app')

@section('title', 'Add User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('body_class', 'add-user-page-host')

@section('content')

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