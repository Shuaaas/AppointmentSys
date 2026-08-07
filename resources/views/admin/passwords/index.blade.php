@extends('layout.app')

@section('title', 'Reset Passwords')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-circle-check" aria-hidden="true"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form class="user-toolbar" method="GET" action="{{ route('admin.passwords.index') }}">
        <div class="search-wrap">
            <i class="ti ti-search" aria-hidden="true"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search by name or email…" class="js-autosubmit">
        </div>
        <select name="role" class="filter-select js-autosubmit">
            <option value="">All roles</option>
            @foreach (App\Enums\Role::cases() as $r)
                <option value="{{ $r->value }}" {{ $role === $r->value ? 'selected' : '' }}>{{ $r->label() }}</option>
            @endforeach
        </select>
    </form>

    <div class="table-card">
        <div class="tbl-wrap">
            <table class="table user-table">
                <colgroup>
                    <col class="col-name">
                    <col class="col-role">
                    <col class="col-status">
                    <col class="col-action">
                </colgroup>
                <thead>
                    <tr>
                        <th class="th-name">Name</th>
                        <th class="th-center">Role</th>
                        <th class="th-center">Status</th>
                        <th class="th-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $parts = array_filter(explode(' ', $user->name));
                            $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[count($parts) - 1] ?? '', 0, 1));
                            $roleClass = 'role-' . $user->role;
                        @endphp
                        <tr>
                            <td class="td-name">
                                <div class="user-cell">
                                    <div class="user-avatar">{{ $initials }}</div>
                                    <div>
                                        <div class="user-name">{{ $user->name }}</div>
                                        <div class="user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="td-center"><span class="badge {{ $roleClass }}">{{ $user->roleLabel() }}</span></td>
                            <td class="td-center">
                                <span class="status-pill {{ $user->is_active ? 'active' : 'inactive' }}">
                                    <span class="status-dot"></span>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="td-center">
                                <button type="button" class="btn btn-sm btn-blue"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ addslashes($user->name) }}">
                                    <i class="ti ti-key icon-sm" aria-hidden="true"></i> Reset password
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="user-footer">
            <span>
                @if ($users->total() > 0)
                    Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} user{{ $users->total() !== 1 ? 's' : '' }}
                @else
                    No users found.
                @endif
            </span>
            <div class="user-pagination">
                @if (!$users->onFirstPage())
                    <a href="{{ $users->previousPageUrl() }}">&laquo; Prev</a>
                @else
                    <span class="disabled">&laquo; Prev</span>
                @endif

                @php
                    $current = $users->currentPage();
                    $last = $users->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $current)
                        <span aria-current="page">{{ $i }}</span>
                    @else
                        <a href="{{ $users->url($i) }}">{{ $i }}</a>
                    @endif
                @endfor

                @if (!$users->onLastPage())
                    <a href="{{ $users->nextPageUrl() }}">Next &raquo;</a>
                @else
                    <span class="disabled">Next &raquo;</span>
                @endif
            </div>
        </div>
    </div>

    <div class="overlay" id="reset-overlay" data-base-url="{{ url('admin/passwords') }}">
        <div class="modal modal--narrow">
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
                    <button type="button" id="reset-cancel-btn" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-blue">Reset password</button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script src="{{ asset('js/reset-password.js') }}"></script>
@endpush
@endsection
