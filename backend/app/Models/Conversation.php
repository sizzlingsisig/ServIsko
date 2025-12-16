<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['subject'];

    // Relationship: A conversation has many messages
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    // Relationship: A conversation belongs to many users (participants)
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot('last_read_at')
                    ->withTimestamps();
    }

    // Helper: Get the latest message in the conversation
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    // Helper: Check if user is a participant
    public function hasParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }
}
