@extends('layout.app')

@section('title', 'Trash')

@section('content')
<div class="action-bar">
    <div class="action-bar-right">
        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to Appointments
        </a>
    </div>
</div>

<div class="table-card" data-bulk-url="{{ route('appointments.bulkDestroy') }}">
    <div class="tbl-wrap">
        <table>
            <colgroup>
                <col style="width:38px"><col style="width:240px"><col style="width:170px"><col style="width:180px"><col style="width:140px">
            </colgroup>
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-trash" onchange="toggleSelectAll(this)"></th>
                    <th>Full name</th>
                    <th>School / district</th>
                    <th>Deleted at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($trashed as $i => $item)
                    <tr>
                        <td><input type="checkbox" class="select-row" value="{{ $item->id }}"></td>
                        <td>
                            <div class="name-text">{{ $item->full_name }}</div>
                            <div class="small-text">{{ $item->position_title }}</div>
                        </td>
                        <td>{{ $item->school_district ?: '—' }}</td>
                        <td style="font-size:12px;color:var(--text-muted);">{{ optional($item->deleted_at)->format('F j, Y g:i A') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-success" data-restore-url="{{ route('appointments.restore', $item->id) }}" onclick="restoreSingle(this, '{{ addslashes($item->full_name) }}')">Restore</button>
                            <button type="button" class="btn btn-sm btn-danger" data-force-url="{{ route('appointments.forceDelete', $item->id) }}" onclick="deleteSingle(this, '{{ addslashes($item->full_name) }}')" style="margin-left:8px">Delete permanently</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"><p class="empty-note">No trashed appointments found.</p></td>
                    </tr>
                @endforelse
                @if ($trashed->count() > 0)
                    <tr class="bulk-footer-row">
                        <td colspan="5" style="text-align:right;padding-top:10px;border-bottom:0">
                            <button type="button" class="btn btn-danger btn-sm" id="btn-bulk-delete">
                                <i class="ti ti-trash" aria-hidden="true"></i> Delete Selected Permanently
                            </button>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleSelectAll(source) {
    document.querySelectorAll('.select-row').forEach(cb => cb.checked = source.checked);
}

function buildForm(url) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(form);
    return form;
}

function restoreSingle(btn, name) {
    if (!confirm('Restore ' + name + '?')) {
        return;
    }
    buildForm(btn.dataset.restoreUrl).submit();
}

function deleteSingle(btn, name) {
    if (!confirm('Permanently delete ' + name + '? This cannot be undone.')) {
        return;
    }
    buildForm(btn.dataset.forceUrl).submit();
}

function bulkDelete() {
    const selected = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one record to delete.');
        return;
    }
    if (!confirm('Permanently delete the selected ' + selected.length + ' record(s)? This cannot be undone.')) {
        return;
    }
    const bulkUrl = document.querySelector('.table-card')?.dataset?.bulkUrl;
    if (!bulkUrl) {
        alert('Bulk delete URL is not configured.');
        return;
    }
    const form = buildForm(bulkUrl);
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });
    form.submit();
}

(function() {
    const btn = document.getElementById('btn-bulk-delete');
    if (btn) {
        btn.addEventListener('click', bulkDelete);
    }
})();
</script>
@endsection
