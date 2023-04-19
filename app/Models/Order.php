<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class Order extends Model
{
    protected $table = 'order_types';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = ['name'];
        
    static function build_tx($user_id, $amount, $note = 'Fastboosting funds refill') {
      $req = [
        'user_id' => $user_id ?? 0, 
        'amount'  => $amount ?? 0,
        'note'    => $note,
      ];
      $res = ApiProvider::create_tx($req);
      return $res['code'] == 200 ? $res['system_number'] : null;
    }

    static function request($form) {
      $req = [
        'urgency'            => $form['urgency'] ?? 24,
        'user_id'            => $form['user_sid'] ?? 0,
        'method'             => $form['method'] ?? 0,
        'amount'             => $form['amount'] ?? 0,
        'type'               => $form['type'] ?? 0,
        'quality'            => $form['quality'] ?? 0,
        'comment'            => $form['comment'] ?? null,
        'promocode'          => $form['promocode'] ?? null,
        'promovalue'         => $form['promovalue'] ?? null,
        'ban_guard'          => $form['banguard'] == 'on' ? 1 : 0,
        'mmr_from'           => $form['from'] ?? 0,
        'mmr_to'             => $form['to'] ?? 0,
        'picks'              => $form['picks'] ?? [],
        'bans'               => $form['bans'] ?? [],
        'medal_from'         => $form['medal_from'] ?? 0,
        'medal_to'           => $form['medal_to'] ?? 0,
        'medal_prev'         => $form['medal_prev'] ?? 0,
        'games'              => $form['games'] ?? 0,
        'cali_type'          => $form['cali_type'] ?? 0,
        'cali_warranty'      => $form['win_warranty'] == 'on' ? 1 : 0,
        'training_services'  => $form['services'] ?? [],
        'training_hours'     => $form['hours'] ?? 0,
        'training_coach'     => $form['coach'] ?? 0,
        'training_time_from' => $form['date_from'] ?? 0,
        'training_time_till' => $form['date_till'] ?? 0,
      ];
      $res = ApiProvider::create_order($req);
      return $res['code'] == 200 ? $res['order'] : null;
    }

    static function order_types() {
      $res = DB::select("SELECT * FROM order_types WHERE display > 0");
      return $res ?? [];
    }

    static function order_type(int $id = 1) {
      $res = DB::select("SELECT order_types.*, order_types.game as game_id, (SELECT name FROM games WHERE id = order_types.game) as game FROM order_types WHERE id = $id LIMIT 1");
      return $res[0] ?? null;
    }

    static function calibration_settings() {
      $res = DB::select("SELECT * FROM order_calibration_settings LIMIT 1");
      return $res[0] ?? null;
    }

    static function calibration_qualities() {
      $res = DB::select("SELECT * FROM order_calibration_quality WHERE display > 0 ORDER BY display_number");
      foreach($res as &$item) $item->labels = json_decode($item->labels) ?? [];
      return $res ?? [];
    }

    static function calibration_prices() {
      $res = DB::select("SELECT `id`, `from`, `till`, rub as price FROM order_calibration_prices ORDER BY `from`");
      return $res ?? [];
    }

    static function lp_settings() {
      $res = DB::select("SELECT * FROM order_lp_settings LIMIT 1");
      return $res[0] ?? null;
    }

    static function medal_settings() {
      $res = DB::select("SELECT * FROM order_medals_settings LIMIT 1");
      return $res[0] ?? null;
    }

    static function boosting_quality(int $id = 1) {
      $res = DB::select("SELECT *, rub as price FROM order_boosting_quality WHERE id = $id LIMIT 1");
      return $res[0] ?? null;
    }

    static function boosting_qualities() {
      $res = DB::select("SELECT *, rub as price FROM order_boosting_quality WHERE display > 0 ORDER BY display_number");
      foreach($res as &$item) $item->options = explode('|', $item->options);
      return $res ?? [];
    }

    static function boosting_settings() {
      $res = DB::select("SELECT * FROM order_boosting_settings LIMIT 1");
      return $res[0] ?? null;
    }

    static function boosting_prices() {
      $res = DB::select("SELECT `from`,`till`,`volume`,`rub` as price FROM order_boosting_prices");
      return $res ?? [];
    }

    static function boosting_timings() {
      $res = DB::select("SELECT `from`,`till`,`volume`,`hours` FROM order_boosting_timings");
      return $res ?? [];
    }
    
    static function training_settings() {
      $res = DB::select("SELECT * FROM order_training_settings LIMIT 1");
      return $res[0] ?? null;
    }
    
    static function training_prices() {
      $res = DB::select("SELECT hours, rub as price FROM order_training_prices");
      return $res;
    }
    
    static function training_services() {
      $res = DB::select("SELECT id, name, heroes, rub as price FROM order_training_services ORDER BY heroes");
      return $res;
    }

    static function get_heroes($ez = false) {
      if($ez):
        $res = DB::select("SELECT id, localized_name FROM heroes");
      else:
        $res = DB::select("SELECT DISTINCT attribute FROM heroes LIMIT 3");
        foreach($res as &$item):
          $item->heroes = DB::select("SELECT id, localized_name FROM heroes WHERE attribute = '$item->attribute'");
        endforeach;
      endif;
      return $res ?? [];
    }

    static function get_medals($ez = false) {
      if($ez == 'list'):
        $res = DB::select("SELECT id, title, rank, image, rub as price FROM order_medals_prices");
      elseif($ez == 'simple'):
        $res = DB::select("SELECT id, rub as price, `time` FROM order_medals_prices");
      else:
        $res = DB::select("SELECT DISTINCT title FROM order_medals_prices");
        foreach($res as &$item):
          $item->medals = DB::select("SELECT id, title, rank, image, rub as price FROM order_medals_prices WHERE title = '$item->title'");
        endforeach;
      endif;
      return $res ?? [];
    }

    static function calculate($form) {
      $amount = 0;
      if($form['type'] == 1):
        $amount = self::calculate_boosting($form);
      elseif($form['type'] == 2):
        $amount = self::calculate_calibration($form);
      elseif($form['type'] == 3):
        $amount = self::calculate_medal($form);
      elseif($form['type'] == 4):
        $amount = self::calculate_training($form);
      elseif($form['type'] == 5):
        $amount = self::calculate_low_priority($form);
      endif;
      return number_format($amount < 0 ? 0 : $amount,2,'.','');
    }

    static function urgency($form) {
      $hours = 0;
      if($form['type'] == 1):
        $hours = self::urgency_boosting($form);
      elseif($form['type'] == 2):
        $hours = self::urgency_calibration($form);
      elseif($form['type'] == 3):
        $hours = self::urgency_medal($form);
      elseif($form['type'] == 4):
        $hours = self::urgency_training($form);
      elseif($form['type'] == 5):
        $hours = self::urgency_low_priority($form);
      endif;
      return $hours;
    }

    static function calculate_discount($amount, $promovalue) {
      $value = (float)preg_replace('/[^0-9]/', '', $promovalue) ?: 0;
      return strpos($promovalue, '%') ? (float)($amount / 100 * $value) : (float)($value);
    }

    static function calculate_training($form) {
      $prices = self::training_prices();
      $services = self::training_services();
      $amount = 0;
      foreach($prices as $item):
        if($item->hours == $form['hours']) $amount += $item->price;
      endforeach;
      foreach($services as $item):
        if(in_array($item->id, $form['services'])) $amount += $item->price;
      endforeach;
      $discount = self::calculate_discount($amount, $form['promovalue']);
      $amount -= $discount;
      return $amount;
    }

    static function calculate_calibration($form) {
      $settings = self::calibration_settings();
      $qualities = self::calibration_qualities();
      $prices = self::calibration_prices();
      $quality = object_find($qualities, function($e) use ($form) { return $e->id == $form['cali_type']; });
      $range = object_find($prices, function($e) use ($form) { return $form['from'] >= $e->from && $form['from'] <= $e->till; });
      $amount = 0;
      $amount += $form['games'] * ($range->price ?? object_last($prices)->price ?? 0);
      $discount = self::calculate_discount($amount, $form['promovalue']);
      $warranty_price = ($amount / 100 * ($form['win_warranty'] == 'on' ? $settings->warranty_price : 0));
      $amount += ($quality ? ($amount / 100 * $quality->price) : 0);
      $amount = $amount + $warranty_price - $discount;
      return $amount;
    }

    static function calculate_low_priority($form) {
      $settings = self::lp_settings();
      $amount = 0;
      $amount += $form['games'] * $settings->price;
      $discount = self::calculate_discount($amount, $form['promovalue']);
      $amount -= $discount;
      return $amount;
    }

    static function calculate_medal($form) {
      $picks_price = 0;
      $bans_price = 0;
      $guard_price = 0;
      $prices = self::get_medals('list');
      $settings = self::medal_settings();
      $amount = self::medal_price($form['medal_from'],$form['medal_to'],$prices);
      $discount = self::calculate_discount($amount, $form['promovalue']);
      $guard_price = ($amount / 100 * ($form['banguard'] ? $settings->ban_guard_price : 0));
      $picks_price = sizeof($form['picks']) ? ($amount / 100 * $settings->hero_pick_price) : 0;
      $bans_price = sizeof($form['bans']) ? ($amount / 100 * $settings->hero_ban_price) : 0;
      $amount = $amount + $bans_price + $picks_price + $guard_price - $discount;
      return $amount;
    }

    static function calculate_boosting($form) {
      $picks_price = 0;
      $bans_price = 0;
      $guard_price = 0;
      $prices = self::boosting_prices();
      $settings = self::boosting_settings();
      $qualities = self::boosting_qualities();
      $quality = object_find($qualities, function($e) use ($form) { return $e->id == $form['quality']; });
      if($qualities && !$quality) $quality = object_first($qualities);
      $amount = self::boost_price($form['from'],$form['to'],$prices);
      $amount += ($quality->price ?? 0);
      $discount = self::calculate_discount($amount, $form['promovalue']);
      $guard_price = ($amount / 100 * ($form['banguard'] == 'on' ? $settings->ban_guard_price : 0));
      $picks_price = sizeof($form['picks']) ? ($amount / 100 * $settings->hero_pick_price) : 0;
      $bans_price = sizeof($form['bans']) ? ($amount / 100 * $settings->hero_ban_price) : 0;
      $amount = $amount + $bans_price + $picks_price + $guard_price - $discount;
      return $amount;
    }

    static function urgency_boosting($form) {
      $hours = 0;
      $timings = self::boosting_timings();
      $hours = self::boost_hours($form['from'],$form['to'],$timings);
      return $hours;
    }

    static function urgency_calibration($form) {
      $hours = 0;
      $settings = self::calibration_settings();
      $hours += $settings->time_hours / $settings->time_volume * $form['games'];
      return ceil($hours);
    }

    static function urgency_medal($form) {
      $hours = 0;
      $timings = self::get_medals('list');
      $hours = self::medal_hours($form['medal_from'],$form['medal_to'],$timings);
      return $hours;
    }

    static function urgency_training($form) {
      $hours = 0;
      return $hours;
    }

    static function urgency_low_priority($form) {
      $hours = 0;
      $settings = self::lp_settings();
      $hours += $settings->time_hours / $settings->time_volume * $form['games'];
      return ceil($hours);
    }

    static function medal_hours($a, $b, $timings) {
      $start = intval($a) ?? 0;
      $end = intval($b) ?? 0;
      $hours = 0;
      foreach($timings as $e):
        if($e->id > $a && $e->id <= $b && $a != $b) $hours += $e->time;
      endforeach;
      return $hours;
    }

    static function medal_price($a, $b, $prices) {
      $start = intval($a) ?? 0;
      $end = intval($b) ?? 0;
      $amount = 0;
      foreach($prices as $e):
        if($e->id > $a && $e->id <= $b && $a != $b) $amount += $e->price;
      endforeach;
      return $amount;
    }

    static function boost_hours($a, $b, $timings) {
      $start = intval($a) ?? 0;
      $end = intval($b) ?? 0;
      $hours = 0;
      foreach($timings as $e):
        if(self::in_range($start, $e->from, $e->till) && $start <= $end):
          $in_range = 0;
          if($end >= $e->till):
            $in_range = $e->till - $start;
          else:
            $in_range = $end - $start;
          endif;
          $hours += $in_range/$e->volume*$e->hours;
          $start += $in_range;
        endif;
      endforeach;
      return ceil($hours);
    }

    static function boost_price($a, $b, $prices) {
      $start = intval($a) ?? 0;
      $end = intval($b) ?? 0;
      $amount = 0;
      foreach($prices as $e):
        if(self::in_range($start, $e->from, $e->till) && $start <= $end):
          $in_range = 0;
          if($end >= $e->till):
            $in_range = $e->till - $start;
          else:
            $in_range = $end - $start;
          endif;
          $amount += $in_range/$e->volume*$e->price;
          $start += $in_range;
        endif;
      endforeach;
      return $amount;
    }

    static function in_range($x, $a, $b) {
      if($x >= $a && $x <= $b) return true;
      return false; 
    }

    static function matches_html($matches = [], $cdn) {
      ob_start(); 
      ?>
      <thead>
          <tr><td><?= __('Дата') ?></td><td><?= __('Тип игры') ?></td><td><?= __('Результат') ?></td><td><?= __('Герой') ?></td><td><?= __('KDA') ?></td><td><?= __('Вещи героя') ?></td></tr>
      </thead>
      <tbody>   
      <?php
      if($matches):
      foreach($matches as $item): 
        ?>
        <tr>
            <td><span class="tracking-app-games-history-table-title-alternate"><?= __('Дата') ?></span> <?= date('d.m.Y', $item->start_time) ?></td>
            <td><span class="tracking-app-games-history-table-title-alternate"><?= __('Тип игры') ?></span> <?= $item->game_mode ?></td>
            <td><span class="tracking-app-games-history-table-title-alternate"><?= __('Результат') ?></span> <?= $item->win ? __('Победа') : __('Поражение') ?></td>
            <td>
              <span class="tracking-app-games-history-table-title-alternate"><?= __('Герой') ?></span> 
              <div class="tracking-app-games-history-table-hero"><img src="<?= $cdn.'/dota/heroes/'.$item->hero_id.'_sm.png' ?>"></div>
            </td>
            <td class="kda">
              <span class="tracking-app-games-history-table-title-alternate"><?= __('KDA') ?></span> <?= $item->kills ?>/<?= $item->deaths ?>/<?= $item->assists ?>
              <div class="tracking-app-chart-bar-stacked" data-values="<?= $item->kills ?>/<?= $item->deaths ?>/<?= $item->assists ?>"><i></i><i></i><i></i></div>
            </td>
            <td>
              <span class="tracking-app-games-history-table-title-alternate"><?= __('Вещи героя') ?></span>
              <div class="tracking-app-games-history-table-item">
              <?php 
              foreach($item->items as $weapon_id): $weapon_image = $cdn.'/dota/items/'.$weapon_id.'.png'; 
                if(@getimagesize($weapon_image)):
                  ?>  <img src="<?= $weapon_image ?>"> <?php 
                endif;
              endforeach; 
              ?>
              </div>
            </td>
        </tr>
      <?php 
      endforeach; 
      ?>
      </tbody>
      <?php                    
      endif;
      return ob_get_clean();
    }

}
