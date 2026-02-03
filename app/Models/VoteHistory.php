<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteHistory extends Model
{
    use HasFactory;
    
    // Tracks released votes
    protected $fillable = ['poll_id', 'poll_option_id', 'user_ip', 'voted_at', 'released_at'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
    
    public function option()
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }
}
