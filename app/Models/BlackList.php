<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackList extends Model
{
    protected $table = 'black_lists';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = [
    	'user_id',
		'facebook',
		'vkontakte',
		'skype',
		'discord',
		'other',
		'reason',
		'updated_at',
		'created_at',
    ];
}
