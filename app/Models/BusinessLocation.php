<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable=[
        "name",
        "email",
        "mobile",
        "landmark",
        "country",
        "state",
        "city",
        "zipcode"
    ];
}
