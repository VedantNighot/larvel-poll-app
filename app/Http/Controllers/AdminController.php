<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;

class AdminController extends Controller
{
    // ...

    public function store(Request $request)
    {
        // ...
        foreach ($request->options as $optionText) {
            PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => $optionText
            ]);
        }
        // ...
    }

    // Module 4: View IPs for a poll
    public function showVotes($pollId)
    {
        $poll = Poll::findOrFail($pollId);
        // Get all votes (active and released) ordered by time
        $votes = $poll->votes()->with('option')->orderBy('created_at', 'desc')->get();
        
        return view('votes_admin', compact('poll', 'votes'));
    }

    // Module 4: Release IP
    public function releaseIp($voteId)
    {
        $vote = \App\Models\Vote::findOrFail($voteId);
        $vote->is_released = true;
        $vote->save();

        return response()->json(['status' => 'success']);
    }
}
