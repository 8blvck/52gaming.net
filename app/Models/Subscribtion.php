<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class Subscribtion extends Model
{
    protected $table = 'users_subscriptions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = ['user_id','email','type'];

}
