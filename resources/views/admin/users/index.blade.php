@extends('layout.app')

@section('title', 'User Management')

@section('content')
    <form class="user-toolbar" method="GET" action="{{ route('admin.users.index') }}">
        <div class="search-wrap">
            <i class="ti ti-search" aria-hidden="true"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search by name or email…" class="js-autosubmit">
        </div>
        <select name="role" class="filter-select" class="js-autosubmit">
            <option value="">All roles</option>
            @foreach (App\Enums\Role::cases() as $r)
                <option value="{{ $r->value }}" {{ $role === $r->value ? 'selected' : '' }}>{{ $r->label() }}</option>
            @endforeach
        </select>
        <select name="status" class="filter-select" class="js-autosubmit">
            <option value="">All statuses</option>
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </form>

    <div class="table-card">
        <div class="user-tabs">
            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab' => 'all'])) }}" class="user-tab {{ $tab === 'all' ? 'active' : '' }}">All users ({{ $allCount }})</a>
            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab' => 'active'])) }}" class="user-tab {{ $tab === 'active' ? 'active' : '' }}">Active ({{ $activeCount }})</a>
            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab' => 'inactive'])) }}" class="user-tab {{ $tab === 'inactive' ? 'active' : '' }}">Inactive ({{ $inactiveCount }})</a>
        </div>

        <div class="tbl-wrap">
            <table class="table user-table">
                <colgroup>
                    <col class="col-name">
                    <col class="col-position">
                    <col class="col-status">
                    <col class="col-action">
                </colgroup>
                <thead>
                    <tr>
                        <th class="th-name">Name</th>
                        <th class="th-center">Position</th>
                        <th class="th-center">Status</th>
                        <th class="th-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $parts = array_filter(explode(' ', $user->name));
                            $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[count($parts) - 1] ?? '', 0, 1));
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
                            <td class="td-center">
                                <input type="text"
                                       class="position-input"
                                       data-user-id="{{ $user->id }}"
                                       placeholder="e.g. Administrative Assistant III"
                                       value="{{ $user->position_title }}">
                            </td>
                            <td class="td-center">
                                <span class="status-pill {{ $user->is_active ? 'active' : 'inactive' }}">
                                    <span class="status-dot"></span>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="td-center">
                                @if ($user->is_active && $user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="deactivate-user-form d-inline-block" data-user-name="{{ $user->name }}" data-action="deactivate">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="action-pill deactivate deactivate-trigger">Deactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="deactivate-user-form d-inline-block" data-user-name="{{ $user->name }}" data-action="reactivate">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="action-pill reactivate deactivate-trigger">Reactivate</button>
                                    </form>
                                @endif
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

    <div id="deactivate-confirm-modal" class="deactivate-modal-overlay">
        <div class="deactivate-modal-box">
            <h3 id="deactivate-confirm-title" class="deactivate-modal-title">Deactivate account?</h3>
            <p id="deactivate-confirm-text" class="deactivate-modal-text">Are you sure you want to deactivate this account?</p>
            <div class="deactivate-modal-actions">
                <button type="button" class="btn btn-secondary" id="deactivate-cancel-btn">Cancel</button>
                <button type="button" class="btn btn-danger" id="deactivate-confirm-btn">Deactivate</button>
            </div>
        </div>
    </div>

@push('scripts')
<script src="{{ asset('js/user-management.js') }}"></script>
@endpush
@endsection
