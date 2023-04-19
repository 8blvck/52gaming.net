<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    static function get_list(string $dbp = '', int $offset = 0, int $limit = 3, int $exclude = 0) {
      $res = DB::select("
        SELECT DISTINCT r.id, r.comment, r.mark, r.created_at, users.nick_name as author, 
        games.name as game, ot.name as type_name, ot.review_icon as type_icon, r.updated_at
        FROM reviews as r
        LEFT JOIN games ON games.id = r.order_game
        LEFT JOIN users ON users.id = r.client_id
        LEFT JOIN order_types AS ot ON ot.id = r.order_type
        WHERE r.publish = 1 AND r.id <> $exclude
        ORDER BY r.updated_at DESC
      ");
      $reviews = array_slice($res, $offset, $limit);
      $hidden = array_slice($res, $offset + $limit);
      return (object)['list'=>$reviews, 'found'=>sizeof($res), 'hidden'=>sizeof($hidden)];
    }

    static function to_html($data = [], $_locale) {
      ob_start();
      foreach($data as $item): ?>
        <div class="item">
            <div class="logo">
              <?php if($item->type_icon): ?>
                <img src="<?= url('/public/img/order/types/' . $item->type_icon) ?>" alt="<?= __($item->type_name ) ?>">
              <?php endif; ?>
            </div>
            <div class="text">
                <div class="rating r-<?= $item->mark ?>"><i></i><i></i><i></i><i></i><i></i></div>
                <h6><?= __($item->type_name) ?></h6>
                <div class="message">
                    <p><?= $item->comment ?></p>
                </div>
                <div class="author"><?= $item->author ?? 'anonymous' ?></div>
            </div>
        </div>  
      <?php
      endforeach;
      return ob_get_clean();
    }
}
