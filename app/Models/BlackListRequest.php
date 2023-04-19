<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackListRequest extends Model
{
    protected $table = 'black_lists_requests';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = [
		'username',
		'email',  	
        'files',    
		'comment',  	
    	'updated_at',
    	'created_at'
    ];
}
