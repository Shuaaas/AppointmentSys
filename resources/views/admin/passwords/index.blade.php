@extends('layout.app')

@section('title', 'Reset Passwords')

@section('content')
    <div class="page-header">
        <h2>Reset Passwords</h2>
        <p class="text-muted">Set a new password for any user who has forgotten theirs.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-circle-check" aria-hidden="true"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="table-card">
        <div class="tbl-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->roleLabel() }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <button type="button" class="btn btn-sm btn-primary"
                                        onclick="openResetModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                    <i class="ti ti-key" style="font-size:12px" aria-hidden="true"></i> Reset password
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="overlay" id="reset-overlay">
        <div class="modal" style="max-width:460px">
            <div class="modal-head">
                <span class="modal-title" id="reset-title">Reset password</span>
                <button type="button" class="modal-close" onclick="closeResetModal()" aria-label="Close">&times;</button>
            </div>
            <form id="reset-form" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted" id="reset-subtext" style="margin-top:0"></p>

                    <div class="wz-field">
                        <label>New password <span class="req">*</span></label>
                        <input type="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="wz-field">
                        <label>Confirm new password <span class="req">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
                    <p class="text-muted" style="font-size:12px;margin-bottom:0">Minimum 8 characters.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeResetModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reset password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openResetModal(id, name) {
            const overlay = document.getElementById('reset-overlay');
            const form = document.getElementById('reset-form');
            form.action = '{{ url('admin/passwords') }}/' + id;
            document.getElementById('reset-subtext').textContent = 'Set a new password for ' + name + '.';
            form.reset();
            overlay.classList.add('show');
        }

        function closeResetModal() {
            document.getElementById('reset-overlay').classList.remove('show');
        }

        document.getElementById('reset-overlay').addEventListener('click', function (e) {
            if (e.target === this) closeResetModal();
        });
    </script>
@endsection
