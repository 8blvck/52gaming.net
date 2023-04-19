<?php
namespace App\Providers;

use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Translation\FileLoader;
use Cache;

class LocaleTranslationLoader extends TranslationServiceProvider
{
    public function load($locale, $group, $namespace = null) {
        if ($namespace !== null && $namespace !== '*') {
            
        }
        return Cache::remember("locale.fragments.{$locale}.{$group}", 60, function () use ($group, $locale) {
            return [$group, $locale];
        });
    }
}