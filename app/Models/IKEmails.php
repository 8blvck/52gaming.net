<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IKEmails extends Model
{
    protected $table = 'payment_emails';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = ['tx','status'];
}
