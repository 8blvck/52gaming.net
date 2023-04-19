<?php

namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'landing_pages';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = ['name','title','slug'];

    static function for_orders() {
        $res = DB::select("
            SELECT order_types.*, pages.slug 
            FROM pages JOIN order_types ON order_types.id = pages.order_type
            WHERE pages.display > 0 AND pages.order_type > 0 ORDER BY order_types.id ASC
        ");
        foreach($res as &$item):
             $item->info_labels = json_decode($item->info_labels);
             $item->info_labels = $item->info_labels ? $item->info_labels : [];
        endforeach;
    	return $res; 
    }

    static function get_configurations() {
        $res = DB::select('SELECT * FROM order_configure_steps LIMIT 1');
        if($res = isset($res[0]) ? $res[0] : null):
            $res->step_0_texts = array_filter(explode('|', $res->step_0_texts));
            $res->step_1_texts = array_filter(explode('|', $res->step_1_texts));
            $res->step_2_texts = array_filter(explode('|', $res->step_2_texts));
            $res->step_3_texts = array_filter(explode('|', $res->step_3_texts));
        endif;
        return $res;
    }
}
