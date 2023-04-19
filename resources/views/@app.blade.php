<!DOCTYPE html>
<html lang="{{ $_locale->name }}">
  <head>
    <title>{{ __($_page->seo_title) }}</title>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0"/>
    <meta name="format-detection" content="telephone=no">
    <meta name="robots" content="{{ intval($_page->seo_indexing) ? 'index, follow' : 'noindex, nofollow' }}" />
    <meta name="keywords" content="{{ __($_page->seo_keywords) }}" />
    <meta name="description" content="{{ __($_page->seo_description) }}" />
    <meta name="author" content="dynamite" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ url('/public/css/_.css?v='.microtime()) }}" type="text/css" />
    <link rel="stylesheet" href="{{ url('/public/css/slick.css?v='.microtime()) }}" type="text/css" />
    <link rel="stylesheet" href="{{ url('/public/css/bootstrap.css?v='.microtime()) }}" type="text/css" />
    <link rel="stylesheet" href="{{ url('/public/css/fonts.css?v='.microtime()) }}" type="text/css" />
    <link rel="shortcut icon" type="image/ico" href="{{ url('/public/favicon.ico') }}"/>
    <link rel="apple-touch-icon" sizes="57x57" href="{{ url('/public/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ url('/public/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ url('/public/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ url('/public/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ url('/public/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ url('/public/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ url('/public/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ url('/public/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/public/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192"  href="{{ url('/public/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/public/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ url('/public/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('/public/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ url('/public/manifest.json') }}">
    <meta name="msapplication-TileImage" content="{{ url('/public/ms-icon-144x144.png') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    {!! $_page->seo_head_scripts !!}
  </head>
  <body class="{{ $_bodyclass }}">
    {!! $_page->seo_body_scripts !!}
    @include('elements.header')
    @yield('content')
    @include('elements.footer')
    <script src="{{ url('/public/js/jquery.js') }}"></script>
    <script src="{{ url('/public/js/bootstrap.js') }}"></script>
    <script src="{{ url('/public/js/slick.js') }}"></script>
    <script src="{{ url('/public/js/_.js') }}"></script>
    {!! $_page->seo_after_body_scripts !!}
  </body>
</html>
