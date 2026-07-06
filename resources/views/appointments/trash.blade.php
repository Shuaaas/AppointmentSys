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

<div class="table-card">
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full name</th>
                    <th>School / district</th>
                    <th>Deleted at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($trashed as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->full_name }}</td>
                        <td>{{ $item->school_district }}</td>
                        <td>{{ optional($item->deleted_at)->format('F j, Y g:i A') }}</td>
                        <td>
                            <form method="POST" action="{{ route('appointments.restore', $item->id) }}" style="display:inline-block">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Restore</button>
                            </form>
                            <form method="POST" action="{{ route('appointments.forceDelete', $item->id) }}" style="display:inline-block;margin-left:8px">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete permanently</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"><p class="empty-note">No trashed appointments found.</p></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
