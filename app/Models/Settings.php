<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class Settings extends Model
{
    protected $table = 'landing_settings';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    static function values($names) {
      $result = [];
      $names = array_filter(is_array($names) ? $names : [$names]);
      $res = DB::select("SELECT name, value FROM landing_settings WHERE name IN ('".implode("','", $names)."') ");
      foreach($names as $name):
        $result[$name] = object_find($res, 
          function($e) use ($name) { return $e->name == $name; }, 
          function($e) { return $e->value; }
        );
      endforeach;
      return (object)$result;
    }
}
