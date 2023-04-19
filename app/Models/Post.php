<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Providers\ApiProvider;

class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    public $fillable = ['category_id','game_id','user_id','slug','tags','cover','title','preview','text','video','featured','publish','show_home','seo_indexing','seo_title','seo_keywords','seo_description','published_at','expires_at','created_at','updated_at'];


    static function featured(string $dbp = '', int $offset = 0, int $limit = 1) {
      $res = DB::select("
        SELECT posts.slug, posts.cover, posts.title, posts.preview, posts.text, posts.published_at, posts.tags,
        (SELECT count(id) from posts_comments WHERE post_id = posts.id) as comments
        FROM posts WHERE posts.publish = 1 AND posts.featured = 1 ORDER BY posts.published_at DESC LIMIT $offset, $limit");
      return !empty($res) ? $res : null;
    }

    static function get_list(string $dbp = '', $params = [], int $offset = 0, int $limit = 3, int $exclude = 0) {
      $conditions = "";
      if(isset($params['games']) && is_array($params['games'])) $params['games'] = array_map(function($e) { return addslashes($e); }, $params['games']);
      if(isset($params['category']) && is_array($params['category'])) $params['category'] = array_map(function($e) { return addslashes($e); }, $params['category']);
      if(isset($params['tags']) && $params['tags']) $conditions .= " AND (posts.tags LIKE '%".implode(" OR posts.tags LIKE %',", $params['tags'])."%')";
      if(isset($params['games']) && $params['games']) $conditions .= " AND games.name IN ('".implode("',", $params['games'])."')";
      if(isset($params['category']) && $params['category']) $conditions .= " AND categories.id IN ('".implode("','", $params['category'])."')";
      if(isset($params['months']) && $params['months']) $conditions .= " AND DATE_FORMAT(posts.published_at, '%m') IN ('".implode("','", $params['months'])."')";

      $res = DB::select("
        SELECT DISTINCT posts.slug, posts.cover, posts.title, posts.preview, posts.text, posts.published_at, posts.tags,
        (SELECT count(id) FROM posts_comments WHERE post_id = posts.id) as comments
        FROM posts 
        LEFT JOIN games AS games ON games.id = posts.game_id
        LEFT JOIN posts_categories AS categories ON categories.id = posts.category_id
        WHERE posts.publish = 1 AND posts.id <> $exclude $conditions ORDER BY posts.published_at DESC
      ");
      $posts = array_slice($res, $offset, $limit);
      $hidden = array_slice($res, $offset + $limit);
      foreach($posts as &$post) $post->tags = array_filter(explode('|', $post->tags));
      return (object)['list'=>$posts, 'found'=>sizeof($res), 'hidden'=>sizeof($hidden)];
    }

    static function archives() {
      return DB::select("
        SELECT months.month as date, (SELECT count(sub.id) FROM posts as sub WHERE DATE_FORMAT(sub.published_at, '%m-%Y') = months.month) as count 
        FROM (SELECT DATE_FORMAT(published_at, '%m-%Y') as month FROM posts GROUP BY month) as months
        ORDER BY date DESC 
      ");
    }

    static function categories() {
      return DB::select("SELECT * FROM posts_categories");
    }

    static function games() {
      return DB::select("SELECT * FROM games");
    }

    static function for_home(string $dbp = '') {
      $res = DB::select("
        SELECT DISTINCT posts.slug, posts.cover, posts.title, posts.published_at, posts.show_home,
        (SELECT count(id) FROM posts_comments WHERE post_id = posts.id) as comments
        FROM posts 
        WHERE posts.publish = 1
        ORDER BY posts.published_at DESC, posts.show_home DESC
        LIMIT 3
      ");
      return $res;
    }

    static function get_post(string $dbp = '', string $slug = '') {
      $res = collect(DB::select("
        SELECT posts.id, posts.slug, posts.cover, posts.title, posts.preview, posts.text, posts.published_at, posts.tags,
        posts.seo_indexing, posts.seo_title, posts.seo_keywords, posts.seo_description,
        (SELECT count(id) FROM posts_comments WHERE post_id = posts.id) as comments
        FROM posts 
        LEFT JOIN posts_games AS games ON games.id = posts.game_id
        LEFT JOIN posts_categories AS categories ON categories.id = posts.category_id
        WHERE posts.publish = 1 AND posts.slug = '$slug' ORDER BY posts.published_at DESC
        LIMIT 1
      "))->first();
      if($res) $res->tags = array_filter(explode('|', $res->tags));
      return $res;
    }

    static function get_comments(int $post_id) {
      $res = DB::select("
        SELECT com.id, com.text, com.created_at, com.marked, com.reply_to, users.nick_name as author_name, users.avatar as author_avatar 
        FROM posts_comments as com
        LEFT JOIN users ON users.id = com.user_id
        WHERE com.post_id = $post_id AND com.publish = 1 ORDER BY com.created_at DESC
      ");
      return $res;      
    }

    static function comments_html($list, $reply_to = 0) {
      $result = '';
      foreach($list as $item):    
        if($item->reply_to == $reply_to):
          $nested = self::comments_html($list, $item->id);
          $replies = object_filter($list, function($e) use ($item) { return $e->reply_to == $item->id; } );
          $date = \Carbon\Carbon::parse($item->created_at);
          ob_start(); ?>
          <div class="item <?= $item->marked ? 'marked' : '' ?>">
              <div class="avatar"><div class="image"><img src="<?= ApiProvider::avatar($item->author_avatar) ?? url('/public/img/users/mock.png') ?>"></div></div>
              <div class="text">
                  <span class="user"><?= $item->author_name ?></span>
                  <span class="date"><?= $date->diffInHours() < 24 ? $date->diffForHumans() : $date->format('j.n.Y H:i'); ?></span>
                  <p class="comment"><?= $item->text ?></p>
                  <div class="controls">
                      <span class="comments-count"><?= sizeof($replies) ?></span>
                      <span class="show" data-reverse-text="<?= __('скрыть') ?>"><?= __('показать') ?></span>
                      <span class="reply"><?= __('ответить') ?></span>
                  </div>
                  <div class="add-reply">
                      <div class="flex">
                          <label class="column"><input type="text" placeholder="<?= __('Ваше сообщение') ?>:"></label>
                          <div class="column shrink">
                            <button class="btn btn-blue" onclick="_.add_comment(event, <?= $item->id ?>)">
                              <b class="process"></b><b class="onsubmit"><?= __('Отправить') ?></b><b class="onprocess"><?= __('Отправка') ?></b>
                            </button>
                        </div>                                      
                      </div>
                  </div>
                  <div class="replies"><?= strlen($nested) ? $nested : '<h6>'.__('Нет ответов').'</h6>' ?></div>
              </div>
          </div> 
          <?php         
          $result .= ob_get_clean();
        endif;
      endforeach;
      return $result;
    }

    static function to_html($posts = [], $_locale) {
      ob_start();
      foreach($posts as $item): ?>
        <div class="post col-xs-6 col-sm-6 col-md-4">
            <div class="post-item">
                <div class="image" style="background-image: url(<?= url('/public/img/posts/' . $item->cover) ?>)"></div>
                <div class="footer">
                    <h5 class="txt-hover-blue"><a href="<?= url($_locale->prefix . '/novosti/' . ltrim($item->slug, '/')) ?>"><?= $item->title ?></a></h5>
                    <div class="info">
                        <span class="date">
                            <i class="icon icon-clock"></i> 
                            <?php $date = \Carbon\Carbon::parse($item->published_at);
                                echo $date->diffInHours() < 24 ? $date->diffForHumans() : $date->format('j.n.Y H:i'); ?>
                        </span>
                        <span class="comments"><i class="icon icon-commenting-o"></i> <?= $item->comments ?> </span>
                    </div>
                    <a href="<?= url($_locale->prefix . '/novosti/' . ltrim($item->slug, '/')) ?>" class="btn btn-red runner"><?= __('Подробнее') ?> <i class="icon icon-angle-right"></i></a>
                </div>
            </div>
        </div>  
      <?php
      endforeach;
      return ob_get_clean();
    }
}
