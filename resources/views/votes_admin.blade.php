@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Votes for: {{ $poll->question }}</h4>
    <a href="{{ route('admin.index') }}" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>IP Address</th>
                <th>Voted Option</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($votes as $vote)
            <tr class="{{ $vote->is_released ? 'table-warning text-muted' : '' }}">
                <td>{{ $vote->user_ip }}</td>
                <td>{{ $vote->option->option_text ?? 'N/A' }}</td>
                <td>{{ $vote->created_at }}</td>
                <td>
                    @if($vote->is_released)
                        <span class="badge bg-secondary">Released (History)</span>
                    @else
                        <span class="badge bg-success">Active</span>
                    @endif
                </td>
                <td>
                    @if(!$vote->is_released)
                        <button class="btn btn-warning btn-sm release-btn" data-id="{{ $vote->id }}">Release IP</button>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
$(document).on('click', '.release-btn', function() {
    let btn = $(this);
    let id = btn.data('id');
    
    if(!confirm('Release this IP? The user will be allowed to vote again.')) return;

    $.ajax({
        url: '/admin/votes/' + id + '/release',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            location.reload(); // Reload to show updated status/history
        },
        error: function() {
            alert('Error releasing IP');
        }
    });
});
</script>
@endsection
