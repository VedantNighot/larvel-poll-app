<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vote;
use App\Models\Poll;

class VoteController extends Controller
{
    public function store(Request $request, $pollId)
    {
        $request->validate([
            'option_id' => 'required|exists:poll_options,id'
        ]);

        $ip = $request->ip();

        // 1. Check if IP has an ACTIVE vote
        $activeVote = Vote::where('poll_id', $pollId)
                            ->where('user_ip', $ip)
                            ->where('is_released', false)
                            ->first();

        if ($activeVote) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already voted on this poll (IP Restriction).'
            ], 403);
        }

        // 2. Store Vote (New history entry)
        Vote::create([
            'poll_id' => $pollId,
            'poll_option_id' => $request->option_id,
            'user_ip' => $ip,
            'is_released' => false
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vote recorded successfully!'
        ]);
    }
}
