<div class="card card-body">
    <h3 class="card-title">{{ $poll->question }}</h3>
    
    <form id="vote-form" data-id="{{ $poll->id }}">
        @foreach($options as $option)
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="option_id" id="opt-{{ $option->id }}" value="{{ $option->id }}">
                <label class="form-check-label" for="opt-{{ $option->id }}">
                    {{ $option->option_text }}
                </label>
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary mt-3">Vote</button>
    </form>
    
    <div id="results-area" class="mt-4"></div>
</div>
