<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
use Session;
use App\Http\Providers\ApiProvider;
use App\Models\Main;

class Promocode extends Model
{
    protected $table = 'order_promocodes';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
      'user_id',
      'type',
      'services',
      'code',
      'from',
      'till',
      'vtype',
    ];

    public function setCodeAttribute($value) {
        $this->attributes['code'] = $value == 'generate' ? Main::randstr(12) : $value;
    }

    static function check($code = null, $uid = 0, $service = 0) {
      $res = DB::select("SELECT * FROM order_promocodes WHERE code = '$code' LIMIT 1");
      $code = $res ? $res[0] : null;
      if(!$code) return $code;
      $code->services = array_filter(explode('|', $code->services));
      $used = DB::select("SELECT * FROM order_promocodes_used WHERE code_id = $code->id AND user_id = $uid LIMIT 1");
      if(strtotime($code->expires_at) <= time()):
        return null;
      elseif($code->user_id && $code->user_id != $uid):
        return null;
      elseif($code->type == 1 && $used):
        return null;
      elseif(!empty($code->services) && !in_array($service, $code->services)):
        return null;
      else:
        $amount = mt_rand($code->from, $code->till);
        $value = ($code->vtype == 2 ? $amount.'%' : $amount.'$');
        Session::put('promocode', $code->code);
        Session::put('promovalue', $value);
        return $value;
      endif;
    }
}
