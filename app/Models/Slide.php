<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slides';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = ['title'];
}
