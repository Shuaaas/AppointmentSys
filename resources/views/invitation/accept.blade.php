@extends('layout.app')

@section('title', 'Confirm Account')

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

                @if (session('error'))
                    <div class="alert alert-danger py-2">{{ session('error') }}</div>
                @endif

                @if ($invitation && $invitation->isValid())
                    <p style="margin-top:0">Confirming account for <strong>{{ $invitation->name }}</strong> ({{ $invitation->email }}) as <strong>{{ \App\Enums\Role::tryFrom($invitation->role)?->label() ?? $invitation->role }}</strong>.</p>
                    <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                        @csrf
                        <button type="submit" class="btn-hr-primary">Confirm & Create Account</button>
                    </form>
                @else
                    <p class="text-danger">This invitation is invalid or has expired. Please contact your administrator.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
