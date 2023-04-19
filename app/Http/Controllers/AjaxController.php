<?php
namespace App\Http\Controllers;

use Session;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Main;
use App\Models\User;
use App\Models\JobRequest;
use App\Models\PartnerRequest;
use App\Models\BlackList;
use App\Models\BlackListRequest;
use App\Models\Order;
use Lang;
use App;

class AjaxController extends Controller {
  private $request;
  private $responses;
  private $response = ['status'=>'failed','title'=>'','message'=>''];
  private $guarded = [
    'change_profile', 'change_password', 'change_email', 
    'create_ticket', 'checkout', 'postpayment', 'refill', 'account_admission', 
    'account_configuration', 'account_timings','add_order_revew','order_review_reward'
  ];

  private $steam_api = [
    'key' => "D820EA8DC6FF98314353F6D6E645F061",
    'matches' => "https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/",
    'match' => "https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/",
    'game_modes' => [
      0 => "None", 1 => "All Pick", 2 => "Captain's Mode", 3 => "Random Draft", 4 => "Single Draft", 5 => "All Random", 
      6 => "Intro", 7 => "Diretide", 8 => "Reverse Captain's Mode", 9 => "The Greeviling", 10 => "Tutorial", 11 => "Mid Only",
      12 => "Least Played", 13 => "New Player Pool", 14 => "Compendium Matchmaking", 15 => "Co-op vs Bots", 16 => "Captains Draft", 
      18 => "Ability Draft", 20 => "All Random Deathmatch", 21 => "1v1 Mid Only", 22 => "Рейтинговый",
    ]
  ];

