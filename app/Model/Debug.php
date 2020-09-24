<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $k 
 * @property string $v 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Debug extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'debugs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['k', 'v'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}