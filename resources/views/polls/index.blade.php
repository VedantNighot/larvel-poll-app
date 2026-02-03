@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Active Polls List -->
    <div class="col-md-4">
        <h4>Active Polls</h4>
        <div class="list-group" id="poll-list">
            @foreach($polls as $poll)
                <a href="#" class="list-group-item list-group-item-action poll-item" data-id="{{ $poll->id }}">
                    {{ $poll->question }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Poll Detail View (Loaded via AJAX) -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0" id="poll-title">Select a poll to vote</h5>
            </div>
            <div class="card-body" id="poll-content">
                <p class="text-muted">Click on a poll from the left to load options.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/poll.js') }}"></script>
@endsection
