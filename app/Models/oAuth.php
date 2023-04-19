<?php

namespace App\Models;

use Session;

class oAuth
{
  const OAUTH_URI = 'https://fastboosting.ru/oauth';
  // const OAUTH_URI = 'https://localhost/fastboosting.new/oauth';
  const DISCORD_REDIRECT_URI = self::OAUTH_URI . '/discord';
  const DISCORD_CDN_ENDPOINT = 'https://cdn.discordapp.com';
  const DISCORD_API_ENDPOINT = 'https://discordapp.com/api/v6';
  const DISCORD_CLIENT_ID = '494161890017017857';
  const DISCORD_CLIENT_SECRET = 'MGXLJeGpkAu4-J83NNjmaZdC-IE4vFiW';
  const DISCORD_SCOPES = 'identify email connections';

  static function discord_auth_uri() {
    return "https://discordapp.com/api/oauth2/authorize?".http_build_query([
      'client_id'     => self::DISCORD_CLIENT_ID,
      'redirect_uri'  => self::DISCORD_REDIRECT_URI,
      'scope'         => self::DISCORD_SCOPES,
      'response_type' => 'code',
      'state'         => Session::get('oauth state')
    ], null, '&', PHP_QUERY_RFC3986);
  }

  static function discord_exchange_code($code) {
      $data = [
          'grant_type'    => 'authorization_code',
          'client_id'     => self::DISCORD_CLIENT_ID,
          'client_secret' => self::DISCORD_CLIENT_SECRET,
          'redirect_uri'  => self::DISCORD_REDIRECT_URI,
          'scope'         => self::DISCORD_SCOPES,
          'code'          => $code
      ];
      $req = self::request(self::DISCORD_API_ENDPOINT.'/oauth2/token', $data);
      $user = self::discord_get_user($req);
      if($user && isset($user['email'])):
        $entity = [];
        $entity['name']       = $user['username'];
        $entity['email']      = $user['email'];
        $entity['discord']    = $user['username'].'#'.$user['discriminator'];
        $entity['registered_as'] = 'discord#'.$user['id'];
        if($user['avatar']):
          $path = self::DISCORD_CDN_ENDPOINT . '/avatars/'.$user['id'].'/'.$user['avatar'].'.png';
          $content = @file_get_contents($path);
          $entity['avatar'] = [
            'name' => basename(strtok($path,'?')),
            'data' => base64_encode($content),
          ];
        endif;
        $user = $entity;
      else:
        $user = null;
      endif;
      return $user;
  }

  static function discord_get_user($req) {
      $user = [];
      if($req):
          $token_value = $req['access_token'];
          $token_type = $req['token_type'];
          $headers = [
              "Authorization: $token_type $token_value", 
              "Content-Type: application/x-www-form-urlencoded"
          ];
          $user = self::request(self::DISCORD_API_ENDPOINT.'/users/@me', false, $headers, 'get');
      endif;
      return $user;
  }

  static function discord_refresh_token($data) {
      $data['refresh_token'] = $req['refresh_token'];
      $data['grant_type'] = 'refresh_token';
      return self::request(self::DISCORD_API_ENDPOINT.'/oauth2/token', $data);
  }

  const FACEBOOK_REDIRECT_URI = self::OAUTH_URI . '/facebook';
  const FACEBOOK_CLIENT_ID = '254440578546966';
  const FACEBOOK_CLIENT_SECRET = '2652d81d0792cb12c8b414b1f99c290d';
  const FACEBOOK_API_ENDPOINT = 'https://graph.facebook.com';

  static function facebook_auth_uri() {
    return "https://www.facebook.com/dialog/oauth?".http_build_query([
      'client_id'     => self::FACEBOOK_CLIENT_ID,
      'redirect_uri'  => self::FACEBOOK_REDIRECT_URI,
      'response_type' => 'code',
      'scope'         => 'email'
    ], null, '&', PHP_QUERY_RFC3986);
  }

  static function facebook_exchange_code($code) {
      $data = [
        'client_id'     => self::FACEBOOK_CLIENT_ID,
        'client_secret' => self::FACEBOOK_CLIENT_SECRET,
        'redirect_uri'  => self::FACEBOOK_REDIRECT_URI,
        'code'          => $code
      ];
      $req = self::request(self::FACEBOOK_API_ENDPOINT.'/oauth/access_token', $data, false, 'get');
      $user = self::facebook_get_user($req);
      if($user && isset($user['email'])):
        $entity = [];
        $entity['name']       = $user['first_name'] . ' ' . $user['last_name'];
        $entity['email']      = $user['email'];
        $entity['facebook']   = null; // according to profiles abuse the links are not publishing anymore
        $entity['registered_as'] = 'facebook#'.$user['id'];
        if(isset($user['picture']) && isset($user['picture']['data'])):
          $path = $user['picture']['data']['url'];
          $content = @file_get_contents($path);
          $entity['avatar'] = [
            'name' => basename(strtok($path,'?')) . exif_format($path),
            'data' => base64_encode($content),
          ];
        endif;
        $user = $entity;
      else:
        $user = null;
      endif;
      return $user;
  }

