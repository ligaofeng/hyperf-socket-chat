<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $name 
 * @property string $nick_name 
 * @property string $email 
 * @property string $phone 
 * @property string $password 
 * @property string $bio 
 * @property int $sex 
 * @property string $location 
 * @property string $avatar 
 * @property string $bg 
 * @property int $status 
 * @property int $type 
 * @property string $email_verified_at 
 * @property string $phone_verified_at 
 * @property string $remember_token 
 * @property string $register_ip 
 * @property string $last_login_ip 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $deleted_at 
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'sex' => 'integer', 'status' => 'integer', 'type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}