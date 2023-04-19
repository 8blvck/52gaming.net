<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class OurReviews extends Model
{
    protected $table = 'our_reviews';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'avatar',
        'author',
        'message',
        'user_id',
        'order_id',
        'order_type',
        'mark',
        'promo_sent',
    ];

    static function get_list(string $dbp = '', int $offset = 0, int $limit = 3, int $exclude = 0) {
      $res = DB::select("
        SELECT DISTINCT r.id, r.message as comment, r.mark, r.created_at, r.author, 
        games.name as game, ot.name as type_name, ot.review_icon as type_icon, r.created_at
        FROM our_reviews as r
        LEFT JOIN order_types AS ot ON ot.id = r.order_type
        LEFT JOIN games ON games.id = ot.game
        LEFT JOIN users ON users.id = r.user_id
        WHERE r.display = 1 AND r.id <> $exclude
        ORDER BY r.created_at DESC
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
