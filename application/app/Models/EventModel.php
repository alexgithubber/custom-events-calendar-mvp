<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventModel extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'user_id',
        'location',
        'date',
    ];

    public function invitees(): hasMany
    {
        return $this->hasMany(EventInviteeModel::class, 'event_id', 'id');
    }
}
