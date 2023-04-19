<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    protected $table = 'job_requests';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = [
		'game_id',
		'username',
		'email',
		'nickname',
		'vkontakte',
		'facebook',
		'discord',
		'skype',
		'telegram',
		'exp',
		'exp_source',
		'play_hours',
		'play_week',
		'comment',    	
    	'updated_at',
    	'created_at'
    ];
}
