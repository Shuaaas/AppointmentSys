@extends('layout.app')

@section('title', 'History')
@section('page-title', 'History')

@section('content')
<div class="card">
    <div class="card-body">
        <h3 class="h5 mb-3">Completed appointments</h3>

        @if ($history->isEmpty())
            <p class="text-muted mb-0">No concluded appointments found.</p>
        @else
            <ul class="list-group">
                @foreach ($history as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $item->full_name }}</strong>
                            <div class="small text-muted">{{ $item->position_title }}</div>
                        </div>
                        <span class="badge bg-secondary">{{ optional($item->date_concluded)->format('M d, Y') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
