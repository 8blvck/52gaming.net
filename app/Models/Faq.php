<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class Faq extends Model
{
    protected $table = 'faq';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = ['type'];

    static function general(string $pre = 'ru_') {
      $res = DB::select("
        SELECT faq.".$pre."answer as answer, faq.".$pre."question as question
        FROM faq
        WHERE faq.display = 1 AND faq.type = 1
        ORDER BY faq.display_number
      ");
      return $res ?? [];
    }

    static function payment_failure(string $pre = 'ru_') {
      $res = DB::select("
        SELECT faq.".$pre."answer as answer, faq.".$pre."question as question
        FROM faq
        WHERE faq.display = 1 AND faq.type = 2
        ORDER BY faq.display_number
      ");
      return $res ?? [];
    }
}
