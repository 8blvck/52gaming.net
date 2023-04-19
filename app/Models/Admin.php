<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
  protected $table = 'landing_noadmin_users';
  protected $primaryKey = 'id';
  public $incrementing = true;
  public $timestamps = false; 
  protected $fillable = [
    'login',
    'email',
    'password',
    'avatar',
    'nick_name',
    'birth_date',
    'language',
    'country',
    'city',
    'zip',
    'phone',
    'skype',
    'viber',
    'discord',
    'vkontakte',
    'facebook',
    'instagram',
    'youtube',
    'twitter',
    'is_approved',
    'is_blocked',
    'is_configured',
    'type',
    'permissions',
    'referrer_hash',
    'recovery_hash',
    'created_at',
    'updated_at',
  ];

}