  private function matches_html($matches = [], $cdn) {
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

  private function match_history() {
    $api = (object)$this->steam_api;
    $url = $this->request['url'];
    if(!filter_var($url, FILTER_VALIDATE_URL)):
      return $this->response = array_merge($this->response, $this->get_message(26));
    endif;
    $steam_id = rtrim($url, '/');
    $steam_id = explode('/', $steam_id);
    $steam_id = $steam_id ? end($steam_id) : null;
    if(!$steam_id):
      return $this->response = array_merge($this->response, $this->get_message(26));
    endif;
    $matches_res = @file_get_contents($api->matches.'?'.http_build_query(['key'=>$api->key,'account_id'=>$steam_id,'date_min'=>0,'date_max'=>0,'matches_requested'=>10]));
    $matches_res = @json_decode($matches_res);
    if(!$matches_res):
      $this->response['html'] = '';
    elseif($matches_res->result->status == 15):
      $this->response['html'] = '';
    else:
      $matches = [];
      $matches_list = isset($matches_res->result->matches) ? $matches_res->result->matches : [];
      $heroes = Order::get_heroes(true);
      foreach($matches_list as $match):
        $match_id = $match->match_id;
        $match_details = @file_get_contents($api->match.'?'.http_build_query(['key'=>$api->key,'match_id'=>$match_id]));
        $match_details = @json_decode($match_details);
        $match_details = isset($match_details->result) ? $match_details->result : false;
        if($match_details && $match_details->game_mode == 22):
          $details = [
            "match_id"      => $match_details->match_id,
            "start_time"    => $match_details->start_time,
            "duration"      => $match_details->duration,
            "dire_score"    => $match_details->duration,
            "radiant_score" => $match_details->duration,
            "radiant_win"   => !!$match_details->radiant_win,
            "game_mode"     => isset($api->game_modes[$match_details->game_mode]) ? $api->game_modes[$match_details->game_mode] : null,
          ];
          foreach($match_details->players as $player):
            if($player->account_id == $steam_id):
              $hero = object_find($heroes, function($h) use ($player) { return $h->id == $player->hero_id; });
              $slot = substr("00000000".decbin($player->player_slot), -8);
              $team = (int)$slot[0];
              $details["kills"] = $player->kills;
              $details["deaths"] = $player->deaths;
              $details["assists"] = $player->assists;
              $details["hero_id"] = $player->hero_id;
              $details["hero_name"] = isset($hero->localized_name) ? $hero->localized_name : null;
              $details["hero_lvl"] = $player->level;
              $details["slot"] = $slot;
              $details["team"] = !$team ? "radiant" : "dire";
              $details["win"] = $details['radiant_win'] === !$team;
              $details["items"] = array_filter([
                isset($player->item_0) ? $player->item_0 : null,
                isset($player->item_1) ? $player->item_1 : null,
                isset($player->item_2) ? $player->item_2 : null,
                isset($player->item_3) ? $player->item_3 : null,
                isset($player->item_4) ? $player->item_4 : null,
                isset($player->item_5) ? $player->item_5 : null,
              ]);
              $matches[] = (object)$details;
            endif;
          endforeach;
        endif;
      endforeach;
      $this->response['status'] = 'ok';
      $this->response['html'] = $this->matches_html($matches, $this->cdn);
    endif;
  }

  private function lastOrders() {
    $search = $this->request['search'] ?? '';
    $page = $this->request['page'] ?? 1;
    $onclick = $this->request['onclick'] ?? null;
    $details_url = $this->request['order_url'] ?? 'https://52gaming.net/statistics/';
    $list = DB::table('orders')
    ->select('orders.id','orders.created_at','orders.system_number','orders.type','orders.status','orders.mmr_start','orders.cali_games_total','orders.cali_games_done','orders.training_hours','orders.training_hours_done','orders.mmr_boosted','orders.medal_start','orders.medal_current','orders.medal_finish','orders.mmr_finish','orders.training_services','orders_types.pub as type_name','orders_games.icon as type_icon','orders_statuses.pub as status_name')
    ->leftJoin('orders_types', 'orders_types.id', '=', 'orders.type')
    ->leftJoin('orders_games', 'orders_games.id', '=', 'orders_types.game_id')
    ->leftJoin('orders_statuses', 'orders_statuses.id', '=', 'orders.status')
    ->selectRaw('if(orders.type = 6, (select title from users_pricelists_dota_cups_prices where id = orders.medal_current limit 1), null) as cup_current')
    ->selectRaw('if(orders.type = 6, (select title from users_pricelists_dota_cups_prices where id = orders.medal_finish limit 1), null) as cup_finish')
    ->selectRaw('if(orders.type = 9, (select concat(title," ",rank) as title from users_pricelists_dota_autochess_prices where id = orders.medal_start limit 1), null) as chess_start')
    ->selectRaw('if(orders.type = 9, (select concat(title," ",rank) as title from users_pricelists_dota_autochess_prices where id = orders.medal_current limit 1), null) as chess_current')
    ->selectRaw('if(orders.type = 9, (select concat(title," ",rank) as title from users_pricelists_dota_autochess_prices where id = orders.medal_finish limit 1), null) as chess_finish')
    ->selectRaw('if(orders.type = 3, (select concat(title," ",rank) as title from dota_medals where id = orders.medal_start limit 1), null) as rank_start')
    ->selectRaw('if(orders.type = 3, (select concat(title," ",rank) as title from dota_medals where id = orders.medal_current limit 1), null) as rank_current')
    ->selectRaw('if(orders.type = 3, (select concat(title," ",rank) as title from dota_medals where id = orders.medal_finish limit 1), null) as rank_finish')
    ->selectRaw('if(orders.type = 8, (select title from users_pricelists_dota_behavior_score_prices where orders.mmr_start >= `from` and orders.mmr_start <= `till` limit 1), null) as grade_start')
    ->selectRaw('if(orders.type = 8, (select title from users_pricelists_dota_behavior_score_prices where orders.mmr_start + orders.mmr_boosted >= `from` and orders.mmr_start <= `till` limit 1), null) as grade_current')
    ->selectRaw('if(orders.type = 8, (select title from users_pricelists_dota_behavior_score_prices where orders.mmr_finish >= `from` and orders.mmr_finish <= `till` limit 1), null) as grade_finish')
    ->whereIn('orders.type',[1,2,3,4,5,6,7,8,9,10,11])
    ->whereIn('orders.status',[1,2,3,4,5,6])
    ->orderBy('orders.created_at','desc')
    ->paginate(5);
    $lista = $list->toArray();
    $progress = function($x,$y,$max) {
      $v = 100/$y*$x;
      return $v < $max ? round($v) : $max;
    };
    ob_start();
    echo $list->links('vendor.pagination.app', ['onclick'=>$onclick,'from'=>$lista['from'] ?? 0,'to'=>$lista['to'] ?? 0]);
    $this->response['pagination'] = ob_get_clean();
    ob_start(); 
    if(sizeof($lista['data'])):
      foreach($list as $item): 
        $item->training_services = json_decode($item->training_services, true);
        if($item->type == 11):
          $item->medals = DB::table('orders_reports')->select('medal')->where('order_id', $item->id)->where('starter','=','0')->where('medal','>','0')->groupBy('medal')->get()->toArray();
        endif;
      ?>
        <div class="tracking-app-orders-list-item">
          <table>
            <tbody>
              <tr>
                <td>
                  <div class="tracking-app-orders-list-info">
                    <table>
                      <tr>
                        <td colspan="5">
                          <div class="tracking-app-orders-list-item-title tracking-app-flex">
                            <span><?= __('Заказ') ?> <?= $item->id ?>: <?= __($item->type_name) ?></span>&nbsp;
                            <?php if($item->type == 1 or $item->type == 10): ?>
                            <span class="tracking-app-color-red"><?= $item->mmr_start ?>><?= $item->mmr_finish ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 8): ?>
                            <span class="tracking-app-color-red"><?= __($item->grade_start) ?>><?= __($item->grade_finish) ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 2): ?>
                            <span class="tracking-app-color-red"><?= $item->cali_games_total ?> <?= __('игр') ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 7): ?>
                            <span class="tracking-app-color-red"><?= $item->cali_games_total ?> <?= __('побед') ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 4): ?>
                            <span class="tracking-app-color-red"><?= $item->training_hours ?> <?= __('ч.') ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 5): ?>
                            <span class="tracking-app-color-red"><?= $item->cali_games_total ?> <?= __('игр') ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 3): ?>
                            <span class="tracking-app-color-red"><?= __($item->rank_start) ?>><?= __($item->rank_finish) ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 9): ?>
                            <span class="tracking-app-color-red"><?= __($item->chess_start) ?>><?= __($item->chess_finish) ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 6): ?>
                            <span class="tracking-app-color-red"><?= __($item->cup_finish) ?></span>
                            <?php endif; ?>
                            <?php if($item->type == 11): ?>
                            <span class="tracking-app-color-red"><?= sizeof($item->training_services) ?></span>
                            <?php endif; ?>
                          </div>
                        </td>
                        <td>
                          <?php if($item->status == 5 or $item->status == 6): ?>
                          <div class="tracking-app-badge"><?= __($item->status_name) ?></div>
                          <?php elseif($item->status == 7 or $item->status == 8): ?>
                          <div class="tracking-app-badge tracking-app-badge-dark"><?= __($item->status_name) ?></div>
                          <?php else: ?>
                          <div class="tracking-app-badge tracking-app-badge-light"><?= __($item->status_name) ?></div>
                          <?php endif; ?>
                        </td>
                      </tr>
                      <tr>
                        <td><img class="tracking-app-orders-list-info-logo" src="<?= $this->cdn.'/games/'.$item->type_icon  ?>"></td>
                        <td>
                          <?php if($item->type == 1 or $item->type == 10): ?>
                          <h5><?= $item->mmr_start ?></h5>
                          <h6><?= __('Начальный ММР') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 8): ?>
                          <h5 style="font-size: 22px;"><?= __($item->grade_start) ?></h5>
                          <h6><?= __('Начальный рейтинг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 2): ?>
                          <h5><?= $item->mmr_start < 0 ? __('Первая калибровка') : $item->mmr_start ?></h5>
                          <h6><?= __('Начальный ММР') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 5 or $item->type == 7): ?>
                          <h5><?= $item->mmr_start ?></h5>
                          <h6><?= __('Текущий ММР') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 4): ?>
                          <h5><?= $item->training_hours_done ?></h5>
                          <h6><?= __('Часов сыграно') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 9): ?>
                          <h5 style="font-size: 22px;"><?= __($item->chess_start) ?></h5>
                          <h6><?= __('Начальный ранг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 3): ?>
                          <h5 style="font-size: 22px;"><?= __($item->rank_start) ?></h5>
                          <h6><?= __('Начальный ранг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 6): ?>
                          <h5 style="font-size: 22px;"><?= __($item->cup_current ?? 'Не выигран') ?></h5>
                          <h6><?= __('Текущий кубок') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 11): ?>
                          <h5><?= sizeof($item->medals) ?></h5>
                          <h6><?= __('Выполнено') ?></h6>
                          <?php endif; ?>
                        </td>
                        <td><h5 class="tracking-app-orders-list-info-arrows">&rsaquo;&rsaquo;&rsaquo;</h5></td>
                        <td>
                          <?php if($item->type == 1 or $item->type == 10): ?>
                          <h5><?= $item->mmr_finish ?></h5>
                          <h6><?= __('Конечный ММР') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 8): ?>
                          <h5 style="font-size: 22px;"><?= __($item->grade_finish) ?></h5>
                          <h6><?= __('Конечный рейтинг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 2): ?>
                          <h5><?= $item->cali_games_done ?></h5>
                          <h6><?= __('Игр сыграно') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 4): ?>
                          <h5><?= $item->training_hours ?></h5>
                          <h6><?= __('Часов заказано') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 5): ?>
                          <h5><?= $item->cali_games_total ?></h5>
                          <h6><?= __('Количество игр') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 7): ?>
                          <h5><?= $item->cali_games_total ?></h5>
                          <h6><?= __('Количество побед') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 9): ?>
                          <h5 style="font-size: 22px;"><?= __($item->chess_finish) ?></h5>
                          <h6><?= __('Конечный ранг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 3): ?>
                          <h5 style="font-size: 22px;"><?= __($item->rank_finish) ?></h5>
                          <h6><?= __('Конечный ранг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 6): ?>
                          <h5 style="font-size: 22px;"><?= __($item->cup_finish) ?></h5>
                          <h6><?= __('Желаемый кубок') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 11): ?>
                          <h5><?= sizeof($item->training_services) ?></h5>
                          <h6><?= __('Заказано') ?></h6>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php 
                            if($item->type == 1 or $item->type == 8 or $item->type == 10):
                              $item->progress = $progress($item->mmr_boosted, $item->mmr_finish - $item->mmr_start, 100);
                            elseif($item->type == 2 or $item->type == 5 or $item->type == 7):
                              $item->progress = $progress($item->cali_games_done, $item->cali_games_total, 100);
                            elseif($item->type == 4):
                              $item->progress = $progress($item->training_hours_done, $item->training_hours, 100);
                            elseif($item->type == 9 or $item->type == 3):
                              $item->progress = $progress($item->medal_current - $item->medal_start, $item->medal_finish - $item->medal_start, 100);
                            elseif($item->type == 6):
                              $item->progress = $progress($item->medal_current, $item->medal_finish, 100);
                            endif;
                          ?>
                          <div class="tracking-app-progress-bar tracking-app-progress-bar-lg"><span style="width: <?= $item->progress ?? 0 ?>%;"></span></div>
                        </td>
                        <td>
                          <?php if($item->type == 1 or $item->type == 10): ?>
                          <h5 class="tracking-app-color-red"><?= ($item->mmr_finish - ($item->mmr_start + $item->mmr_boosted)) > 0 ? ($item->mmr_finish - ($item->mmr_start + $item->mmr_boosted)) : 0 ?></h5>
                          <h6><?= __('Осталось ММР') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 8): ?>
                          <h5 style="font-size: 22px;" class="tracking-app-color-red"><?= __($item->grade_current) ?></h5>
                          <h6><?= __('Осталось') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 2): ?>
                          <h5 class="tracking-app-color-red"><?= ($item->cali_games_total - $item->cali_games_done) > 0 ? $item->cali_games_total - $item->cali_games_done : 0 ?></h5>
                          <h6><?= __('Игр осталось') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 4): ?>
                          <h5 class="tracking-app-color-red"><?= ($item->training_hours - $item->training_hours_done) > 0 ? $item->training_hours - $item->training_hours_done : 0 ?></h5>
                          <h6><?= __('Часов осталось') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 5 or $item->type == 7): ?>
                          <h5 class="tracking-app-color-red"><?= ($item->cali_games_total - $item->cali_games_done) > 0 ? $item->cali_games_total - $item->cali_games_done : 0 ?></h5>
                          <h6><?= __('Осталось') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 9): ?>
                          <h5 style="font-size: 22px;" class="tracking-app-color-red"><?= __($item->chess_current) ?></h5>
                          <h6><?= __('Текущий ранг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 3): ?>
                          <h5 style="font-size: 22px;" class="tracking-app-color-red"><?= __($item->rank_current) ?></h5>
                          <h6><?= __('Текущий ранг') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 6): ?>
                          <h5 style="font-size: 22px;"><?= __($item->cup_current ?? 'Не выигран') ?></h5>
                          <h6><?= __('Выигран') ?></h6>
                          <?php endif; ?>
                          <?php if($item->type == 11): ?>
                          <h5 class="tracking-app-color-red"><?= sizeof($item->training_services) - sizeof($item->medals) ?></h5>
                          <h6><?= __('Осталось') ?></h6>
                          <?php endif; ?>
                        </td>
                      </tr>
                    </table>
                  </div>
                </td>
                <td>
                    <button class="tracking-app-button tracking-app-button-white tracking-app-button-lg" onclick="_tracking_app_.orderDetails('<?= $item->id ?>')"><?= __('Подробнее') ?></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      <?php
      endforeach;
    else: 
    ?>
      <tr class="text-center fs20"><td colspan="99"><?= __('Нет данных') ?></td></tr>
    <?php
    endif;
    $this->response['html'] = ob_get_clean();
    if($list):
      $this->response['status'] = 'ok';
    else:
      $this->response = array_merge($this->response, $this->get_message(1));
    endif;
  }

  private function blackList() {
    $search = $this->request['search'] ?? '';
    $page = $this->request['page'] ?? 1;
    $list = BlackList::where('user_name','like',"%$search%")
    ->orWhere('facebook','like',"%$search%")
    ->orWhere('vkontakte','like',"%$search%")
    ->orWhere('discord','like',"%$search%")
    ->paginate(5);
    $lista = $list->toArray();
    ob_start(); 
    echo $list->links('vendor.pagination.default',['onclick'=>'_.blackListTable','from'=>$lista['from'] ?? 0,'to'=>$lista['to'] ?? 0]);
    $this->response['pagination'] = ob_get_clean();
    ob_start(); 
    if(sizeof($lista['data'])):
    foreach($list as $user): 
    ?>
        <tr>
            <td class="w20"><span class="details color-black"><?= strlen($user->user_name) ? $user->user_name : '-' ?></span></td>
            <td><span class="details color-black"><?= strlen($user->facebook) ? "<a href='$user->facebook' target='_blank'>".__('Перейти')."</a>" : '-' ?> / <?= strlen($user->vkontakte) ? "<a href='$user->vkontakte' target='_blank'>".__('Перейти')."</a>" : '-' ?></span></td>
            <td><span class="details color-black"><?= strlen($user->skype) ? $user->skype : '-' ?></span></td>
            <td><span class="details color-black"><?= strlen($user->discord) ? $user->discord : '-' ?></span></td>
            <td class="hidden-451"><span class="details color-black"><?= strlen($user->other) ? $user->other : '-' ?></span></td>
            <td onclick="$(this).parent().toggleClass('details-visible')"><a class="show-details"><b class="h-text"><?= __('Показать') ?></b><b class="v-text"><?= __('Скрыть') ?></b></a></td>
        </tr>
        <tr class="row-details">
            <td colspan="99"><?= strlen($user->reason) ? $user->reason : '-' ?></td>
        </tr>
    <?php
    endforeach;
    else: 
    ?>
      <tr class="text-center fs20"><td colspan="99"><?= __('Нет данных') ?></td></tr>
    <?php
    endif;
    $this->response['html'] = ob_get_clean();
    if($list):
      $this->response['status'] = 'ok';
    else:
      $this->response = array_merge($this->response, $this->get_message(1));
    endif;
  }

  private function partnerRequest() {
    $entity = PartnerRequest::create([
      'games' => is_array($this->request['games']) ? implode($this->request['games'], '|') : null,
      'service_link' => $this->request['service'] ?? null,
      'email' => $this->request['email'] ?? null,
      'username' => $this->request['username'] ?? null,
      'comment' => $this->request['comment'] ?? null,
    ]);
    if($entity):
      $this->response['status'] = 'ok';
      $this->response = array_merge($this->response, $this->get_message(40));
    else:
      $this->response = array_merge($this->response, $this->get_message(41));
    endif;
  }

  private function boosterRequest() {
    $entity = JobRequest::create([
      'game_id' => $this->request['game'] ?? 0,
      'username' => $this->request['username'] ?? null,
      'email' => $this->request['email'] ?? null,
      'nickname' => $this->request['nickname'] ?? null,
      'vkontakte' => $this->request['vkontakte'] ?? null,
      'facebook' => $this->request['facebook'] ?? null,
      'discord' => $this->request['discord'] ?? null,
      'skype' => $this->request['skype'] ?? null,
      'telegram' => $this->request['telegram'] ?? null,
      'exp' => $this->request['exp'] ?? 0,
      'exp_source' => $this->request['exp_source'] ?? null,
      'play_hours' => $this->request['play_hours'] ?? 0,
      'play_week' => $this->request['play_week'] ?? 0,
      'comment' => $this->request['comment'] ?? null,
    ]);
    if($entity):
      $this->response['status'] = 'ok';
      $this->response = array_merge($this->response, $this->get_message(40));
    else:
      $this->response = array_merge($this->response, $this->get_message(41));
    endif;
  }

  private function boostersTop() {
    $data = json_decode(file_get_contents($this->api.'/info/boosters-top?limit=5&locale='.$this->locale->name));
    $players = $data->players ?? [];
    $totals = $data->totals ?? '';
    $currency = $data->currency ?? '';
    if($totals):
      $this->response['total'] = number_format($totals->earned_total, 2);
      $this->response['currency'] = $totals->currency;
    endif;

    if($players):
      ob_start();
        foreach($players as $x => $player): 
      ?>
          <tr>
              <td><span class="position"><?= $x + 1 ?></span></td>
              <td class="w40px"><span class="avatar"><img src="<?= $this->cdn.'/avatars/'.$player->user_avatar ?>" alt="<?= $player->user_name ?>"></span></td>
              <td class="w30"><span class="username color-black"><?= $player->user_name ?></span></td>
              <td class="w30"><span class="money color-red"><?= number_format($player->earned_month, 2).' '.$player->currency_name ?></span></td>
              <td class="w20"><span class="money color-black"><?= number_format($player->earned_total, 2).' '.$player->currency_name ?></span></td>
          </tr>          
      <?php endforeach;
      $html = ob_get_clean();
      $this->response['status'] = 'ok';
      $this->response['html'] = $html;
    else:
      $this->response = array_merge($this->response, $this->get_message(1));
    endif;
  }

  private function blackListRequest(Request $request) {
    $entity = BlackListRequest::create([
      'username' => $this->request['username'] ?? null,
      'email' => $this->request['email'] ?? null,
      'comment' => $this->request['comment'] ?? null,
      'files' => null,
    ]);
    
    $path = getcwd()."/../../api.52gaming.net/storage/shared/";
    $files = [];
    foreach($request->file('files') as $file):
      $extension = $file->getClientOriginalExtension();
      $filename = 'bl'.uniqid().'.'.$extension;
      $file->move($path, $filename);
      $files[] = $filename;
    endforeach;

    if($entity):
      $entity->update(['files' => implode($files,'|')]);
      $this->response['status'] = 'ok';
      $this->response = array_merge($this->response, $this->get_message(40));
    else:
      $this->response = array_merge($this->response, $this->get_message(41));
    endif;    
  }

  public function __invoke(Request $request) {
    $this->request = $request->all();
    $this->request['ip'] = $request->ip();
    $this->request['referer'] =  $request->server('HTTP_REFERER');
    $this->request['guest_id'] = Session::get('guest_id');
    $this->request['user_id'] = Session::get('user_id');
    $this->request['time'] = date('Y-m-d H:i:s');
    if(in_array($this->request['action'], $this->guarded)):
      if(!SIGNEDIN) $this->request['action'] = null;
    endif;
    // sleep(1);
    switch ($this->request['action']):
      case 'boostersTop':
        $this->boostersTop();
      break;
      case 'boosterRequest':
        $this->boosterRequest();
      break;
      case 'partnerRequest':
        $this->partnerRequest();
      break;
      case 'blackList':
        $this->blackList();
      break;
      case 'lastOrders':
        $this->lastOrders();
      break;
      case 'blackListRequest':
        $this->blackListRequest($request);
      break;
      case 'match_history':
        $this->match_history();
      break;
      default:
        $this->response = $this->get_message();
      break;
    endswitch;

    return response()->json($this->response, 200);
  }

  private function get_message($code = 1) {
    return User::get_response($code);
  }
}
