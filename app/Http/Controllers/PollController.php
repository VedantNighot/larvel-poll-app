<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;

class PollController extends Controller
{
    // Show list of active polls
    public function index()
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect('/login');
        }

        // Module 3: Active polls only
        $polls = Poll::where('is_active', true)->latest()->get();
        return view('polls', compact('polls')); 
    }

    // AJAX Endpoint: Get options for a specific poll
    public function getOptions(Request $request, $id)
    {
        error_log("DEBUG: getOptions called for ID: $id");
        
        // Explicitly fetch poll and options to avoid runtime "with" complexity issues
        $poll = Poll::findOrFail($id);
        
        if (!$poll) {
            return response()->json(['status' => 'error', 'message' => 'Poll not found'], 404);
        }

        error_log("DEBUG: Poll found: " . json_encode($poll));
        
        $options = PollOption::where('poll_id', $id)->get();
        // Force conversion to plain array to ensure JSON array output
        if (is_object($options) && method_exists($options, 'getArrayCopy')) {
            $options = $options->getArrayCopy();
        } else {
             $options = (array)$options;
        }

        error_log("DEBUG: Options found: " . json_encode($options));

        $response = [
            'status' => 'success',
            'poll' => $poll,
            'options' => array_values($options) // Ensure sequential keys [0,1,2] for JSON Array
        ];
        
        error_log("DEBUG: Returning response");
        return response()->json($response);
    }

    // AJAX Endpoint: Get live results
    public function getResults(Request $request, $id)
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
