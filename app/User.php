<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Lab404\Impersonate\Models\Impersonate;
use DB;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name','name', 'email', 'password', 'plan_password' , 'remember_token',
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
    ];


    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    public function authorizeRoles($roles)
    {
        if (is_array($roles)) {
            return $this->hasAnyRole($roles) ||
                abort(401, 'This action is unauthorized.');
        }
        return $this->hasRole($roles) ||
            abort(401, 'This action is unauthorized.');
    }
    public function hasAnyRole($roles)
    {
        return null !== $this->roles()->whereIn('name', $roles)->first();
    }
    public function hasRole($role)
    {
        return null !== $this->roles()->where('name', $role)->first();
    }

    public function userSetting()
    {
       return $this->hasOne('App\UserSettings', 'user_id');
    }

   
    public function IsEmailExist($email,$id){
        $query = DB::table('users');
        if (!empty($id)) {
            $result = $query->where('id','!=', $id);
        }
        $query->where('email',$email);
        
        return $query->count();
    }   

    public static function isCandidateTakeTest($fair_id, $candidate_id)
    {
        $candidateTest = New CandidateTest;
        $res = $candidateTest->where('fair_id', $fair_id)->where('candidate_id',$candidate_id)->get();
        if ($res->count() > 0) {
            return 1;
        }

        return 0;
    }

    public static function isCandidateAttendFair($fair_id, $candidate_id)
    {
        $candidateTurnout = New CandidateTurnout;
        $res = $candidateTurnout->where('fair_id', $fair_id)->where('candidate_id',$candidate_id)->first();
        if ($res) {
            return 1;
        }

        return 0;
    }

    public static function isCandidateInMainHall($fair_id, $candidate_id)
    {
        $fairCandidates = New FairCandidates;
        $res = $fairCandidates->where('fair_id', $fair_id)->where('candidate_id',$candidate_id)->where('mainhall',1)->first();
        if ($res) {
            return 1;
        }

        return 0;
    }
}
