<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupDetail extends Model
{
    use HasFactory;

    protected $table = 'group_details';

    protected $fillable = [
        'group_id',
        'card_name',
        'card_code',
        'currency',
        'cust_bu'
    ];

    public function group()
    {
        return $this->belongsToMany(Group::class);
    }
}
