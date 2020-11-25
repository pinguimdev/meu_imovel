<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profile';

    protected $fillable = [
        'about', 'social_networks', 'phone', 'mobile_phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
