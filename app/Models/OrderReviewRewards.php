<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReviewRewards extends Model
{
    protected $table = 'order_review_rewards';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
