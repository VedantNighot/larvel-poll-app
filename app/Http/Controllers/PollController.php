<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;

class PollController extends Controller
{
    // ... index method ...

    // AJAX Endpoint: Get options for a specific poll
    public function getOptions($id)
    {
        $poll = Poll::with('options')->findOrFail($id);
        // ...
    }

    // AJAX Endpoint: Get live results
    public function getResults($id)
    {
        // Count votes per option
        $results = PollOption::where('poll_id', $id)
            ->withCount(['votes' => function($query) {
                // Only count active votes (Module 4 prep: status != released)
                // For now, count all. Will update in Module 4.
                $query->where('is_released', false); 
            }])
            ->get()
            ->map(function($option) {
                return [
                    'option_text' => $option->option_text,
                    'votes' => $option->votes_count
                ];
            });

        return response()->json([
            'status' => 'success',
            'results' => $results
        ]);
    }
}
