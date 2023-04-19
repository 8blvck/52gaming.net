<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Crypt;
use Cookie;
use Session;
use App;
use Config;
use Route;
use Request;
use stdClass;
use App\Models\Page;
use App\Models\User;
use App\Models\Main;
use App\Models\MailTemplates;
use App\Models\Settings;
use App\Models\oAuth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $api_ws = 'wss://api.52gaming.net';
    public $api = 'https://api.52gaming.net/v2/';
    public $cdn = 'https://api.52gaming.net/storage/';
    public $locale;
    public $locales;
    public $db_prefix;
    public $page_class;
    public $user;
    public $action;
    public $domain;
    public $trailing;
    public $path;
    public $route;
    public $socials;
    public $header = [];
    public $footer = [];
    public $page = [];
    public $currency = ['sign'=>'₽','name'=>'руб','abbr'=>'р.'];
    public $translations = [];

    function __construct() {
      $this->translations = ["янв","фев","мар","апр","май","июн","июл","авг","сен","окт","ноя", "дек","Января","Февраля","Марта","Апреля","Мая","Июня","Июля","Августа","Сентября","Октября","Ноября","Декабря","Бустер"];
      if(Session::has('signout')):
        $this->signout();
      else:
        if(Session::has('remember_me')):
          Cookie::queue(Cookie::make('remember_me', Session::get('remember_me'), 43200));
          Session::remove('remember_me');
        endif;
        if(!Session::get('user_id') && Cookie::get('remember_me')):
          $user_cooked = (int)Crypt::decrypt(Cookie::get('remember_me'));
          if($user_cooked) Session::put('user_id', $user_cooked);
        endif;
      endif;

      $controller = explode('@', Route::currentRouteAction());
      $locale = App::getLocale();
      $segments = array_filter(Request::segments(), function($e) use ($locale) { return $e != $locale; });
    	$this->locales = Config::get('app.locales');
    	$this->locale = $this->locales->{$locale};
    	$this->db_prefix = $this->locale->db_prefix;
      $this->domain = Request::root();
      $this->route = trim(Route::currentRouteName());
      $this->action = end($controller);
      $this->path = ltrim(str_replace_first($this->locale->prefix, null, Request::path()), '/');
      $this->trailing = Request::segments();
      $this->trailing = array_pop($segments) ?: '/';
      $this->page = Page::where('slug', $this->trailing)->get()->first();
      $this->page = $this->page ? $this->page : (object)['seo_indexing'=>0,'seo_title'=>null,'seo_keywords'=>null,'seo_description'=>null,'seo_text'=>null,'title'=>null];
      view()->share('_locale', $this->locale);
      view()->share('_locales', $this->locales);
      view()->share('_route', $this->route);
      view()->share('_action', $this->action);
      view()->share('_path', $this->path);
      view()->share('_trailing', $this->trailing);
      view()->share('_query', Request::query() ? '?'.http_build_query(Request::query()) : '');
      view()->share('_domain', $this->domain);
      view()->share('_cdn', $this->cdn);
      view()->share('_translations', $this->translations);
      view()->share('_urisegments', array_values($segments));
      view()->share('_oauth', (object)[
        'discord'=>oAuth::discord_auth_uri(),
        'facebook'=>oAuth::facebook_auth_uri(),
        'vkontakte'=>oAuth::vkontakte_auth_uri(),
      ]);
      view()->share('_currency', (object)$this->currency);
      \Carbon\Carbon::setLocale(App::getLocale());
    }

    public function beforeRender() {
      $seo_settings = Settings::values(['seo_indexing','seo_title','seo_keywords','seo_description','seo_head_scripts','seo_body_scripts','seo_after_body_scripts','copyrights']);
      $this->page->seo_indexing = !intval($seo_settings->seo_indexing) ? 0 : $this->page->seo_indexing;
      $this->page->seo_title = !strlen(trim($this->page->seo_title)) ? $seo_settings->seo_title : $this->page->seo_title;
      $this->page->seo_keywords = !strlen(trim($this->page->seo_keywords)) ? $seo_settings->seo_keywords : $this->page->seo_keywords;
      $this->page->seo_description = !strlen(trim($this->page->seo_description)) ? $seo_settings->seo_description : $this->page->seo_description;
      $this->page->seo_head_scripts = $seo_settings->seo_head_scripts;
      $this->page->seo_body_scripts = $seo_settings->seo_body_scripts;
      $this->page->seo_after_body_scripts = $seo_settings->seo_after_body_scripts;
      $this->page->copyrights = $seo_settings->copyrights;
      $tempvars['_page'] = $this->page;
      $tempvars['_bodyclass'] = $this->page_class;
      $tempvars['_contacts'] = Settings::values(['vkontakte','facebook','instagram']);
      view()->share($tempvars);
    }

    public function signout() {
      Session::put('user_id', 0);
      Session::remove('remember_me');
      Session::remove('signout');
      Cookie::queue(Cookie::forget('remember_me'));  
    }
}
