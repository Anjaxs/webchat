<?php

declare (strict_types=1);
namespace App\Model;

/**
 */
class Count extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'counts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['count', 'user_id', 'room_id'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * 不自动维护 created_at 和 updated_at
     */
    public $timestamps = false;
}