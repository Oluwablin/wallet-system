<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaystackTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'reference', 'status', 'amount', 'paid_at',
    ];

    /**
     * RELATIONSHIPS
     */

     //Relationship with User
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
