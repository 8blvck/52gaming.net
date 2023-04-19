<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Config;
use Request;
use App;
use Session;
use Exception;

class LocaleServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        $this->match(true);
    }

    protected function match($ael = false)
    {
        $locale = Config::get('app.locale');
        $locale_default = $this->client_locale();
        $locales_config = (array) Config::get('app.locales');

        if(!$locales_config):
            throw new Exception("You should specify config.locales in /config/app.php");
        endif;

        if(!isset($locales_config[$locale_default])):
            $locale_default = 'en';
        endif;

        $locales = array_keys($locales_config);
        $locale_in_url = Request::segment(1);

        if(in_array($locale_in_url, $locales)):
            $locale = $locale_in_url;
            $locale_to_route = $locale;
        else:
            $locale = $locale_default;
            $locale_to_route = $ael ? '' : $locale;
        endif;
        
        App::setLocale($locale);
        Session::put('locale', $locale);
        Config::set('route_prefix', $locale_to_route);
    }

    protected function client_locale() {
        preg_match_all(
           '/([a-z]{1,8})' .       // M1 - first part of language e.g en
           '(-[a-z]{1,8})*\s*' .   // M2 - other parts of language e.g -us
           '(;\s*q\s*=\s*((1(\.0{0,3}))|(0(\.[0-9]{0,3}))))?/i', // M4 - quality Factor
           $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null, $langParse);
        $langs = $langParse[1];
        return $langs[0] ?? Config::get('app.fallback_locale');
    }

}