  static function facebook_get_user($req) {
      $user = [];
      if($req):
          $token_value = $req['access_token'];
          $token_type = $req['token_type'];
          $data = [ 'access_token'=>$token_value, 'fields'=>'id,first_name,last_name,picture,email'];
          $res = self::request(self::FACEBOOK_API_ENDPOINT.'/me', $data, false, 'get');
          $user = is_array($res) ? $res : $user; 
      endif;
      return $user;
  }

  const VKONTAKTE_REDIRECT_URI = self::OAUTH_URI . '/vkontakte';
  const VKONTAKTE_CLIENT_ID = '6703055';
  const VKONTAKTE_CLIENT_SECRET = 'wqWexkpdqpVJClg00jgw';
  const VKONTAKTE_OAUTH_ENDPOINT = 'https://oauth.vk.com';
  const VKONTAKTE_API_ENDPOINT = 'https://api.vk.com/method';

  static function vkontakte_auth_uri() {
    return self::VKONTAKTE_OAUTH_ENDPOINT."/authorize?".http_build_query([
      'client_id'     => self::VKONTAKTE_CLIENT_ID,
      'redirect_uri'  => self::VKONTAKTE_REDIRECT_URI,
      'response_type' => 'code',
      'display'       => 'page',
      'scope'         => 'email',
      'v'             => '5.85',
    ], null, '&', PHP_QUERY_RFC3986);
  }

  static function vkontakte_exchange_code($code) {
      $data = [
        'client_id'     => self::VKONTAKTE_CLIENT_ID,
        'client_secret' => self::VKONTAKTE_CLIENT_SECRET,
        'redirect_uri'  => self::VKONTAKTE_REDIRECT_URI,
        'code'          => $code
      ];
      $req = self::request(self::VKONTAKTE_OAUTH_ENDPOINT.'/access_token', $data, false, 'get');
      $user = self::vkontakte_get_user($req);
      if($user && isset($user['email'])):
        $entity = [];
        $entity['name']       = $user['first_name'] . ' ' . $user['last_name'];
        $entity['email']      = $user['email'];
        $entity['vkontakte']   = 'https://vk.com/id'.$user['id'];
        $entity['registered_as'] = 'vkontakte#'.$user['id'];
        if(isset($user['photo_100'])):
          $path = $user['photo_100'];
          $content = @file_get_contents($path);
          $entity['avatar'] = [
            'name' => basename(strtok($path,'?')),
            'data' => base64_encode($content),
          ];
        endif;
        $user = $entity;
      else:
        $user = null;
      endif;
      return $user;
  }

  static function vkontakte_get_user($req) {
      $user = null;
      if($req):
          $token_value = $req['access_token'];
          $user_id = $req['user_id'];
          $data = [ 'user_ids'=>$user_id,'fields'=>'email,first_name,last_name,photo_100','v'=>'5.85','access_token'=>$token_value ];
          $res = self::request(self::VKONTAKTE_API_ENDPOINT.'/users.get', $data, false, 'get');
          $user = is_array($res['response'] ?? null) ? array_pop($res['response']) : $user; 
          if($user && ($req['email'] || $user['email'])) $user['email'] = $res['email'] ?? $req['email'];
      endif;
      return $user;
  }

  static function request($url, $data, $headers = [], $method = 'post') {
      if(!$headers) $headers = ['Content-Type: application/x-www-form-urlencoded'];
      $options['http'] = [];
      $options['http']['header']  = implode("\r\n", $headers);
      $options['http']['method']  = strtoupper($method);
      if($method == 'post'):
        $options['http']['content'] = $data ? http_build_query($data) : null;
      endif;
      if($method == 'get'):
        $url = $url . '?' . ($data ? http_build_query($data) : null);
      endif;
      $context = stream_context_create($options);
      $result = @file_get_contents($url, false, $context);
      $proc = $result ? json_decode($result, true) : $result;
      if(is_array($proc)) {
        $proc['url'] = $url;
        $proc['context'] = $context;
      }
      return $proc;
  }

}
