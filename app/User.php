<?php

namespace App;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class User extends Authenticatable implements JWTSubject //, MustVerifyEmail
{
    use Notifiable, HasRoles, LadaCacheTrait, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'phone',
        'avatar',
        'address_id',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'photo_url', 'name'
    ];

    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        return 'https://www.gravatar.com/avatar/'.md5(strtolower($this->email)).'.jpg?s=200&d=mm';
    }

    public function getAvatarAttribute($value){
        $avatar = '/app-assets/img/portrait/profile_default.png';

        switch ($this->role_id) {
            case 1:
                $avatar = '/app-assets/img/portrait/admin_profile.png';
                break;
            case 2:
                $avatar = '/app-assets/img/portrait/admin_profile.png';
                break;
            case 3:
                $avatar = '/app-assets/img/portrait/org_profile.png';
                break;
            default:
                $avatar = '/app-assets/img/portrait/profile_default.png';
                break;
        }

        $avatarURL = asset($avatar);

        return $value ? $value : $avatarURL;
    }

    /**
     * Get the full name attribute.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the oauth providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function user_role() {
        return $this->belongsTo('App\Role', 'role_id');
        //return $this->hasMany(User::class);
    }

    public function address() {
        return $this->belongsTo('App\Address');
    }

    public function organisation_user() {
        return $this->hasOne('App\OrganizationUser');
    }

    public function scopeGetUsers($query, $request){
        $query = $query
        ->select('*')
        ->with(['user_role']);

        if(isset($request['search']) && !empty($request['search'])){
            $query = $query->where(function ($q) use($request) {
                $q->orWhere('email', 'like', '%'.$request['search'].'%');
                $q->orWhere('first_name', 'like', '%'.$request['search'].'%');
                $q->orWhere('last_name', 'like', '%'.$request['search'].'%');
            });
        }

        if(isset($request['role']) && !empty($request['role'])){
            $query = $query->whereHas('user_role', function($q) use($request) {
                $q->where('name', strtolower($request['role']));
            });
        }else{
            $query = $query->whereHas('user_role', function($q) use($request) {
                $q->where('name', '<>', 'organisation');
            });
        }

        return $query;
    }
}
