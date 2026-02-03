@extends('layouts.app')

@section('content')
<div class="card col-md-8 mx-auto">
    <div class="card-header">Create New Poll</div>
    <div class="card-body">
        <a href="{{ route('polls.index') }}" class="btn btn-outline-primary mb-3">View Live Polls</a>
        
        <!-- List of existing polls to manage -->
        <h5 class="mt-4">Manage Polls</h5>
        <ul class="list-group mb-4">
            <!-- Ideally fetch from DB, but for now we link to specific ones or assume logic -->
            <!-- In a real app we'd pass $polls to this view -->
            @if(isset($polls))
            <div class="list-group">
            @foreach($polls as $poll)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $poll->question }}</h5>
                        <small class="text-muted">ID: {{ $poll->id }} | Active: {{ $poll->is_active ? 'Yes' : 'No' }}</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.votes.show', ['id' => $poll->id]) }}" class="btn btn-sm btn-info text-white">Manage Votes</a>
                        
                        <form action="{{ route('admin.polls.toggle', ['id' => $poll->id]) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $poll->is_active ? 'btn-warning' : 'btn-success' }}">
                                {{ $poll->is_active ? 'Close' : 'Open' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.polls.delete', ['id' => $poll->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
            @endif
        </ul>

        <hr>
        <h5>Create New</h5>
        <form action="{{ route('admin.polls.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Question</label>
                <input type="text" name="question" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Options</label>
                <div id="options-container">
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 1" required>
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 2" required>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addOption()">+ Add Option</button>
            </div>

            <button type="submit" class="btn btn-success">Create Poll</button>
        </form>
    </div>
</div>

<script>
function addOption() {
    let input = document.createElement('input');
    input.type = 'text';
    input.name = 'options[]';
    input.className = 'form-control mb-2';
    input.placeholder = 'New Option';
    document.getElementById('options-container').appendChild(input);
}
</script>
@endsection
