<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class OurStaff extends Model
{
    protected $table = 'our_staff';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = ['name','heroes','user_id','mmr','country','rating','avatar'];

    static function get_list(string $dbp = '', int $offset = 0, int $limit = 3, int $exclude = 0) {
      $res = self::where('display', 1)->orderBy('rating','desc')->get()->toArray();
      $list = array_slice($res, $offset, $limit);
      foreach($list as &$item):
        $item = (object)$item;
      	$item->heroes = json_decode($item->heroes) ?? [];
      	$item->lanes = json_decode($item->lanes) ?? [];
      endforeach;
      $hidden = array_slice($res, $offset + $limit);
      return (object)['list'=>$list, 'found'=>sizeof($res), 'hidden'=>sizeof($hidden)];
    }

    static function to_html($data = [], $_locale) {
      ob_start();
      foreach($data as $item): ?>
      <div class="item col-xs-12 col-sm-4">
        <div class="our-boosters--item">
          <div class="avatar" style="background-image: url(<?= is_url($item->avatar) ? $item->avatar : url('/public/img/users/' . $item->avatar) ?>)"></div>
          <h5 class="name"><?= $item->name ?></h5>
          <table class="info-table">
            <tr>
              <td><?= __('Страна') ?>:</td>
              <td><b><?= __($item->country) ?></b></td>
            </tr>
            <tr>
              <td><?= __('Кол-во MMR') ?>:</td>
              <td><b><?= $item->mmr ?></b></td>
            </tr>
            <tr>
              <td><?= __('Рейтинг') ?>:</td>
              <td><div class="rating r-<?= $item->rating ?? 5 ?>"><i></i><i></i><i></i><i></i><i></i></div></td>
            </tr>
          </table>
          <div class="separator"></div>
          <button class="btn btn-down"><i class="hidden-sm"><?= __('Показать') ?></i> <?= __('Подробности') ?></button>
          <div class="our-boosters--footer">
            <h6><?= __('Лучшие герои') ?></h6>
            <div class="best-heroes-list">
              <?php foreach($item->heroes as $hero): ?>
              <i class="image" style="background-image: url(<?= ApiProvider::cdn_heroes.$hero.'_sm.png'?>);"></i>
              <?php endforeach; ?>
            </div>
            <div class="separator"></div>
            <h6><?= __('Знание линий') ?></h6>
                <div class="charts"> 
                  <?php foreach($item->lanes as $lane): ?>
                  <div class="charts-item">
                      <div class="chart-presenter">
                        <canvas class="chart-canvas" width="60" height="60" data-fill="<?= $lane->value ?>"></canvas>
                        <div class="value"><?= $lane->value ?>%</div>
                      </div>
                    <div class="title"><?= _($lane->name) ?></div>
                  </div>
                  <?php endforeach ?>
              </div>
            <button class="btn btn-down up"><i class="hidden-sm"><?= __('Скрыть') ?></i> <?= __('Подробности') ?></button>
          </div>    
        </div>
      </div> 
      <?php
      endforeach;
      return ob_get_clean();
    }

}
