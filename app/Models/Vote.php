<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;
    
    // 'is_released' false = active vote, true = released (history)
    protected $fillable = ['poll_id', 'poll_option_id', 'user_ip', 'is_released'];

    protected $casts = [
        'is_released' => 'boolean',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function option()
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }
}
