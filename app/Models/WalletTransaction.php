<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'receiver_id', 'amount',
    ];

    /**
     * RELATIONSHIPS
     */

     //Relationship with Sender
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    //Relationship with Receiver
    public function receiver() {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
