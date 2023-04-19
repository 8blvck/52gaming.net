<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Http\Providers\ApiProvider;
use App\Models\MailTemplates;
use App\Models\Main;
use Request;
use Lang;

class User extends Authenticatable
{
  protected $table = 'users';
  protected $primaryKey = 'id';
  public $incrementing = true;
  public $timestamps = false; 
  protected $fillable = [
    'sysid', 'login', 'email', 'password', 'avatar', 'first_name', 'last_name', 'nick_name', 'birth_date', 'language', 'country', 'city', 'zip', 'phone', 'skype', 'discord', 'vkontakte', 'facebook', 'instagram', 'youtube', 'twitter', 'dotabuff', 'is_approved', 'is_blocked', 'is_subscribed', 'is_configured', 'type', 'heroes', 'lanes', 'mmr_solo', 'mmr_party', 'created_at', 'updated_at', 'referrer_hash', 'referred_by', 'recovery_hash', 'registered_as',
  ];
  protected $hidden = [
    'permissions', 'order_permissions', 'deposit', 'rating', 'rating_id', 'currency_id', 'referrer_hash', 'referred_by', 'recovery_hash',
  ];

  static function register($data) {
    $result = ['code'=>0,'error'=>null,'user_id'=>0];
    $data['password'] = isset($data['password']) ? $data['password'] : Main::randstr(10);
    $response = ApiProvider::register($data);
    $result['code'] = $response['code'];
    if($response['code'] == 200):
        $user = $response['user'];
        $user['password'] = md5($data['password']);
        $user['login'] = $data['email'];
        $user['discord'] = isset($data['discord']) ? $data['discord'] : null;
        $user['facebook'] = isset($data['facebook']) ? $data['facebook'] : null;
        $user['vkontakte'] = isset($data['vkontakte']) ? $data['vkontakte'] : null;
        $user['is_approved'] = isset($user['is_approved']) ? $user['is_approved'] : 0;
        $user['registered_as'] = isset($data['registered_as']) ? $data['registered_as'] : 'common';
        $entity = self::create($user);
        if($entity):
            $result['user_id'] = $entity->id;
            $activation_uri = Request::root().'/verify/'.$entity->recovery_hash;
            if($entity->is_approved):
                MailTemplates::build('auth_register', $entity->login, [
                  'username'=>$entity->nick_name,
                  'user_email'=>$data['email'],
                  'user_password'=>$data['password'],
                ]);  
            else:
                MailTemplates::build('auth_activation', $entity->login, [
                  'username'=>$entity->nick_name,
                  'activation_uri'=>$activation_uri,
                ]);                  
            endif; 
        endif;  
    else:
        $result['error'] = $response['error'] ?? 'internal_error';
    endif;
    return $result; 
  }

  static function get_response($code = 1) {
    $responses = [
        1 => ['title'=>'', 'message'=>'Что-то пошло не так как запланировано'],
        2 => ['title'=>'', 'message'=>'Ваш аккаунт создан, на email отправлено письмо с инструкцией по его активации'],
        3 => ['title'=>'', 'message'=>'Ваш email определен как недействительный, попробуйте другой'],
        4 => ['title'=>'', 'message'=>'Ваш пароль не может быть короче 5 символов'],
        5 => ['title'=>'', 'message'=>'Но пользователь с этим email уже зарегистрирован в системе'],
        6 => ['title'=>'', 'message'=>'Но пользователь не найден'],
        7 => ['title'=>'', 'message'=>'Вы успешно авторизованы в системе'],
        8 => ['title'=>'', 'message'=>'Владелец этого аккаунта не подтвержден, инструкция по активации была выслана на Ваш email при регистрации'],
        9 => ['title'=>'', 'message'=>'Этот аккаунт заблокирован администрацией сайта'],
        10 => ['title'=>'', 'message'=>'На Ваш email отправлено письмо с инструкцией по восстановлению пароля'],
        11 => ['title'=>'', 'message'=>'Ваш комментарий будет опубликован после модерации'],
        12 => ['title'=>'', 'message'=>'Операция на данный момент недоступна'],
        13 => ['title'=>'', 'message'=>'Настройки аккаунта изменены'],
        14 => ['title'=>'', 'message'=>'Операция на данный момент недоступна'],
        15 => ['title'=>'', 'message'=>'Ваш пароль сменен'],
        16 => ['title'=>'', 'message'=>'Но длина пароля не может быть короче 5 символов'],
        17 => ['title'=>'', 'message'=>'Вы ввели не верный пароль'],
        18 => ['title'=>'', 'message'=>'Операция на данный момент недоступна'],
        19 => ['title'=>'', 'message'=>'На email отправлено письмо с инструкцией по подтверждению прав владения'],
        20 => ['title'=>'', 'message'=>'Новый email эквивалентен Вашему, его не зачем менять'],
        21 => ['title'=>'', 'message'=>'По неизвестным причинам ваш аккаунт не найден'],
        22 => ['title'=>'', 'message'=>'Ваш тикет создан'],
        23 => ['title'=>'', 'message'=>'Операция на данный момент недоступна'],
        24 => ['title'=>'', 'message'=>''],
        25 => ['title'=>'', 'message'=>'Промокод не действителен'],
        26 => ['title'=>'', 'message'=>'но сервис временно не доступен'],
        27 => ['title'=>'', 'message'=>'Информация о Вашем балансе недоступна'],
        28 => ['title'=>'', 'message'=>'Суммы на вашем счету недостаточно для оплаты'],
        29 => ['title'=>'', 'message'=>'Заказ создан'],
        30 => ['title'=>'', 'message'=>'Используйте настоящий email адресс'],
        31 => ['title'=>'', 'message'=>'Вы уже подписаны на это оповещение'],
        32 => ['title'=>'', 'message'=>'Мы сообщим Вам о изменениях работы сервиса'],
        33 => ['title'=>'', 'message'=>'Вы будете перенаправлены автоматически'],
        34 => ['title'=>'', 'message'=>'Сумма пополнения не может быть меньше 1$'],
        35 => ['title'=>'', 'message'=>'Данные изменены'],
        36 => ['title'=>'', 'message'=>'Сообщение слишком короткое'],
        37 => ['title'=>'', 'message'=>'Но Ваш заказ не найден'],
        38 => ['title'=>'', 'message'=>'Вы уже оставляли отзыв по этому заказу'],
        39 => ['title'=>'', 'message'=>'Вы сможете добавить отзыв когда заказ будет полностью готов'],
        40 => ['title'=>'', 'message'=>'Мы получили Вашу заявку и обязательно свяжемся после ее рассмотрения'],
        41 => ['title'=>'', 'message'=>'Мы не смогли получить Вашу заявку по неизвестным причинам'],
    ];
    $response = $responses[$code] ?? $responses[1];
    $response['title'] = Lang::get($response['title']);
    $response['message'] = Lang::get($response['message']);
    return $response;
  }

}
