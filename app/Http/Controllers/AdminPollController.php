<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;

class AdminPollController extends Controller
{
    public function create()
    {
        return view('admin.polls.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255'
        ]);

        $poll = Poll::create([
            'question' => $request->question,
            'is_active' => true
        ]);

        foreach ($request->options as $optionText) {
            PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => $optionText
            ]);
        }

        return redirect()->route('polls.index')->with('success', 'Poll created successfully!');
    }
}
