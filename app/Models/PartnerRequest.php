<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerRequest extends Model
{
    protected $table = 'job_requests_partners';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = [
		'games',
		'service_link',
		'email',
		'username',
		'vkontakte',
		'facebook',
		'discord',
		'skype',
		'comment',
		'updated_at',
		'created_at',
    ];
}
