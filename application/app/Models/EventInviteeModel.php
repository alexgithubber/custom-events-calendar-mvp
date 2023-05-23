<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventInviteeModel extends Model
{
    use HasFactory;

    protected $table = 'event_invitees';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'email',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventModel::class, 'event_id');
    }
}
