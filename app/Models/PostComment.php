<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
  protected $table = 'posts_comments';
  protected $primaryKey = 'id';
  public $incrementing = true;
  public $timestamps = false; 

  protected $fillable = [
    'post_id',
    'user_id',
    'user_name',
    'user_avatar',
    'text',
    'marked',
    'reply_to',
    'publish',
    'created_at',
    'updated_at',
  ];
}
