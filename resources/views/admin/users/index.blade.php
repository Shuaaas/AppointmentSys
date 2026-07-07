@extends('layout.app')

@section('title', 'User Management')

@section('content')
    <div class="page-header">
        <h2>User Management</h2>
        <p class="text-muted">View and manage admin users, roles, and activation status.</p>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Requested</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->requested_role ? ucfirst($user->requested_role) : '-' }}</td>
                        <td style="vertical-align:middle;">
                            <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                                <span style="display:inline-block; min-width:70px;">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>

                                @if ($user->is_active)
                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="deactivate-user-form" style="display:inline-block; margin-left:8px;" data-user-name="{{ $user->name }}" data-action="deactivate">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="btn btn-sm btn-danger deactivate-trigger">Deactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="deactivate-user-form" style="display:inline-block; margin-left:8px;" data-user-name="{{ $user->name }}" data-action="reactivate">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="btn btn-sm btn-success deactivate-trigger">Reactivate</button>
                                    </form>
                                @endif
                            </div>
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

    <div id="deactivate-confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; padding:20px;">
        <div style="width:min(420px, 100%); background:#fff; border-radius:12px; box-shadow:0 16px 40px rgba(0,0,0,0.2); padding:24px; text-align:center;">
            <h3 id="deactivate-confirm-title" style="margin:0 0 10px; font-size:20px;">Deactivate account?</h3>
            <p id="deactivate-confirm-text" style="margin:0 0 20px; color:#475569;">Are you sure you want to deactivate this account?</p>
            <div style="display:flex; justify-content:center; gap:10px;">
                <button type="button" class="btn btn-secondary" id="deactivate-cancel-btn">Cancel</button>
                <button type="button" class="btn btn-danger" id="deactivate-confirm-btn">Deactivate</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('deactivate-confirm-modal');
            const title = document.getElementById('deactivate-confirm-title');
            const text = document.getElementById('deactivate-confirm-text');
            const cancelBtn = document.getElementById('deactivate-cancel-btn');
            const confirmBtn = document.getElementById('deactivate-confirm-btn');
            let pendingForm = null;

            document.querySelectorAll('.deactivate-user-form').forEach(function (form) {
                form.querySelector('.deactivate-trigger').addEventListener('click', function () {
                    pendingForm = form;
                    const userName = form.dataset.userName || 'this account';
                    const action = form.dataset.action === 'reactivate' ? 'reactivate' : 'deactivate';

                    title.textContent = action === 'reactivate' ? 'Reactivate account?' : 'Deactivate account?';
                    text.textContent = action === 'reactivate'
                        ? 'Are you sure you want to reactivate ' + userName + '?'
                        : 'Are you sure you want to deactivate ' + userName + '?';
                    confirmBtn.textContent = action === 'reactivate' ? 'Reactivate' : 'Deactivate';
                    confirmBtn.className = action === 'reactivate' ? 'btn btn-success' : 'btn btn-danger';
                    modal.style.display = 'flex';
                });
            });

            function closeModal() {
                modal.style.display = 'none';
                pendingForm = null;
            }

            cancelBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            confirmBtn.addEventListener('click', function () {
                if (pendingForm) {
                    pendingForm.submit();
                }
            });
        });
    </script>

    {{ $users->links() }}
@endsection
