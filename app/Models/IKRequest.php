<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class IKRequest extends Model
{
    protected $table = 'payment_requests';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = ['post_data', 'get_data', 'sign', 'system'];

    const attributes = [
      'kassa_id' 		=> "57c40abd3c1eafa32e8b4572",
	  	'ik_sci' 		  => "https://sci.interkassa.com/",
		  'secret' 		  => "lGeBV5MPEQ2Cbr4a",
		  'secret_test' => "ZPRt7DlAll3GYTyl",
	  	'currency' 		=> "RUB",
      'interact_url'=> '/ipn/ik-ipn'
  	];

  	public static function settings($attr) {
      $results = self::attributes[$attr] ?? null;
  		return $attr == 'interact_url' ? url($results) : $results;
  	}

    public static function get_sign($data) {
        unset($data['ik_sign']);
        ksort($data, SORT_STRING);
        array_push($data, self::attributes['secret']);
        $signString = implode(':', $data);
        $sign = base64_encode(md5($signString, true));
        return $sign;                                   
    }
}
