<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;

class AdminController extends Controller
{
    public function index()
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect('/login');
        }
        $polls = Poll::latest()->get();
        return view('admin', compact('polls')); 
    }

    public function create()
    {
         return view('admin'); 
    }

    public function store(Request $request)
    {
        // Simple create for runtime
        $poll = Poll::create([
            'question' => $request->question,
            'is_active' => true
        ]);

        if($request->options && is_array($request->options)) {
            foreach ($request->options as $optionText) {
                if(trim($optionText)) {
                    PollOption::create([
                        'poll_id' => $poll->id,
                        'option_text' => $optionText
                    ]);
                }
            }
        }

        return redirect()->route('admin.index');
    }

    // Module 4: View IPs for a poll
    // Module 4: View IPs for a poll
    public function showVotes(Request $request, $pollId)
    {
        $poll = Poll::findOrFail($pollId);
        // Get all votes (active and released) ordered by time
        $votes = $poll->votes()->with('option')->orderBy('created_at', 'desc')->get();
        
        return view('votes_admin', compact('poll', 'votes'));
    }

    // Module 4: Release IP
    public function releaseIp(Request $request, $voteId)
    {
        $vote = \App\Models\Vote::findOrFail($voteId);
        $vote->is_released = true;
        $vote->save();

        return response()->json(['status' => 'success']);
    }

    // Toggle Poll Status (Open/Close)
    public function toggleStatus(Request $request, $id)
    {
        $poll = Poll::findOrFail($id);
        $poll->is_active = !$poll->is_active;
        $poll->save();
        return redirect()->back();
    }

    // Delete Poll
    public function destroy(Request $request, $id)
    {
        $poll = Poll::findOrFail($id);
        // Cascade delete options/votes manually since mock DB doesn't support FK cascade
        \App\Models\PollOption::where('poll_id', $id)->delete(); // Need logic for this? Or just raw SQL
        \App\Models\Vote::where('poll_id', $id)->delete();
        // Since my mock model might not have strict delete, let's use raw or just rely on IDs
        // Actually mock has no delete() on builder yet? Check Runtime.
        // Assuming I might need to add delete() to Builder.
        
        // For now, let's use the DB facade for safety in this mock env
        \Illuminate\Support\Facades\DB::pdo()->exec("DELETE FROM poll_options WHERE poll_id = $id");
        \Illuminate\Support\Facades\DB::pdo()->exec("DELETE FROM votes WHERE poll_id = $id");
        \Illuminate\Support\Facades\DB::pdo()->exec("DELETE FROM polls WHERE id = $id");

        return redirect()->back();
    }

    // Utility: Reset Database (Clear all polls)
    public function resetDatabase()
    {
        if (\Illuminate\Support\Facades\Auth::id() != 1) {
             return redirect('/login');
        }

        // Disable foreign key checks to truncate
        \Illuminate\Support\Facades\DB::pdo()->exec("SET FOREIGN_KEY_CHECKS=0");
        \App\Models\Vote::truncate(); // If method missing, use raw SQL in runtime or just exec
        \Illuminate\Support\Facades\DB::pdo()->exec("TRUNCATE TABLE votes");
        \Illuminate\Support\Facades\DB::pdo()->exec("TRUNCATE TABLE vote_histories");
        \Illuminate\Support\Facades\DB::pdo()->exec("TRUNCATE TABLE poll_options");
        \Illuminate\Support\Facades\DB::pdo()->exec("TRUNCATE TABLE polls");
        \Illuminate\Support\Facades\DB::pdo()->exec("SET FOREIGN_KEY_CHECKS=1");

        return redirect()->route('admin.index');
    }
}
