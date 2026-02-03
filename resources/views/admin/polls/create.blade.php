@extends('layouts.app')

@section('content')
<div class="card col-md-8 mx-auto">
    <div class="card-header">Create New Poll</div>
    <div class="card-body">
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
