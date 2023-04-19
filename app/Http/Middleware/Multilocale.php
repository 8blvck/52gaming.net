<?php
namespace App\Http\Middleware;
use Exception;
use Closure;
use Session;
use Config;
use App;

class Multilocal
{
  public function handle($request, Closure $next)
  {
      $locale_default = Config::get('app.fallback_locale');
      $locales_config = Config::get('app.locales');
      if(!$locales_config) {
        throw new Exception("You must specify locales array in your_project_root/config/app.php. Example: 'locales' => [
          'ru' => ['title' => 'ru', 'prefix'=>'ru', 'db_prefix' => 'ru_', 'locale' => 'ru_RU.utf8', 'icon' => '/img/locals/ru.png'],
          'en' => ['title' => 'en', 'prefix'=>'en', 'db_prefix' => 'en_', 'locale' => 'en_EN.utf8', 'icon' => '/img/locals/en.png'],
        ]");
      }

      $locales = array_keys( $locales_config );
      $url_parsed = parse_url($request->fullUrl());
      $query = isset($url_parsed['query']) ? '?'.$url_parsed['query'] : '';
      $path_array = explode('/', rtrim($request->path(), '/'));
      $path = implode('/', array_diff($path_array, $locales)) . $query;
      $localeInUrl = in_array( $request->segment(1), $locales )
        ? $request->segment(1)
        : false;

      /* добываем язык юзера */
      if( Session::has('multilocal.set_locale') ):
          $locale = strtolower( Session::get('multilocal.set_locale') );
          Session::forget('multilocal.set_locale');
      elseif( $localeInUrl ):
          $locale = $localeInUrl;
      else:
          $locale = Session::has('multilocal.locale')
            ? Session::get('multilocal.locale')
            : $locale_default;
      endif;

      /* если язык дефолтный - не добавляем в url */
      $localeSlug = ( $locale != $locale_default ? $locale : '' );

      /* редиректим на новый url если необходимо */
      if( (!$localeInUrl && $locale != $locale_default) ||
           ($localeInUrl && $locale == $locale_default) ||
           ($localeInUrl && $locale != $localeInUrl) ) :
         $redirectTo = (!empty($localeSlug) ? '/'.$localeSlug : '').(!empty($path) ? '/'.$path : '');
         return redirect($redirectTo);
      endif;

      Session::put('multilocal.locale', $locale);

      $_language = $this->cast_to_object($locales_config[$locale]);
      $_languages = $this->cast_to_object($locales_config);

      view()->share('_language', $_language);
      view()->share('_languages', $_languages);
      return $next($request);
  }

  private function cast_to_object($array) {
    $o = new \stdClass;
    foreach($array as $k => $v):
       if(strlen($k)):
          if(is_array($v)):
             $o->{$k} = $this->cast_to_object($v);
          else:
             $o->{$k} = $v;
          endif;
       endif;
    endforeach;
    return $o;
  }

  private function cast_to_array($object) {
    $o = [];
    foreach($object as $k => $v):
       if(strlen($k)):
         $o[$k] = is_object($v) ? $this->cast_to_array($v) : $v;
       endif;
    endforeach;
    return $o;
  }

}
